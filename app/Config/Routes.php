<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Trips::index');

$routes->group('trips', static function ($routes) {
    $routes->get('/', 'Trips::index');
    $routes->get('create', 'Trips::create');
    $routes->post('store', 'Trips::store');
    $routes->get('edit/(:num)', 'Trips::edit/$1');
    $routes->post('update/(:num)', 'Trips::update/$1');
    $routes->get('delete/(:num)', 'Trips::delete/$1');
});

$routes->group('api', static function ($routes) {
    $routes->get('destination-suggest', 'TripApi::destinationSuggest');
    $routes->get('weather', 'TripApi::weather');
    $routes->get('trips/search', 'TripApi::liveSearch');
    $routes->get('nearby-trips', 'TripApi::nearbyTrips');
});
