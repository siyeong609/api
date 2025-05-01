<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('test', 'TestController::checkEnv');

$routes->group('api', ['filter' => 'cors'], function ($routes) {
    $routes->get('me', 'Api\UserController::me');
    $routes->put('me', 'Api\UserController::updateMe');
    $routes->post('register', 'Api\AuthController::register');
    $routes->post('login', 'Api\AuthController::login');
    $routes->post('forgot-password', 'Api\PasswordController::forgotPassword');
    $routes->post('reset-password', 'Api\PasswordController::resetPassword');
});
