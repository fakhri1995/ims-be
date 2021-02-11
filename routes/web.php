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

//User Routes
$router->get('/login', 'UserController@login');
$router->get('/logout', 'UserController@logout');
$router->get('/detailProfile', 'UserController@detailProfile');
$router->get('/changePassword', 'UserController@changePassword');

//Company Routes
$router->get('/getCompanyDetail', 'CompanyController@getCompanyDetail');
$router->get('/getCompanyList', 'CompanyController@getCompanyList');
$router->get('/addCompanyMember', 'CompanyController@addCompanyMember');
$router->get('/updateCompanyDetail', 'CompanyController@updateCompanyDetail');
$router->get('/companyActivation', 'CompanyController@companyActivation');

//Account Routes
$router->get('/getAccountDetail', 'AccountController@getAccountDetail');
$router->get('/getAccountList', 'AccountController@getAccountList');
$router->get('/addAccountMember', 'AccountController@addAccountMember');
$router->get('/updateAccountDetail', 'AccountController@updateAccountDetail');
$router->get('/accountActivation', 'AccountController@AccountActivation');

//Account Routes
$router->get('/getAccessModule', 'AccessFeatureController@getAccessModule');
$router->get('/getAccessFeature', 'AccessFeatureController@getAccessFeature');
$router->get('/addAccessModule', 'AccessFeatureController@addAccessModule');
$router->get('/addAccessFeature', 'AccessFeatureController@addAccessFeature');
$router->get('/updateAccessFeature', 'AccessFeatureController@updateAccessFeature');
$router->get('/updateModuleCompany', 'AccessFeatureController@updateModuleCompany');
$router->get('/updateFeatureAccount', 'AccessFeatureController@updateFeatureAccount');