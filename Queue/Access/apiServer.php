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

/**
 * API class
 *
 * This class accesses queues via API calls
 *
 * @author Larry Lewis <phpMQ@jenolan.org>
 */
class apiServer extends Access
{
    public function __construct( array $keys, array $options = array() )
    {
        parent::__construct( PHPMQ_SERVER );         // Only server functions allowed
        $this->config['keys']     = $keys;
        $this->config['options']  = $options;
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    protected function addMessageProc( array $message, $name )
    {
        throw new \Exception("API server can not handle addMessage");
    }

}