# PhotoBackup PHP server

The PHP implementation of PhotoBackup server, made with core PHP functions.

## Installation

Copy the files in a PHP-enabled webdirectory.

Apache should enable __mod_rewrite__ and accept .htaccess files. 

## Configuration

To Configure the server, change the constants/variables in the index.php:

* `MEDIA_ROOT`, the directory where the pictures are written in ;
* `PASSWORD`, the password as cleartext __SIMPLE BUT NOT RECOMMEND__ ;
* `$pw_hash`, the sha512 hased password - if emtpy, the `PASSWORD` will be hashed
* `$logfile`, the path to a logfile - if emtpy/null, logging is disabled
