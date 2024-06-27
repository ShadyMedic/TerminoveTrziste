<?php

spl_autoload_register(function($className) {
    $className = explode('\\', $className);
    array_shift($className);
    require implode('/', $className).'.php';
});
# ini_set('display_errors', 1);
# ini_set('error_reporting', E_ALL);
