<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index', ['filter' => 'session']);

/* Survey Routes */
$routes->group('surveys', function ($routes) {
    $routes->get('(:num)', 'SurveyController::view/$1');
    $routes->get('thank-you', 'SurveyController::thankYou');
});

/* Survey Routes with Authorisation */
$routes->group('surveys', ['filter' => 'session'], function ($routes) {
    $routes->get('/', 'SurveyController::index');
    $routes->get('create', 'SurveyController::create');
    $routes->get('(:num)/manage', 'SurveyController::manage/$1');
    $routes->get('(:num)/edit', 'SurveyController::edit/$1');
});

/* Admin Routes */
$routes->group('admin', ['filter' => ['session', 'admin']], function ($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('users', 'AdminController::users');
    $routes->get('users/create', 'AdminController::createUser');
});

/* API Routes */
$routes->group('api', ['namespace' => 'App\Controllers\API'], function ($routes) {
    $routes->resource('surveys', ['controller' => 'SurveyController']);
    $routes->resource('questions', ['controller' => 'QuestionsController']);
    $routes->resource('answers', ['controller' => 'AnswersController']);
    $routes->resource('responses', ['controller' => 'ResponsesController']);
    $routes->resource('question-responses', ['controller' => 'QuestionResponsesController']);
});

/* Admin API Routes */
$routes->group('api', ['namespace' => 'App\Controllers\API', 'filter' => ['apiadmin']], function ($routes) {
    $routes->resource('users', ['controller' => 'UsersController']);
});

service('auth')->routes($routes);
