<?php 

spl_autoload_register(function($className) { require str_replace('\\', '/', $className).'.php'; });
# ini_set('display_errors', 1);
# ini_set('error_reporting', E_ALL);

// TODO