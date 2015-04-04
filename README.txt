phpMQ - A simple, fast queue manager and task executor. 
Stack your tasks to queue and execute them later. 
Basic functions: add/remove Queue, add/comsume Message.	

1. Extracting archive using tar and change directory

tar -xzf phpmq.tgz
cd phpmq


2. Download composer and install all dependencies of phpMQ

curl -sS https://getcomposer.org/installer | php
php composer.phar install


3. Edit file with the connection settings to the database ( only mysql )

vim settings.php


4. Сreate database phpmq and import structure 

mysql -u username -ppassword phpmq < Structure/structure.mysql.sql


5. Usage examples are in example.php

