<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['filter' => 'cors'], function ($routes) {
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('login', 'Api\AuthController::login');
});
