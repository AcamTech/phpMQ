<?php

require __DIR__ . '/vendor/autoload.php';

include 'settings.php';

//connection to the library
$phpmq = new \phpMQ\pdom($dsn, $username, $password, $options_pdo);

$queue_name = 'Test queue';

// string $queue_name to add
// returns integer queue id, or FALSE if name already exists

var_dump($phpmq->addQueue($queue_name));

$message = 'Test message';

// string $queue_name to publish to, string $message (or array of strings)
// returns integer message id

var_dump($phpmq->addMessage($queue_name, $message));

// string $queue_name to consume from
// returns message or FALSE if queue is empty or doesn't exist

var_dump($phpmq->consumeMessage($queue_name));

// string $queue_name to delete
// boolean $purge: to delete or not a non-empty queue
// returns TRUE on success or FALSE on failure (non-existent queue or non-exmpty queue with purge==FALSE)
$purge = TRUE;

var_dump($phpmq->removeQueue($queue_name, $purge));

