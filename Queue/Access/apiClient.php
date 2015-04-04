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
//require __DIR__. '/Access.php';
/**
 * API class
 *
 * This class accesses queues via API calls
 *
 * @author Larry Lewis <phpMQ@jenolan.org>
 */
class apiClient extends Access
{

    //TODO API will need to cache info to stop repetitive remote calls (optionally)

    public function __construct( $url, $key, array $options = array() )
    {
        parent::__construct( PHPMQ_CLIENT );         // Only client functions allowed
        $this->config['url']      = $url;
        $this->config['key']      = $key;
        $this->config['options']  = $options;
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    protected function addMessageProc( array $message)
    {
    }
    protected function addQueueProc( array $values )
    {  
    }
    protected function setQueueInfo( int $mq_id, array $values )
    {  
    }
    protected function getQueueInfo( string $name )
    {  
    }
    protected function getQueueID( string $name )
    {   
    }
    protected function removeMessageProc( int $mid )
    {    
    }
    protected function consumeMessageProc( array $message )
    { 
    }
    protected function setMessage( array $message )
    {   
    }

}