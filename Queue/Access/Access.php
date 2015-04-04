<?php
/*
 * This file is part of phpMQ Queue Manager.
 *
 * (c) 2014 Larry Lewis <phpMQ@jenolan.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This system is based on work by Andrius Putna http://fordnox.github.io/php-queue-manager/
 */
namespace phpMQ;
use phpMQ\Exception\phpMQException;
/**
 * Access class
 *
 * This class maps requests to the access method
 *
 * @author Larry Lewis <phpMQ@jenolan.org>
 */

if( ! defined('PHPMQ_SERVER') ){ define('PHPMQ_SERVER',1);}
if( ! defined('PHPMQ_CLIENT') ){ define('PHPMQ_CLIENT',2);}

abstract class Access
{
    protected $mode   = null;               // one or both of PHPMQ_SERVER & PHPMQ_CLIENT
    protected $config = array();            // settings used by the class
    protected $type   = null;               // Which class type like 'PDO' or 'API' (info only)

    protected $allowedStatus = array( 'new', 'open', 'done', 'closed', 'failed' ); // Message status codes
    protected $allowedTypes  = array( 'message', 'api', 'fork', 'intval', 'dataval' ); // Allowed message types

    public function __construct( $mode )
    {
        $this->mode = $mode;
    }

    public function __destruct()
    {
        $this->mode = null;
    }

    /**
     * Return Access type (ie name like PDO)
     *
     * @return string
     */
    public function getType()
    {
        return( $this->type );
    }

    /**
     * Return Access configuration (ie for PDO it is the DB info)
     *
     * @return string
     */
    public function getConfig()
    {
        return( $this->config );
    }

    /**
     * Set message status
     *
     * @param int $mid Message to set status
     * @param string $status status to set
     * @return bool
     */
    public function setStatus(  $mid, $status )
    {
        if( ! $this->isServer() )
        {
            throw new phpMQException( "Can't set status server mode not set" );
        }
        if( ! in_array( $status, $this->allowedStatus ) )
        {
            throw new phpMQException( "Status '{$status}' not allowed" );
        }
        return( $this->setMessage( $mid, array( 'm_status' => $status ) ) );
    }
    /**
     * Remove(delete) a message from queue
     *
     * @param int $mid Message id to delete
     * @return bool
     */
    public function removeMessage( $mid )
    {
        if( ! $this->isServer() )
        {
            throw new phpMQException( "Can't delete message server mode not set" );
        }
        return( $this->removeMessageProc( $mid ) );
    }
    abstract protected function removeMessageProc(  $mid );

    /**
     * Process the message finalisation
     *
     * @param array $msg Message returned from consumeMessage()
     * @param string $log process logging (optional)
     * @param string $reply any return message to initiator (optional)
     * @param int $rc return code (default 0)
     * @return bool
     */
    public function finishMessage( array $msg, string $reply = NULL, string $log = NULL, int $rc = NULL )
    {
        if( ! $this->isServer() )
        {
            throw new phpMQException( "Can't finish message server mode not set" );
        }
        if( $msg['mq_type'] == 'intval' OR $msg['mq_type'] == 'dataval' )
        {
            return( true );     // No action required here
        }
        if( $msg['mq_type'] == 'api' OR $msg['mq_type'] == 'fork' )
        {
            return( false );     //TODO Not coded yet
        }
        if( $msg['mq_type'] == 'message' )
        {
            $arr = array();
            $arr['m_status']    = 'done';
            $arr['m_completed'] = date( 'Y-m-d H:i:s' );
            if( $reply ) $arr['m_reply']  = $reply;
            if( $rc )    $arr['m_rc']     = $rc;
            if( $log )   $arr['m_log']    = $log;
            $this->setMessage( $msg['m_id'], $arr );
            return( true );     // No action required here
        }
        return( false );
    }
    /**
     * Return a message from queue
     *
     * @param string $name the queue 
     * @param bool $mode normally false (normal), true allows processing api & fork queues (normally internal only)
     * @param array $options queue options (default none)
     * @return bool
     */
    public function consumeMessage(  $name )
    {
        if( $this->mode === NULL) 
        {
        $mode = FALSE;
        }
        if( ! $this->isServer() )
        {
            throw new phpMQException( "Can't consume message server mode not set" );
        }
        $q = $this->getQueueInfo( $name );
        if( $q === false )
        {
            throw new phpMQException( "Queue '{$name}' not found" );
        }
        if( $q['mq_type'] == 'intval' )
        {
            return( $q['mq_intval'] );
        }
        if( $q['mq_type'] == 'dataval' )
        {
            return( $q['mq_data'] );
        }
        if( $q['mq_type'] == 'api' AND ! $mode )
        {
            throw new phpMQException( "Queue '{$name}' is type API, consume not allowed" );
        }
        if( $q['mq_type'] == 'fork' AND ! $mode )
        {
            throw new phpMQException( "Queue '{$name}' is type FORK, consume not allowed" );
        }
        $msg = $this->consumeMessageProc( $name );
        if( $msg === false )
        {
            return( false );        //No Message available!
        }

        return(  $msg );
    }
    abstract protected function consumeMessageProc( $name );
    /**
     * Add a message to a queue
     *
     * @param mixed $message to send
     * @param string $name the queue to use (UNIVERSAL default)
     * @param array $options queue options (default none)
     * @return bool
     */
    public function addMessage(  $name = null, $message )
    {
        if( ! $this->isClient() )
        {
            throw new phpMQException( "Can't send message client mode not set" );
        }
        $options = [];
        $arr = array();
        if(is_array($message))$arr['m_msg'] = json_encode( $message );
        
        elseif(!is_string($message)) throw new phpMQException( "Can't send this type of message" );
        
        else $arr['m_msg'] = (string) $message ;
        
        $default = array( 'mq_id'        => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_VALIDATE_INT ) ),
                          'm_msg'        => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_ENCODED ) ),
                          'm_log'        => array( 'default' => null,
                                                   'filter'  => array( 'filter'  => FILTER_SANITIZE_STRING ) ),
                          'm_respond'    => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_URL ) ),
                          'm_status'     => array( 'default' => 'new',
						   'values'  => $this->allowedStatus,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
                          'm_started'    => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
                          'm_reply'      => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
                          'm_rc'         => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_VALIDATE_INT ) ),
                          'm_completed'  => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
			  'm_errors'     => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_VALIDATE_INT ) ),
                          'm_uuid'       => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) )
                        );
        
        $arr['m_uuid'] =  uniqid();
        if( isset( $options['m_respond'] ) AND filter_var( $options['m_respond'], FILTER_VALIDATE_URL ) )
        {
            $arr['m_respond'] = $options['m_respond'];
        }
        $q = $this->getQueueID( $name );
        if( $q === false )
        {
            throw new phpMQException( "Queue '{$name}' not found" );
        }
        else
        {
            $arr['mq_id'] = $q;
            if( $q['mq_type'] == 'intval' )
            {
                return( $this->setIntValue( $name, intval( $message ) ) );
            }
            if( $q['mq_type'] == 'dataval' )
            {
                return( $this->setDataValue( $name, $message ) );
            }
            if(  in_array(  $q['mq_type'] , array(  'api', 'fork' ) ) )
            {
                throw new phpMQException( "Queue '{$name}' not set for a valid messaging mode" );
            }
        }
        $message_arr = $this->mergeOptions( $default, $arr ); 
        
        return( $this->addMessageProc( $message_arr) );
    }
    abstract protected function addMessageProc( array $message );

    /**
     * Return queue id number from the queue name
     *
     * @param string $name the name of the queue to find
     * @return int queueid, false on not found
     */
    abstract protected function getQueueID(  $name );

    /**
     * Return queue information from the queue name
     *
     * @param mixed $name the name of the queue to find (or q_id)
     * @return mixed queueinfo, array of queue information or false on not found
     */
    abstract protected function getQueueInfo(  $name );

    /**
     * Create a new Queue
     *
     * @param string $name the queue to create
     * @return int queueid, false on not found
     */
    public function addQueue(  $name )
    {
        $options = [];
        if( ! $this->isServer() ) throw new phpMQException( "Can't add queue server mode not set" );
        
        if(!is_string ($name) )throw new phpMQException( "Can't add queue with non-string name" );
        
        $default = array( 'mq_name'      => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
                          'mq_type'      => array( 'default' => 'message',
                                                   'values'  => $this->allowedTypes,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
                          'mq_timeout'   => array( 'default' => 3000,
                                                   'filter'  => array( 'filter'  => FILTER_VALIDATE_INT,
                                                                       'options' => array('min_range' => 1000, 'max_range' => 30000 ) ), ),
                          'mq_maxerrors' => array( 'default' => 1,
                                                   'filter'  => array( 'filter' => FILTER_VALIDATE_INT,
                                                                       'options' => array('min_range' => 1, 'max_range' => 10 ), ), ),
                          'mq_desc'      => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) ),
                          'mq_respond'   => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_URL ), ),
                          'mq_intval'    => array( 'default' => 0,
                                                   'filter'  => array( 'filter' => FILTER_VALIDATE_INT ), ),
                          'mq_data'      => array( 'default' => null,
                                                   'filter'  => array( 'filter' => FILTER_SANITIZE_STRING ) )
                        );
        if( $this->getQueueID( $name ) === false )
        {
            $vars = $this->mergeOptions( $default, $options );
            $vars['mq_name'] = $name;
            return( $this->addQueueProc( $vars ) );
        }
        throw new phpMQException( "Can't create queue '{$name}' as it already exists" );
    }
    abstract protected function addQueueProc( array $vars );

    /**
     * Set a single value queue
     *
     * @param string $name queue to use
     * @param int $value integer value to use
     * @return bool
     */
    public function setIntValue( string $name, int $value )
    {
        if( ! $this->isClient() ) throw new phpMQException( "Can't set value client mode not set" );
        $q = $this->getQueueInfo( $name );
        if( $q === false )
        {
            throw new phpMQException( "Queue '{$name}' not found" );
        }
        if( $q['mq_type'] != 'intval' )
        {
            throw new phpMQException( "Queue '{$name}' not set to intval" );
        }
        return( $this->setQueueInfo( $q['mq_id'], array( 'mq_intval' => $value ) ) );
    }
    /**
     * Set a single value queue
     *
     * @param string $name queue to use
     * @param string $value value to use (note serialisation callers responsibility)
     * @return bool
     */
    public function setDataValue(  $name,  $value )
    {
        if( ! $this->isClient() ) throw new phpMQException( "Can't set value client mode not set" );
        $q= $this->getQueueInfo( $name );
        if( $q === false )
        {
            throw new phpMQException( "Queue '{$name}' not found" );
        }
        if( $q['mq_type'] != 'dataval' )
        {
            throw new phpMQException( "Queue '{$name}' not set to dataval" );
        }
        return( $this->setQueueInfo( $q['mq_id'], array( 'mq_data' => $value ) ) );
    }
    /**
     * Remove(delete) a queue
     *
     * @param string $name Queue name to delete
     * @param bool $purge ability to delete a non-empty queue
     * @return bool
     */
    public function removeQueue( $name, $purge = false )
    {
        if( ! $this->isServer() )
        {
            throw new phpMQException( "Can't delete queue server mode not set" );
        }
        $mq_id = $this->getQueueID($name);
        
        if( $mq_id === false )
            {
                throw new phpMQException( "Queue '{$name}' not found" );
            }
        $array_id = $this->getMessageIDfromQueue($mq_id);
        
        if (is_array($array_id) && !$purge)
            {
                throw new phpMQException( "Can't delete queue '{$name}' purge mode not set" );
            }
        elseif(is_array($array_id))
            {
                foreach($array_id as $mid)
            {
                $this->removeMessage( $mid );
            }
            }

        return( $this->removeQueueProc( $mq_id ) );
    }
    abstract protected function removeQueueProc(  $mq_id );
    
    abstract protected function getMessageIDfromQueue(  $mq_id );
    

//    abstract protected function setQueueInfo( int $qid, array $vars );
//    abstract protected function setMessage( int $mid, array $vars );
    /**
     * Return whether server calls allowed
     *
     * @return bool
     */
    public function isServer()
    {
        if( $this->mode === null )
        {
            throw new phpMQException( "Access mode not set" );
        }
        return( ( $this->mode AND PHPMQ_SERVER ) ? true : false );
    }
    /**
     * Return whether client calls allowed
     *
     * @return bool
     */
    public function isClient()
    {
        if( $this->mode === null )
        {
            throw new phpMQException( "Access mode not set" );
        }
        return( ( $this->mode AND PHPMQ_CLIENT ) ? true : false );
    }

    private function mergeOptions( array $default, array $options )
    {
        $result = array();
        foreach( $default AS $k => $v )
        {
            if( $v['default'] !== null ) $result[$k] = $v['default'];
            if( isset( $options[$k] ) AND $options[$k] )
            {
                $fo = array();
                $fo['options'] = isset( $v['filter']['options'] ) ? $v['filter']['options'] : array();
                $fo['flags']   = isset( $v['filter']['flags'] )   ? $v['filter']['flags']   : array();
                $ff            = isset( $v['filter']['filter'] )  ? $v['filter']['filter']  : array();
                $x = filter_var( $options[$k], $ff, $fo );
                if( $x === false )
                {
                    throw new phpMQException("Option '{$k}' value not permitted");
                }
                $result[$k] = $x;
            }
        }
        return( $result );
    }
}
