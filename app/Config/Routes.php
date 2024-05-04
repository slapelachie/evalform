<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index');

/* Survey Routes */
$routes->group('surveys', function ($routes) {
    $routes->get('/', 'SurveyController::index');

    $routes->get('create', 'SurveyController::create');

    $routes->get('(:num)', 'SurveyController::view/$1');
    $routes->get('(:num)/manage', 'SurveyController::manage/$1');
    $routes->get('(:num)/edit', 'SurveyController::edit/$1');
    $routes->get('thank-you', 'SurveyController::thankYou');
});
$routes->get('surveys/(:num)', 'SurveyController::view/$1');
$routes->get('surveys/(:num)/manage', 'SurveyController::manage/$1');
$routes->get('surveys/(:num)/edit', 'SurveyController::edit/$1');

$routes->get('surveys/thank-you', 'SurveyController::thankYou');

/* Testing Route */
$routes->get('test/(:num)', 'TestController::index/$1');

service('auth')->routes($routes);

/* API Routes */
$routes->group('api', ['namespace' => 'App\Controllers\API'], function ($routes) {
    $routes->resource('surveys', ['controller' => 'SurveyController']);
    $routes->resource('questions', ['controller' => 'QuestionsController']);
    $routes->resource('answers', ['controller' => 'AnswersController']);
    $routes->resource('responses', ['controller' => 'ResponsesController']);
    $routes->resource('question-responses', ['controller' => 'QuestionResponsesController']);
});
