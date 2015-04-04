<?php

/* 
 * This file is part of phpMQ Queue Manager.
 * Parameters  of the db connection settings/
 * (c) 2014 Larry Lewis <phpMQ@jenolan.org>
 */


$host = '127.0.0.1';     # IP of database host
$db_name = 'phpmq_test'; # database name
$username = 'phpmq';     # database login
$password = '123';       # database password
$options_pdo = [];       # options of pdo connection to database

$dsn = 'mysql:host=' .$host. ';dbname=' .$db_name;


