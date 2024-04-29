<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index');

/* Survey Routes */
$routes->get('surveys/create', 'SurveyController::create');
$routes->post('surveys/create', 'SurveyController::createSubmit');

$routes->get('surveys/(:num)', 'SurveyController::index/$1');
$routes->get('surveys/(:num)/manage', 'SurveyController::manage/$1');
$routes->get('surveys/(:num)/edit', 'SurveyController::edit/$1');

$routes->post('surveys/(:num)', 'SurveyController::surveySubmit/$1');

/* Testing Route */
$routes->get('test/(:num)', 'TestController::index/$1');

service('auth')->routes($routes);

/* API Routes */
$routes->resource('api/surveys', ['controller' => 'API\SurveyController']);
$routes->resource('api/questions', ['controller' => 'API\QuestionsController']);
$routes->resource('api/answers', ['controller' => 'API\QuestionAnswerChoicesController']);
$routes->resource('api/responses', ['controller' => 'API\ResponsesController']);
