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

/** @var \Laravel\Lumen\Routing\Router $router */
$router->post('/auth/sign-in', 'LoginController@signIn');
$router->post('/auth/sign-up', 'RegisterController@signUp');
$router->post('/auth/refresh', ['middleware' => ['jwt.check'], 'uses' => 'LoginController@refresh']);

$router->group(['middleware' => ['auth']], function (\Laravel\Lumen\Routing\Router $router) {

    $router->group(['middleware' => ['approved']], function (\Laravel\Lumen\Routing\Router $router) {
        $router->post('/tasks', 'TaskController@store');
        $router->get('/tasks', 'TaskController@index');

        $router->get('/credits', 'CreditController@index');
        $router->post('/credits/buy', 'CreditController@buy');
    });

    $router->group(['middleware' => ['admin']], function (\Laravel\Lumen\Routing\Router $router) {
        $router->get('/admin/users/pending', 'UserController@pending');
        $router->get('/admin/users/{id}', 'UserController@show');
        $router->put('/admin/users/{id}/ban', 'UserController@ban');
        $router->put('/admin/users/{id}/approve', 'UserController@approve');
    });
});
