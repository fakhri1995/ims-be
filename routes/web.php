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

// $router->get('/', function () use ($router) {
//     return $router->app->version();
//     return base64_encode(hash("sha1", "GET"."\n"."/admin/v1/get-company"."\n"."02 Jan 06 15:04 MST", $raw_output=TRUE));
// });

//User Routes
$router->post('/login', 'UserController@login');
$router->post('/logout', 'UserController@logout');
$router->post('/detailProfile', 'UserController@detailProfile');
$router->post('/changePassword', 'UserController@changePassword');

//Company Routes
$router->post('/getCompanyDetail', 'CompanyController@getCompanyDetail');
$router->post('/getCompanyList', 'CompanyController@getCompanyList');
$router->post('/addCompanyMember', 'CompanyController@addCompanyMember');
$router->post('/updateCompanyDetail', 'CompanyController@updateCompanyDetail');
$router->post('/companyActivation', 'CompanyController@companyActivation');

//Account Routes
$router->post('/getAccountDetail', 'AccountController@getAccountDetail');
$router->post('/getAccountList', 'AccountController@getAccountList');
$router->post('/addAccountMember', 'AccountController@addAccountMember');
$router->post('/updateAccountDetail', 'AccountController@updateAccountDetail');
$router->post('/changeAccountPassword', 'AccountController@changeAccountPassword');
$router->post('/accountActivation', 'AccountController@AccountActivation');

//Access Feature Routes
$router->post('/getAccessModule', 'AccessFeatureController@getAccessModule');
$router->post('/getAccessFeature', 'AccessFeatureController@getAccessFeature');
$router->post('/addAccessModule', 'AccessFeatureController@addAccessModule');
$router->post('/addAccessFeature', 'AccessFeatureController@addAccessFeature');
$router->post('/updateAccessFeature', 'AccessFeatureController@updateAccessFeature');
$router->post('/updateModuleCompany', 'AccessFeatureController@updateModuleCompany');
$router->post('/updateFeatureAccount', 'AccessFeatureController@updateFeatureAccount');

//Bank Account Routes
$router->get('/getBanks', 'BankController@getBanks');
$router->post('/addBank', 'BankController@addBank');
$router->put('/updateBank', 'BankController@updateBank');
$router->delete('/deleteBank', 'BankController@deleteBank');

//Group Routes
$router->get('/getGroups', 'GroupController@getGroups');
$router->get('/getGroup', 'GroupController@getGroup');
$router->post('/addGroup', 'GroupController@addGroup');
$router->put('/updateGroup', 'GroupController@updateGroup');
$router->delete('/deleteGroup', 'GroupController@deleteGroup');

//Group Routes
// $router->post('/attachPivotGU', 'GroupUserPivotController@attachPivotGU');
// $router->delete('/detachPivotGU', 'GroupUserPivotController@detachPivotGU');

//Asset Routes
$router->get('/getAssets', 'AssetInventoryController@getAssets');
$router->get('/getDeletedAssets', 'AssetInventoryController@getDeletedAssets');
$router->post('/addAsset', 'AssetInventoryController@addAsset');
$router->put('/updateAsset', 'AssetInventoryController@updateAsset');
$router->delete('/deleteAsset', 'AssetInventoryController@deleteAsset');

//Inventory Column Routes
$router->get('/getInventoryColumns', 'AssetInventoryController@getInventoryColumns');
$router->get('/getDeletedInventoryColumns', 'AssetInventoryController@getDeletedInventoryColumns');
$router->post('/addInventoryColumn', 'AssetInventoryController@addInventoryColumn');
$router->put('/updateInventoryColumn', 'AssetInventoryController@updateInventoryColumn');
$router->delete('/deleteInventoryColumn', 'AssetInventoryController@deleteInventoryColumn');

//Inventory Value Routes
$router->get('/getInventoryValues', 'AssetInventoryController@getInventoryValues');
$router->post('/addInventoryValue', 'AssetInventoryController@addInventoryValue');
$router->put('/updateInventoryValue', 'AssetInventoryController@updateInventoryValue');
$router->delete('/deleteInventoryValue', 'AssetInventoryController@deleteInventoryValue');

//Inventory Routes
$router->get('/getAllInventories', 'AssetInventoryController@getAllInventories');
$router->get('/getAssetInventories', 'AssetInventoryController@getAssetInventories');
$router->get('/getInventory', 'AssetInventoryController@getInventory');
$router->post('/addInventory', 'AssetInventoryController@addInventory');
$router->put('/updateInventory', 'AssetInventoryController@updateInventory');
$router->delete('/deleteInventory', 'AssetInventoryController@deleteInventory');

//Vendor Routes
$router->get('/getVendors', 'VendorController@getVendors');
$router->post('/addVendor', 'VendorController@addVendor');
$router->put('/updateVendor', 'VendorController@updateVendor');
$router->delete('/deleteVendor', 'VendorController@deleteVendor');