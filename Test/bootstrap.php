<?php
error_reporting(E_ALL);
require dirname( __DIR__ ) . '/vendor/autoload.php';

function getConnection()
{
    static $dbh = null;
    if(null === $dbh)
    {

        $sql = file_get_contents( dirname( __DIR__ ) . '/Structure/structure.mysql.sql');
        $dbh = new PDO('mysql:host=localhost;dbname=phpmq_test', 'travis'); // Change if you want
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        $dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $dbh->exec( $sql );
    }
    return $dbh;
}
