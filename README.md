phpMQ - PHP Message Queue System
======

[![Build Status](https://travis-ci.org/phpMQ/phpMQ.svg)](https://travis-ci.org/phpMQ/phpMQ)
[![Latest Stable Version](https://poser.pugx.org/phpmq/phpmq/v/stable.svg)](https://packagist.org/packages/phpmq/phpmq) 
[![Latest Unstable Version](https://poser.pugx.org/phpmq/phpmq/v/unstable.svg)](https://packagist.org/packages/phpmq/phpmq) 
[![License](https://poser.pugx.org/phpmq/phpmq/license.svg)](http://opensource.org/licenses/MIT)

[![Total Downloads](https://poser.pugx.org/phpmq/phpmq/downloads.svg)](https://packagist.org/packages/phpmq/phpmq) 
[![Monthly Downloads](https://poser.pugx.org/phpmq/phpmq/d/monthly.png)](https://packagist.org/packages/phpmq/phpmq)
[![Daily Downloads](https://poser.pugx.org/phpmq/phpmq/d/daily.png)](https://packagist.org/packages/phpmq/phpmq)

# Please note not ready for use yet

phpMQ - A simple, fast queue manager and task executor. Stack your tasks to queue and execute them later.

phpMQ is a fast and secure queue manager and executor for PHP. It is a way to manage asynchronous, potentially long-running PHP tasks such as API requests, database export/import operations, email sending, payment notification handlers, feed generation etc.

phpMQ can be integrated to any PHP based application with minimal effort, because it does not depend on any framework. PDO extension and mySQL are the only requirements. phpMQ manager is for those who want to have a message queue running quickly.

phpMQ is and will remain as simple Message Queue system, for more complex message handling look at [RabbitMQ](http://rabitmq.com).

**Requirements**

 * PHP >5.4
 * PDO
 * Database backend (sqlite, sqlite memory, mysql, postgres, any PDO supported database)

**Install with Composer**

```json
"require": 
{
    "phpmq/phpmq": "dev-master"
}
```

**Сreate database table** 

mysql -u username -ppassword phpmq < Structure/structure.mysql.sql

**Examples**
Usage examples are in example.php

**Documentation and Help**

 * Visit the [homepage](http://phpmq.org/) for more information
 * [Bug Tracker](https://github.com/phpmq/phpmq/issues)
 * If you make improvements or bug fixes please generate a pull request

Thanks to [RabbitMQ](http://rabbitmq.com) for ideas and [PHP Queue Manager](http://fordnox.github.io/php-queue-manager/) for a quick start, we did initially run with [RabbitMQ](http://rabbitmq.com) but the management was too much for what we needed. Now using phpMQ with [etcd/etcdctl](https://github.com/coreos/etcd) to solve our interserver needs.
