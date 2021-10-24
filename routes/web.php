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

// ------------ Company Profile ------------ //

$router->post('/addMessage', 'CompanyProfileController@addMessage');
$router->get('/getCareers', 'CompanyProfileController@getCareers');


// ----------------------------------------- //

$router->post('/login', 'LoginController@login');

$router->group(['middleware' => 'auth'], function($router){
    
    //Log Routes
    $router->get('/getActivityInventoryLogs', 'ActivityLogController@getActivityInventoryLogs');
    $router->get('/getClientTicketLog', 'ActivityLogController@getClientTicketLog');
    $router->get('/getTicketLog', 'ActivityLogController@getTicketLog');
    $router->get('/getCloseTicketLog', 'ActivityLogController@getCloseTicketLog');

    //User Routes
    $router->post('/logout', 'LoginController@logout');
    $router->get('/detailProfile', 'LoginController@detailProfile');
    $router->post('/changePassword', 'LoginController@changePassword');

    //Message Routes
    $router->get('/getMessages', 'CompanyProfileController@getMessages');
    $router->delete('/deleteMessage', 'CompanyProfileController@deleteMessage');

    //Career Routes
    $router->get('/getCareers', 'CompanyProfileController@getCareers');
    $router->post('/addCareer', 'CompanyProfileController@addCareer');
    $router->put('/updateCareer', 'CompanyProfileController@updateCareer');
    $router->delete('/deleteCareer', 'CompanyProfileController@deleteCareer');

    //Account Routes
    //Agent Routes
    $router->get('/getAgentDetail', 'UserController@getAgentDetail');
    $router->get('/getAgentList', 'UserController@getAgentList');
    $router->post('/addAgentMember', 'UserController@addAgentMember');
    $router->put('/updateAgentDetail', 'UserController@updateAgentDetail');
    $router->put('/changeAgentPassword', 'UserController@changeAgentPassword');
    $router->put('/agentActivation', 'UserController@agentActivation');
    $router->delete('/deleteAgent', 'UserController@deleteAgent');

    //Requester Routes
    $router->get('/getRequesterDetail', 'UserController@getRequesterDetail');
    $router->get('/getRequesterList', 'UserController@getRequesterList');
    $router->post('/addRequesterMember', 'UserController@addRequesterMember');
    $router->put('/updateRequesterDetail', 'UserController@updateRequesterDetail');
    $router->put('/changeRequesterPassword', 'UserController@changeRequesterPassword');
    $router->put('/requesterActivation', 'UserController@requesterActivation');
    $router->delete('/deleteRequester', 'UserController@deleteRequester');

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

    //Company Routes
    $router->get('/getCompanyClientList', 'CompanyController@getCompanyClientList');
    $router->get('/getLocations', 'CompanyController@getLocations');

    //MIG Company Routes
    $router->get('/getMainCompanyDetail', 'CompanyController@getMainCompanyDetail');
    $router->put('/updateMainCompany', 'CompanyController@updateMainCompany');

    //MIG Branch Company Routes
    $router->get('/getBranchCompanyList', 'CompanyController@getBranchCompanyList');
    $router->get('/getCompanyBranchDetail', 'CompanyController@getCompanyBranchDetail');
    $router->post('/addCompanyBranch', 'CompanyController@addCompanyBranch');
    $router->put('/updateCompanyBranch', 'CompanyController@updateCompanyBranch');
    $router->put('/companyBranchActivation', 'CompanyController@companyBranchActivation');
    $router->delete('/deleteCompanyBranch', 'CompanyController@deleteCompanyBranch');

    //MIG Client Company Routes
    $router->get('/getClientCompanyList', 'CompanyController@getClientCompanyList');
    $router->get('/getCompanyClientDetail', 'CompanyController@getCompanyClientDetail');
    $router->post('/addCompanyClient', 'CompanyController@addCompanyClient');
    $router->put('/updateCompanyClient', 'CompanyController@updateCompanyClient');
    $router->put('/companyClientActivation', 'CompanyController@companyClientActivation');
    $router->delete('/deleteCompanyClient', 'CompanyController@deleteCompanyClient');
    
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

    //Access Feature Routes
    $router->get('/getFeatures', 'AccessFeatureController@getFeatures');
    $router->post('/addFeature', 'AccessFeatureController@addFeature');
    $router->put('/updateFeature', 'AccessFeatureController@updateFeature');
    $router->delete('/deleteFeature', 'AccessFeatureController@deleteFeature');

    //Module Routes
    $router->get('/getModules', 'AccessFeatureController@getModules');
    $router->post('/addModule', 'AccessFeatureController@addModule');
    $router->put('/updateModule', 'AccessFeatureController@updateModule');
    $router->post('/addModuleFeature', 'AccessFeatureController@addModuleFeature');
    $router->delete('/deleteModuleFeature', 'AccessFeatureController@deleteModuleFeature');
    $router->delete('/deleteModule', 'AccessFeatureController@deleteModule');

    //Role Routes
    $router->get('/getRoleUserFeatures', 'AccessFeatureController@getRoleUserFeatures');
    $router->get('/getRoles', 'AccessFeatureController@getRoles');
    $router->get('/getRole', 'AccessFeatureController@getRole');
    $router->post('/addRole', 'AccessFeatureController@addRole');
    $router->put('/updateRole', 'AccessFeatureController@updateRole');
    $router->delete('/deleteRole', 'AccessFeatureController@deleteRole');

    //Asset Routes
    $router->get('/getAssets', 'AssetController@getAssets');
    $router->get('/getAsset', 'AssetController@getAsset');
    $router->get('/getDeletedAssets', 'AssetController@getDeletedAssets');
    $router->post('/addAsset', 'AssetController@addAsset');
    $router->put('/updateAsset', 'AssetController@updateAsset');
    $router->delete('/deleteAsset', 'AssetController@deleteAsset');

    //Model Routes
    $router->get('/getModels', 'AssetController@getModels');
    $router->get('/getModel', 'AssetController@getModel');
    $router->get('/getModelRelations', 'AssetController@getModelRelations');
    $router->post('/addModel', 'AssetController@addModel');
    $router->put('/updateModel', 'AssetController@updateModel');
    $router->delete('/deleteModel', 'AssetController@deleteModel');

    //Inventory Routes
    $router->get('/getInventories', 'AssetController@getInventories');
    $router->get('/getInventory', 'AssetController@getInventory');
    $router->get('/getInventoryAddable', 'AssetController@getInventoryAddable');
    $router->get('/getInventoryRelations', 'AssetController@getInventoryRelations');
    $router->get('/getInventoryReplacements', 'AssetController@getInventoryReplacements');
    $router->get('/getChangeStatusUsageDetailList', 'AssetController@getChangeStatusUsageDetailList');
    $router->post('/addInventory', 'AssetController@addInventory');
    $router->post('/addInventoryNotes', 'AssetController@addInventoryNotes');
    $router->post('/addInventoryParts', 'AssetController@addInventoryParts');
    $router->put('/updateInventory', 'AssetController@updateInventory');
    $router->put('/updateInventoryParts', 'AssetController@updateInventoryParts');
    $router->put('/replaceInventoryPart', 'AssetController@replaceInventoryPart');
    $router->put('/changeStatusUsage', 'AssetController@changeStatusUsage');
    $router->put('/changeStatusCondition', 'AssetController@changeStatusCondition');
    $router->delete('/removeInventoryPart', 'AssetController@removeInventoryPart');
    $router->delete('/deleteInventory', 'AssetController@deleteInventory');
        
    //Manufacturer Routes
    $router->get('/getManufacturers', 'AssetController@getManufacturers');
    $router->post('/addManufacturer', 'AssetController@addManufacturer');
    $router->put('/updateManufacturer', 'AssetController@updateManufacturer');
    $router->delete('/deleteManufacturer', 'AssetController@deleteManufacturer');

    //Relationship
    $router->get('/getRelationships', 'AssetController@getRelationships');
    $router->get('/getRelationship', 'AssetController@getRelationship');
    $router->post('/addRelationship', 'AssetController@addRelationship');
    $router->put('/updateRelationship', 'AssetController@updateRelationship');
    $router->delete('/deleteRelationship', 'AssetController@deleteRelationship');

    //Relationship Asset
    $router->get('/getRelationshipAssets', 'AssetController@getRelationshipAssets');
    $router->get('/getRelationshipAsset', 'AssetController@getRelationshipAsset');
    $router->get('/getRelationshipAssetRelation', 'AssetController@getRelationshipAssetRelation');
    $router->get('/getRelationshipAssetDetailList', 'AssetController@getRelationshipAssetDetailList');
    $router->post('/addRelationshipAsset', 'AssetController@addRelationshipAsset');
    $router->put('/updateRelationshipAsset', 'AssetController@updateRelationshipAsset');
    $router->delete('/deleteRelationshipAsset', 'AssetController@deleteRelationshipAsset');

    //Relationship Inventory
    // $router->get('/getRelationshipInventories', 'AssetController@getRelationshipInventories');
    $router->get('/getRelationshipInventory', 'AssetController@getRelationshipInventory');
    $router->get('/getRelationshipInventoryRelation', 'AssetController@getRelationshipInventoryRelation');
    $router->get('/getRelationshipInventoryDetailList', 'AssetController@getRelationshipInventoryDetailList');
    $router->post('/addRelationshipInventories', 'AssetController@addRelationshipInventories');
    $router->put('/updateRelationshipInventory', 'AssetController@updateRelationshipInventory');
    $router->delete('/deleteRelationshipInventory', 'AssetController@deleteRelationshipInventory');

    //Ticket Routes
    $router->get('/getTicketRelation', 'TicketController@getTicketRelation');
    $router->get('/getClientTicketRelation', 'TicketController@getClientTicketRelation');
    $router->get('/getClosedTickets', 'TicketController@getClosedTickets');
    $router->get('/getClientClosedTickets', 'TicketController@getClientClosedTickets');
    $router->get('/getTickets', 'TicketController@getTickets');
    $router->get('/getClientTickets', 'TicketController@getClientTickets');
    $router->get('/getTicket', 'TicketController@getTicket');
    $router->get('/getClientTicket', 'TicketController@getClientTicket');
    $router->post('/addTicket', 'TicketController@addTicket');
    $router->put('/setItemTicket', 'TicketController@setItemTicket');
    $router->put('/changeStatusTicket', 'TicketController@changeStatusTicket');
    $router->put('/cancelClientTicket', 'TicketController@cancelClientTicket');
    $router->put('/assignTicket', 'TicketController@assignTicket');
});

//Group Routes
// $router->get('/getGroups', 'GroupController@getGroups');
// $router->get('/getGroup', 'GroupController@getGroup');
// $router->post('/addGroup', 'GroupController@addGroup');
// $router->put('/updateGroup', 'GroupController@updateGroup');
// $router->delete('/deleteGroup', 'GroupController@deleteGroup');


//Group Routes
// $router->post('/attachPivotGU', 'GroupUserPivotController@attachPivotGU');
// $router->delete('/detachPivotGU', 'GroupUserPivotController@detachPivotGU');




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






// //Terms of Payment
// $router->post('/addDefaultPayments', 'ServiceController@addDefaultPayments');
