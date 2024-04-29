<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index');

/* Survey Routes */
$routes->get('surveys', 'SurveyController::index');

$routes->get('surveys/create', 'SurveyController::create');

$routes->get('surveys/(:num)', 'SurveyController::view/$1');
$routes->get('surveys/(:num)/manage', 'SurveyController::manage/$1');
$routes->get('surveys/(:num)/edit', 'SurveyController::edit/$1');

$routes->get('surveys/thank-you', 'SurveyController::thankYou');

/* Testing Route */
$routes->get('test/(:num)', 'TestController::index/$1');

service('auth')->routes($routes);

/* API Routes */
$routes->resource('api/surveys', ['controller' => 'API\SurveyController']);
$routes->resource('api/questions', ['controller' => 'API\QuestionsController']);
$routes->resource('api/answers', ['controller' => 'API\AnswersController']);
$routes->resource('api/responses', ['controller' => 'API\ResponsesController']);
$routes->resource('api/question-responses', ['controller' => 'API\QuestionResponsesController']);
