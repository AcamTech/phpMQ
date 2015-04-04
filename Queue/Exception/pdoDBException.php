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
namespace phpMQ\Exception;

/**
 * pdo DB Exception class
 *
 * This class makes a PDO error clean (and allows a log entry point (later)
 *
 * @author Larry Lewis <phpMQ@jenolan.org>
 */
class pdoDBException extends \PDOException
{

    public function __construct( PDOException $e )
    {
        if( strstr( $e->getMessage(), 'SQLSTATE[' ) )
        {
            preg_match( '/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches );
            $this->code    = ( $matches[1] == 'HT000' ? $matches[2] : $matches[1] );
            $this->message = $matches[3];
        }
    }
}