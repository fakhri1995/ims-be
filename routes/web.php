<?php
// use Spatie\Activitylog\Models\Activity;

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
//     return Activity::all()->last();
//     return $router->app->version();
//     return base64_encode(hash("sha1", "GET"."\n"."/admin/v1/get-company"."\n"."02 Jan 06 15:04 MST", $raw_output=TRUE));
// });

$router->get('/ping', function(){
    return "pong";
});

// ------------ Company Profile ------------ //

//Message Routes
$router->get('/getMessages', 'CompanyProfileController@getMessages');
$router->post('/addMessage', 'CompanyProfileController@addMessage');

//Career Routes
$router->get('/getCareers', 'CompanyProfileController@getCareers');
$router->post('/addCareer', 'CompanyProfileController@addCareer');
$router->put('/updateCareer', 'CompanyProfileController@updateCareer');
$router->delete('/deleteCareer', 'CompanyProfileController@deleteCareer');

// ----------------------------------------- //



//User Routes
$router->post('/login', 'UserController@login');
$router->post('/logout', 'UserController@logout');
$router->post('/detailProfile', 'UserController@detailProfile');
$router->post('/changePassword', 'UserController@changePassword');

//Log Routes
$router->get('/getActivityInventoryLogs', 'ActivityLogController@getActivityInventoryLogs');

//Company Routes
// $router->post('/getCompanyDetail', 'CompanyController@getCompanyDetail');
// $router->post('/getCompanyList', 'CompanyController@getCompanyList');
$router->post('/getCompanyClientList', 'CompanyController@getCompanyClientList');
// $router->post('/addCompanyMember', 'CompanyController@addCompanyMember');
// $router->post('/updateCompanyDetail', 'CompanyController@updateCompanyDetail');
// $router->post('/companyActivation', 'CompanyController@companyActivation');
$router->post('/getLocations', 'CompanyController@getLocations');

//MIG Company Routes
$router->post('/getMainCompanyDetail', 'CompanyController@getMainCompanyDetail');
$router->post('/updateMainCompany', 'CompanyController@updateMainCompany');

//MIG Branch Company Routes
$router->post('/getBranchCompanyList', 'CompanyController@getBranchCompanyList');
$router->post('/getCompanyBranchDetail', 'CompanyController@getCompanyBranchDetail');
$router->post('/addCompanyBranch', 'CompanyController@addCompanyBranch');
$router->post('/updateCompanyBranch', 'CompanyController@updateCompanyBranch');
$router->post('/companyBranchActivation', 'CompanyController@companyBranchActivation');

//MIG Client Company Routes
$router->post('/getClientCompanyList', 'CompanyController@getClientCompanyList');
$router->post('/getCompanyClientDetail', 'CompanyController@getCompanyClientDetail');
$router->post('/addCompanyClient', 'CompanyController@addCompanyClient');
$router->post('/updateCompanyClient', 'CompanyController@updateCompanyClient');
$router->post('/companyClientActivation', 'CompanyController@companyClientActivation');

//Account Routes
// $router->post('/getAccountDetail', 'AccountController@getAccountDetail');
// $router->post('/getAccountList', 'AccountController@getAccountList');
// $router->post('/addAccountMember', 'AccountController@addAccountMember');
// $router->post('/updateAccountDetail', 'AccountController@updateAccountDetail');
// $router->post('/changeAccountPassword', 'AccountController@changeAccountPassword');
// $router->post('/accountActivation', 'accountController@accountActivation');

//Agent Routes
$router->post('/getAgentDetail', 'AccountController@getAgentDetail');
$router->post('/getAgentList', 'AccountController@getAgentList');
$router->post('/addAgentMember', 'AccountController@addAgentMember');
$router->post('/updateAgentDetail', 'AccountController@updateAgentDetail');
$router->post('/changeAgentPassword', 'AccountController@changeAgentPassword');
$router->post('/agentActivation', 'AccountController@agentActivation');
$router->post('/updateFeatureAgent', 'AccountController@updateFeatureAgent');

//Requester Routes
$router->post('/getRequesterDetail', 'AccountController@getRequesterDetail');
$router->post('/getRequesterList', 'AccountController@getRequesterList');
$router->post('/addRequesterMember', 'AccountController@addRequesterMember');
$router->post('/updateRequesterDetail', 'AccountController@updateRequesterDetail');
$router->post('/changeRequesterPassword', 'AccountController@changeRequesterPassword');
$router->post('/requesterActivation', 'AccountController@requesterActivation');
$router->post('/updateFeatureRequester', 'AccountController@updateFeatureRequester');

//Access Feature Routes
$router->post('/getAccessModule', 'AccessFeatureController@getAccessModule');
$router->post('/getAccessFeature', 'AccessFeatureController@getAccessFeature');
$router->post('/addAccessModule', 'AccessFeatureController@addAccessModule');
$router->post('/getFeatures', 'AccessFeatureController@getFeatures');
$router->post('/addFeature', 'AccessFeatureController@addFeature');
$router->post('/deleteFeature', 'AccessFeatureController@deleteFeature');
$router->post('/updateAccessFeature', 'AccessFeatureController@updateAccessFeature');
$router->post('/updateModuleCompany', 'AccessFeatureController@updateModuleCompany');
$router->post('/updateFeatureAccount', 'AccessFeatureController@updateFeatureAccount');

//Module Routes
$router->post('/addModule', 'AccessFeatureController@addModule');
$router->post('/getModules', 'AccessFeatureController@getModules');
$router->post('/getModule', 'AccessFeatureController@getModule');
$router->post('/addModuleFeature', 'AccessFeatureController@addModuleFeature');
$router->post('/updateModuleFeature', 'AccessFeatureController@updateModuleFeature');
$router->post('/deleteModuleFeature', 'AccessFeatureController@deleteModuleFeature');
$router->post('/deleteModule', 'AccessFeatureController@deleteModule');
// $router->post('/getAccessFeature', 'AccessFeatureController@getAccessFeature');
// $router->post('/addAccessModule', 'AccessFeatureController@addAccessModule');
// $router->post('/addAccessFeature', 'AccessFeatureController@addAccessFeature');


//Bank Account Routes
// $router->get('/getBanks', 'BankController@getBanks');
// $router->post('/addBank', 'BankController@addBank');
// $router->put('/updateBank', 'BankController@updateBank');
// $router->delete('/deleteBank', 'BankController@deleteBank');

//Main Bank Account Routes
$router->get('/getMainBanks', 'BankController@getMainBanks');
$router->post('/addMainBank', 'BankController@addMainBank');
$router->put('/updateMainBank', 'BankController@updateMainBank');
$router->delete('/deleteMainBank', 'BankController@deleteMainBank');

//Client Bank Account Routes
$router->get('/getClientBanks', 'BankController@getClientBanks');
$router->post('/addClientBank', 'BankController@addClientBank');
$router->put('/updateClientBank', 'BankController@updateClientBank');
$router->delete('/deleteClientBank', 'BankController@deleteClientBank');

//Group Routes
// $router->get('/getGroups', 'GroupController@getGroups');
// $router->get('/getGroup', 'GroupController@getGroup');
// $router->post('/addGroup', 'GroupController@addGroup');
// $router->put('/updateGroup', 'GroupController@updateGroup');
// $router->delete('/deleteGroup', 'GroupController@deleteGroup');

//Agent Group Routes
$router->get('/getAgentGroups', 'GroupController@getAgentGroups');
$router->get('/getAgentGroup', 'GroupController@getAgentGroup');
$router->post('/addAgentGroup', 'GroupController@addAgentGroup');
$router->put('/updateAgentGroup', 'GroupController@updateAgentGroup');
$router->delete('/deleteAgentGroup', 'GroupController@deleteAgentGroup');

//Requester Group Routes
$router->get('/getRequesterGroups', 'GroupController@getRequesterGroups');
$router->get('/getRequesterGroup', 'GroupController@getRequesterGroup');
$router->post('/addRequesterGroup', 'GroupController@addRequesterGroup');
$router->put('/updateRequesterGroup', 'GroupController@updateRequesterGroup');
$router->delete('/deleteRequesterGroup', 'GroupController@deleteRequesterGroup');

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
$router->post('/cudInventoryColumn', 'AssetInventoryController@cudInventoryColumn');
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

//Incident Routes
$router->get('/getIncidents', 'IncidentController@getIncidents');
$router->post('/addIncident', 'IncidentController@addIncident');
$router->post('/updateIncident', 'IncidentController@updateIncident');
$router->delete('/deleteIncident', 'IncidentController@deleteIncident');

//Ticket Routes
$router->get('/getTickets', 'TicketController@getTickets');
$router->post('/addTicket', 'TicketController@addTicket');
$router->put('/updateTicket', 'TicketController@updateTicket');
$router->delete('/deleteTicket', 'TicketController@deleteTicket');

//Service Category Routes
$router->get('/getServiceCategories', 'ServiceController@getServiceCategories');
$router->post('/addServiceCategory', 'ServiceController@addServiceCategory');
$router->put('/updateServiceCategory', 'ServiceController@updateServiceCategory');
$router->delete('/deleteServiceCategory', 'ServiceController@deleteServiceCategory');

//Service Item Routes
$router->get('/getServiceItems', 'ServiceController@getServiceItems');
$router->get('/getServiceItem', 'ServiceController@getServiceItem');
$router->post('/addServiceItem', 'ServiceController@addServiceItem');
$router->put('/updateServiceItem', 'ServiceController@updateServiceItem');
$router->put('/publishingServiceItem', 'ServiceController@publishingServiceItem');
$router->put('/depublishingServiceItem', 'ServiceController@depublishingServiceItem');
$router->delete('/deleteServiceItem', 'ServiceController@deleteServiceItem');

//Service Item Contract Routes
$router->put('/activatingServiceItemContract', 'ContractController@activatingServiceItemContract');
$router->put('/deactivatingServiceItemContract', 'ContractController@deactivatingServiceItemContract');

//Contract Type Routes
$router->get('/getContractTypes', 'ContractController@getContractTypes');
$router->post('/addContractType', 'ContractController@addContractType');
$router->put('/updateContractType', 'ContractController@updateContractType');
$router->delete('/deleteContractType', 'ContractController@deleteContractType');

//Contract Routes
$router->get('/getContractInputData', 'ContractController@getContractInputData');
$router->get('/getContracts', 'ContractController@getContracts');
$router->get('/getContract', 'ContractController@getContract');
$router->post('/addContract', 'ContractController@addContract');
$router->put('/updateContract', 'ContractController@updateContract');
$router->put('/activatingContract', 'ContractController@activatingContract');
$router->put('/deactivatingContract', 'ContractController@deactivatingContract');
$router->delete('/deleteContract', 'ContractController@deleteContract');

//Depreciation Routes
$router->get('/getDepreciations', 'DepreciationController@getDepreciations');
$router->post('/addDepreciation', 'DepreciationController@addDepreciation');
$router->put('/updateDepreciation', 'DepreciationController@updateDepreciation');
$router->delete('/deleteDepreciation', 'DepreciationController@deleteDepreciation');

//Service Category Routes
$router->get('/getRoleUserFeatures', 'RoleController@getRoleUserFeatures');
$router->get('/getRoles', 'RoleController@getRoles');
$router->get('/getRole', 'RoleController@getRole');
$router->post('/addRole', 'RoleController@addRole');
$router->put('/updateRole', 'RoleController@updateRole');
$router->delete('/deleteRole', 'RoleController@deleteRole');





//Terms of Payment
$router->post('/addDefaultPayments', 'ServiceController@addDefaultPayments');
