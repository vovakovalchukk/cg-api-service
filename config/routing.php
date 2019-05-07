<?php
$routes = array();
$files = [
    'root.php',
    'webhook.php',
];
foreach ($files as $file) {
    $route = require_once __DIR__.'/routes/'.$file;
    $routes = array_merge($routes, $route);
}
return $routes;