<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'DashboardController::index');

/* Survey Routes */
$routes->get('survey/(:num)', 'SurveyController::index/$1');
$routes->get('survey/manage/(:num)', 'SurveyController::manage/$1');
$routes->get('survey/edit/(:num)', 'SurveyController::edit/$1');

$routes->post('survey/(:num)', 'SurveyController::surveySubmit/$1');