<?php

use TrackPHP\Router\Router;

// Instantiate the router
$router = new Router;

$router->addRoute('GET', '/', 'home#index');

return $router;
