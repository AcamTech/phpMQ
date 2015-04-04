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
use phpMQ\Exception\pdoDBException;

/**
 * pdom class
 *
 * This class accesses queues via PDO calls
 *
 * @author Larry Lewis <phpMQ@jenolan.org>
 */
class pdom extends Access
{
    protected $dbh   = null;

    public function __construct(  $dsn, $username = null, $password = null, array $options = array() )
    {
        parent::__construct( PHPMQ_CLIENT && PHPMQ_SERVER );       // Allow both server and client functions
        $this->config['dsn']      = $dsn;
        $this->config['username'] = $username;
        $this->config['password'] = $password;
        $this->config['options']  = $options;
        try
        {
            $this->dbh = new \PDO( $dsn, $username, $password, $options );
        }
        catch( PDOException $e )
        {
            throw new pdoDBException( $e );
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    protected function addMessageProc( array $message )
    {
        $sql = "INSERT INTO phpmq_message " . $this->storeArray( $message );
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY) );
        if( $sth->execute() === false ) return( false );    // Failed!
        return( intval($this->dbh->lastInsertId() ));
    }
    protected function addQueueProc( array $values )
    {
        $sql = "INSERT INTO phpmq_queue " . $this->storeArray( $values );
        $sth = $this->dbh->prepare( $sql,  array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY) );
        if( $sth->execute() === false ) return( false );    // Failed!
        return( intval($this->dbh->lastInsertId() ));
    }
    protected function setQueueInfo( $mq_id, array $values )
    {
        $sql = "UPDATE phpmq_queue SET " . $this->storeArrayUpdate( $values ) . " WHERE mq_id = :mq_id";
        $sth = $this->dbh->prepare( $sql );
        if( $sth->execute( array( 'mq_id' => $mq_id ) ) === false ) return( false );    // Failed!
        return( true );
    }

    protected function getQueueID(  $name )
    {
        $sql = "SELECT mq_id FROM phpmq_queue WHERE mq_name = :name LIMIT 0,1";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute( array(':name' => $name ) );
        $rows = $sth->fetchAll( \PDO::FETCH_ASSOC );
        if( count( $rows ) )
        {
            return( intval($rows[0]['mq_id'] ));
        }
        return( false );
    }
    protected function getQueueInfo( $name )
    {
        $where = "mq_name = :name";
        if( intval( $name ) > 0)
        {
            $name = intval( $name );
            $where = "mq_id = :name";
        }
        $sql = "SELECT * FROM phpmq_queue WHERE {$where} LIMIT 0,1";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute( array(':name' => $name ) );
        $rows = $sth->fetchAll( \PDO::FETCH_ASSOC );
        if( count( $rows ) )
        {
            return( $rows[0] );
        }
        return( false );
    }
    protected function removeQueueProc( $mq_id )
    {
        $sql = "DELETE FROM phpmq_queue WHERE mq_id = :mq_id";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $result = $sth->execute( array(':mq_id' => $mq_id ) );
        return $result;
    }
    private function storeArray( array &$data )
    {
        $cols = implode( ',', array_keys( $data ) );
        foreach( array_values( $data ) as $value )
        {
            isset( $vals ) ? $vals .= ',' : $vals = '';
            $vals .= '\'' . str_replace( "'", "\\'", $value ) . '\'';
        }
        return( ' (' . $cols . ') VALUES (' . $vals . ')' );
    }
    
    private function storeArrayUpdate( array &$data )
    {
        foreach( $data as $key => $value )
        {
            isset( $vals ) ? $vals .= ',' : $vals = '';
            $vals .= $key.'='.'\'' . str_replace( "'", "\\'", $value ) . '\'';
        }
        return(  $vals  );
    }
    
    protected function removeMessageProc( $mid )
    {
        if( !$this->checkMessage( $mid ) )
        {
            return false;
        }
        $sql = "DELETE FROM phpmq_message WHERE m_id = :m_id";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $result = $sth->execute( array(':m_id' => $mid ) );
        return $result;
    }
    protected function consumeMessageProc(  $name )
    {
        $sql = "SELECT * FROM phpmq_message WHERE mq_id = :mq_id LIMIT 0,1";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $mq_id = intval( $this->getQueueID($name));
        $sth->execute( array(':mq_id' => $mq_id ) );
        $rows = $sth->fetchAll( \PDO::FETCH_ASSOC );
        if( count( $rows ) )
        {
            $this-> removeMessageProc( $rows[0]['m_id'] );
            $rows[0]['m_msg'] = urldecode($rows[0]['m_msg']);
            return( $rows[0] );
        }
        return( false );   
    }
        
    protected function getMessageIDfromQueue(  $mq_id )
    {
        $sql = "SELECT m_id FROM phpmq_message WHERE mq_id = :mq_id ";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute( array(':mq_id' => $mq_id ) );
        $rows = $sth->fetchAll( \PDO::FETCH_NUM );
        if( count( $rows ) )
        {
            $result = [];
            foreach( $rows as $value)
            {
                $result[] = $value[0];
            }
            return( $result);
        }
        return( false );
    }
    protected function checkMessage( $mid )
    {
        $sql = "SELECT * FROM phpmq_message WHERE m_id = :m_id";
        $sth = $this->dbh->prepare( $sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $sth->execute( array(':m_id' => $mid ) );
        $rows = $sth->fetchAll( \PDO::FETCH_ASSOC );
        if( count( $rows ) )
        {
            return( $rows[0] );
        }
        return( false );
    }
}