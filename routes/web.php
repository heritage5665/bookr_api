<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/books', 'BooksController@index');
$router->get('/books/{id:[\d]+}', [
    'as' => 'books.show',
    'uses' => 'BooksController@show'
]);
$router->post('/books', 'BooksController@store');
$router->put('/books/{id:[\d]+}', 'BooksController@update');
$router->delete('/books/{id:[\d]+}', 'BooksController@destroy');


//author routes
$router->get('/authors', 'AuthorsController@index');
$router->get('/authors/{id:[\d]+}', [
    'as' => 'authors.show',
    'uses' => 'AuthorsController@show'
]);
$router->post('/authors', 'AuthorsController@store');
$router->put('/authors/{id:[\d]+}', 'AuthorsController@update');
$router->delete('/authors/{id:[\d]+}', 'AuthorsController@destroy');

//bundles router
$router->get('/bundles/{id:[\d]+}', [
    'as' => 'bundles.show',
    'uses' => 'BundlesController@show'
]);
$router->put(
    '/bundles/{bundleId:[\d]+}/books/{bookId:[\d]+}',
    'BundlesController@addBook'
);
$router->delete(
    '/bundles/{bundleId:[\d]+}/books/{bookId:[\d]+}',
    'BundlesController@removeBook'
);


//Author ratings
$router->post('authors/{id:[\d]+}/ratings', 'AuthorRatingsController@store');
$router->delete(
    'authors/{id:[\d]+}/ratings/{ratingId:[\d]+}',
    'AuthorRatingsController@destroy'
);
