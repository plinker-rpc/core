<?php
//@unlink('/tmp/testdb.db');

include './vendor/autoload.php';

register_shutdown_function(function () {
    //remove test database
    //@unlink('/tmp/testdb.db');
});
