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
$router->get('/getArticle', 'CompanyProfileController@getArticle');
$router->get('/getCountArticle', 'CompanyProfileController@getCountArticle');
$router->get('/getCountCustomerStories', 'CompanyProfileController@getCountCustomerStories');
$router->get('/getArticleList', 'CompanyProfileController@getArticleList');
$router->get('/getArticlePopularList', 'CompanyProfileController@getArticlePopularList');
$router->get('/getTestimonialLandingPage', 'CompanyProfileController@getTestimonialLandingPage');
$router->get('/getOtherTestimonial', 'CompanyProfileController@getOtherTestimonial');
$router->get('/getTestimonialHardwarePage', 'CompanyProfileController@getTestimonialHardwarePage');
$router->get('/getTestimonialSoftwarePage', 'CompanyProfileController@getTestimonialSoftwarePage');
$router->get('/getTestimonialTalentPage', 'CompanyProfileController@getTestimonialTalentPage');
$router->get('/getCustomerStoriesPage', 'CompanyProfileController@getCustomerStoriesPage');
$router->get('/getArticleDetail', 'CompanyProfileController@getArticleDetail');
$router->get('/getArticleDetailLanding', 'CompanyProfileController@getArticleDetailLanding');
$router->get('/getTestimonialDetail', 'CompanyProfileController@getTestimonialDetail');
$router->get('/getProduct', 'CompanyProfileController@getProduct');
$router->get('/getProductDetail', 'CompanyProfileController@getProductDetail');
$router->post('/addFormSolution', 'CompanyProfileController@addFormSolution');
$router->post('/addFormSolutionTalent', 'CompanyProfileController@addFormSolutionTalent');
$router->post('/addFormSolutionHardware', 'CompanyProfileController@addFormSolutionHardware');
// ----------------------------------------- //

$router->post('/login', 'LoginController@login');
$router->post('/mailForgetPassword', 'LoginController@mailForgetPassword');
$router->post('/resetPassword', 'LoginController@resetPassword');

$router->group(['middleware' => 'auth'], function ($router) {
    //Android Routes
    $router->get('/getMainAndroid', 'AndroidController@getMainAndroid');

    //Log Routes
    $router->get('/getActivityInventoryLogs', 'ActivityLogController@getActivityInventoryLogs');
    $router->get('/getClientTicketLog', 'ActivityLogController@getClientTicketLog');
    $router->get('/getTicketLog', 'ActivityLogController@getTicketLog');
    $router->get('/getCompanyLog', 'ActivityLogController@getCompanyLog');
    $router->get('/getRecruitmentLog', 'ActivityLogController@getRecruitmentLog');

    //User Routes
    $router->post('/addAndroidToken', 'LoginController@addAndroidToken');
    $router->post('/logout', 'LoginController@logout');
    $router->get('/detailProfile', 'LoginController@detailProfile');
    $router->post('/updateProfile', 'LoginController@updateProfile');
    $router->post('/changePassword', 'LoginController@changePassword');

    //Message Routes
    $router->get('/getMessages', 'CompanyProfileController@getMessages');
    $router->delete('/deleteMessage', 'CompanyProfileController@deleteMessage');

    //formsolution routes

    $router->get('/getFormSolution', 'CompanyProfileController@getFormSolution');
    $router->get('/getFormSolutionDetail', 'CompanyProfileController@getFormSolutionDetail');

    //blog routes
    $router->post('/addArticle', 'CompanyProfileController@addArticle');
    $router->post('/uploadFile', 'CompanyProfileController@uploadFile');

    $router->delete('/deleteArticle', 'CompanyProfileController@deleteArticle');
    $router->post('/updateArticle', 'CompanyProfileController@updateArticle');
    //product routes
    $router->post('/addProduct', 'CompanyProfileController@addProduct');
    $router->delete('/deleteProduct', 'CompanyProfileController@deleteProduct');
    $router->post('/updateProduct', 'CompanyProfileController@updateProduct');
    //Career Routes
    $router->post('/addCareer', 'CompanyProfileController@addCareer');
    $router->put('/updateCareer', 'CompanyProfileController@updateCareer');
    $router->delete('/deleteCareer', 'CompanyProfileController@deleteCareer');

    //Account Routes
    $router->get('/getFilterUsers', 'UserController@getFilterUsers');

    //Agent Routes
    $router->get('/getAgentDetail', 'UserController@getAgentDetail');
    $router->get('/getAgentList', 'UserController@getAgentList');
    $router->post('/addAgentMember', 'UserController@addAgentMember');
    $router->post('/updateAgentDetail', 'UserController@updateAgentDetail');
    $router->put('/changeAgentPassword', 'UserController@changeAgentPassword');
    $router->put('/agentActivation', 'UserController@agentActivation');
    $router->delete('/deleteAgent', 'UserController@deleteAgent');

    //Requester Routes
    $router->get('/getRequesterDetail', 'UserController@getRequesterDetail');
    $router->get('/getRequesterList', 'UserController@getRequesterList');
    $router->post('/addRequesterMember', 'UserController@addRequesterMember');
    $router->post('/updateRequesterDetail', 'UserController@updateRequesterDetail');
    $router->put('/changeRequesterPassword', 'UserController@changeRequesterPassword');
    $router->put('/requesterActivation', 'UserController@requesterActivation');
    $router->delete('/deleteRequester', 'UserController@deleteRequester');

    //Guest Routes
    $router->get('/getGuestDetail', 'UserController@getGuestDetail');
    $router->get('/getGuestList', 'UserController@getGuestList');
    $router->post('/addGuestMember', 'UserController@addGuestMember');
    $router->post('/updateGuestDetail', 'UserController@updateGuestDetail');
    $router->put('/changeGuestPassword', 'UserController@changeGuestPassword');
    $router->put('/guestActivation', 'UserController@guestActivation');
    $router->delete('/deleteGuest', 'UserController@deleteGuest');

    $router->get('/getFilterGroups', 'GroupController@getFilterGroups');
    $router->get('/getFilterGroupsWithUsers', 'GroupController@getFilterGroupsWithUsers');

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

    //Product Inventory Group Routes
    $router->get('/getProductInventories', 'InventoryController@getProductInventories');
    $router->get('/getProductInventory', 'InventoryController@getProductInventory');
    $router->post('/addProductInventory', 'InventoryController@addProductInventory');
    $router->put('/updateProductInventory', 'InventoryController@updateProductInventory');
    $router->delete('/deleteProductInventory', 'InventoryController@deleteProductInventory');
    $router->get('/getProductInventoryId', 'InventoryController@getProductInventoryId');


    $router->get('/getCategories', 'InventoryController@getCategories');
    $router->get('/getCategory', 'InventoryController@getCategory');
    $router->post('/addCategory', 'InventoryController@addCategory');
    $router->put('/updateCategory', 'InventoryController@updateCategory');
    $router->delete('/deleteCategory', 'InventoryController@deleteCategory');

    //General Company Routes
    $router->get('/getCompanyRelationshipInventory', 'AssetController@getCompanyRelationshipInventory');
    $router->get('/getCompanyInventories', 'AssetController@getCompanyInventories');
    $router->get('/getCompanyClientList', 'CompanyController@getCompanyClientList');
    $router->get('/getAllCompanyList', 'CompanyController@getAllCompanyList');
    $router->get('/getMainLocations', 'CompanyController@getMainLocations');
    $router->get('/getLocations', 'CompanyController@getLocations');
    $router->get('/getSubLocations', 'CompanyController@getSubLocations');
    $router->get('/getCompanyDetail', 'CompanyController@getCompanyDetail');
    $router->get('/getSubCompanyDetail', 'CompanyController@getSubCompanyDetail');
    $router->get('/getSubCompanyProfile', 'CompanyController@getSubCompanyProfile');
    $router->post('/updateMainCompany', 'CompanyController@updateMainCompany');
    $router->post('/updateCompany', 'CompanyController@updateCompany');
    $router->put('/companyActivation', 'CompanyController@companyActivation');
    $router->delete('/deleteCompany', 'CompanyController@deleteCompany');

    // Add Route Company
    $router->post('/addCompanyBranch', 'CompanyController@addCompanyBranch');
    $router->post('/addCompanyClient', 'CompanyController@addCompanyClient');
    $router->post('/addCompanySub', 'CompanyController@addCompanySub');

    // Tree Company List
    $router->get('/getBranchCompanyList', 'CompanyController@getBranchCompanyList');
    $router->get('/getClientCompanyList', 'CompanyController@getClientCompanyList');

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
    // $router->post('/addFeature', 'AccessFeatureController@addFeature');
    // $router->put('/updateFeature', 'AccessFeatureController@updateFeature');
    // $router->delete('/deleteFeature', 'AccessFeatureController@deleteFeature');

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
    $router->post('/addAsset', 'AssetController@addAsset');
    $router->put('/updateAsset', 'AssetController@updateAsset');
    $router->delete('/deleteAsset', 'AssetController@deleteAsset');

    //Model Routes
    $router->get('/getFilterModels', 'AssetController@getFilterModels');
    $router->get('/getModels', 'AssetController@getModels');
    $router->get('/getModel', 'AssetController@getModel');
    $router->get('/getModelRelations', 'AssetController@getModelRelations');
    $router->post('/addModel', 'AssetController@addModel');
    $router->put('/updateModel', 'AssetController@updateModel');
    $router->delete('/deleteModel', 'AssetController@deleteModel');

    //Inventory Routes
    $router->get('/getInventories', 'AssetController@getInventories');
    $router->get('/getFilterInventories', 'AssetController@getFilterInventories');
    $router->get('/getInventory', 'AssetController@getInventory');
    $router->get('/getInventoryAddable', 'AssetController@getInventoryAddable');
    $router->get('/getInventoryRelations', 'AssetController@getInventoryRelations');
    $router->get('/getInventoryReplacements', 'AssetController@getInventoryReplacements');
    $router->get('/getChangeStatusUsageDetailList', 'AssetController@getChangeStatusUsageDetailList');
    $router->post('/addInventory', 'AssetController@addInventory');
    $router->post('/addInventoryNotes', 'AssetController@addInventoryNotes');
    $router->post('/addInventoryParts', 'AssetController@addInventoryParts');
    $router->post('/importInventories', 'AssetController@importInventories');
    $router->put('/updateInventory', 'AssetController@updateInventory');
    $router->put('/replaceInventoryPart', 'AssetController@replaceInventoryPart');
    $router->put('/changeStatusUsage', 'AssetController@changeStatusUsage');
    $router->put('/changeStatusRent', 'AssetController@changeStatusRent');
    $router->put('/changeStatusCondition', 'AssetController@changeStatusCondition');
    $router->delete('/removeInventoryPart', 'AssetController@removeInventoryPart');
    $router->delete('/deleteInventory', 'AssetController@deleteInventory');

    //Manufacturer Routes
    $router->get('/getManufacturers', 'AssetController@getManufacturers');
    $router->post('/addManufacturer', 'AssetController@addManufacturer');
    $router->put('/updateManufacturer', 'AssetController@updateManufacturer');
    $router->delete('/deleteManufacturer', 'AssetController@deleteManufacturer');

    //Vendor Routes
    $router->get('/getVendors', 'AssetController@getVendors');
    $router->post('/addVendor', 'AssetController@addVendor');
    $router->put('/updateVendor', 'AssetController@updateVendor');
    $router->delete('/deleteVendor', 'AssetController@deleteVendor');

    //Relationship
    $router->get('/getRelationships', 'AssetController@getRelationships');
    $router->post('/addRelationship', 'AssetController@addRelationship');
    $router->put('/updateRelationship', 'AssetController@updateRelationship');
    $router->delete('/deleteRelationship', 'AssetController@deleteRelationship');

    //Relationship Inventory
    $router->get('/getRelationshipInventory', 'AssetController@getRelationshipInventory');
    $router->get('/getRelationshipInventoryRelation', 'AssetController@getRelationshipInventoryRelation');
    $router->get('/getRelationshipInventoryDetailList', 'AssetController@getRelationshipInventoryDetailList');
    $router->post('/addRelationshipInventories', 'AssetController@addRelationshipInventories');
    $router->put('/updateRelationshipInventory', 'AssetController@updateRelationshipInventory');
    $router->delete('/deleteRelationshipInventory', 'AssetController@deleteRelationshipInventory');

    //Ticket Routes
    $router->get('/getFilterTickets', 'TicketController@getFilterTickets');
    $router->get('/getTicketRelation', 'TicketController@getTicketRelation');
    $router->get('/getClientTicketRelation', 'TicketController@getClientTicketRelation');
    $router->get('/getClosedTickets', 'TicketController@getClosedTickets');
    $router->get('/getClientClosedTickets', 'TicketController@getClientClosedTickets');
    $router->get('/getTicketStatusCounts', 'TicketController@getTicketStatusCounts');
    $router->get('/getClientTicketStatusCounts', 'TicketController@getClientTicketStatusCounts');
    $router->get('/getTickets', 'TicketController@getTickets');
    $router->get('/getClientTickets', 'TicketController@getClientTickets');
    $router->get('/getTicket', 'TicketController@getTicket');
    $router->get('/getClientTicket', 'TicketController@getClientTicket');
    $router->get('/getAssignToList', 'GroupController@getAssignToList');
    $router->get('/ticketsExport', 'TicketController@ticketsExport');
    $router->get('/ticketExport', 'TicketController@ticketExport');
    $router->get('/clientTicketExport', 'TicketController@clientTicketExport');
    $router->post('/addTicket', 'TicketController@addTicket');
    $router->post('/addNoteTicket', 'TicketController@addNoteTicket');
    $router->post('/clientAddNoteTicket', 'TicketController@clientAddNoteTicket');
    $router->post('/updateTicket', 'TicketController@updateTicket');
    $router->put('/setItemTicket', 'TicketController@setItemTicket');
    $router->put('/cancelTicket', 'TicketController@cancelTicket');
    $router->put('/cancelClientTicket', 'TicketController@cancelClientTicket');
    // $router->put('/assignTicket', 'TicketController@assignTicket');
    $router->put('/updateStatusTicket', 'TicketController@updateStatusTicket');
    $router->put('/updateNoteTicket', 'TicketController@updateNoteTicket');
    $router->put('/clientUpdateNoteTicket', 'TicketController@clientUpdateNoteTicket');
    $router->put('/setDeadline', 'TicketController@setDeadline');
    $router->delete('/deleteNoteTicket', 'TicketController@deleteNoteTicket');
    $router->delete('/clientDeleteNoteTicket', 'TicketController@clientDeleteNoteTicket');
    $router->delete('/deleteFileTicket', 'TicketController@deleteFileTicket');

    $router->get('/getTicketDetailTypes', 'TicketController@getTicketDetailTypes');
    $router->post('/addTicketDetailType', 'TicketController@addTicketDetailType');
    $router->put('/updateTicketDetailType', 'TicketController@updateTicketDetailType');
    $router->delete('/deleteTicketDetailType', 'TicketController@deleteTicketDetailType');


    //Task Routes
    $router->get('/getStatusTaskList', 'TaskController@getStatusTaskList');
    $router->get('/getDeadlineTasks', 'TaskController@getDeadlineTasks');
    $router->get('/getTaskStaffCounts', 'TaskController@getTaskStaffCounts');
    $router->get('/getStaffTaskStatuses', 'TaskController@getStaffTaskStatuses');
    $router->get('/getUserTaskStatusList', 'TaskController@getUserTaskStatusList');
    $router->get('/getUserLastTwoTasks', 'TaskController@getUserLastTwoTasks');
    $router->get('/getUserTaskTypeCounts', 'TaskController@getUserTaskTypeCounts');
    $router->get('/getTaskSparePartList', 'TaskController@getTaskSparePartList');
    $router->get('/getTaskPickList', 'TaskController@getTaskPickList');
    $router->get('/getTasks', 'TaskController@getTasks');
    $router->get('/getUserTasks', 'TaskController@getUserTasks');
    $router->get('/getTask', 'TaskController@getTask');
    $router->post('/addTask', 'TaskController@addTask');
    $router->post('/sendInventoriesTask', 'TaskController@sendInventoriesTask');
    // $router->post('/sendInInventoryTask', 'TaskController@sendInInventoryTask');
    // $router->post('/sendOutInventoryTask', 'TaskController@sendOutInventoryTask');
    $router->put('/updateTask', 'TaskController@updateTask');
    $router->post('/saveFilesTask', 'TaskController@saveFilesTask');
    $router->put('/changeStatusToggle', 'TaskController@changeStatusToggle');
    $router->put('/changeAttendanceToggle', 'TaskController@changeAttendanceToggle');
    $router->put('/rejectTask', 'TaskController@rejectTask');
    $router->put('/approveTask', 'TaskController@approveTask');
    $router->put('/submitTask', 'TaskController@submitTask');
    $router->put('/declineTask', 'TaskController@declineTask');
    $router->put('/assignSelfTask', 'TaskController@assignSelfTask');
    $router->delete('/deleteTask', 'TaskController@deleteTask');
    $router->delete('/deleteFileTask', 'TaskController@deleteFileTask');
    $router->delete('/cancelSendOutInventoryTask', 'TaskController@cancelSendOutInventoryTask');
    $router->delete('/cancelSendInInventoryTask', 'TaskController@cancelSendInInventoryTask');
    $router->post('/addTaskReport', 'TaskController@addTaskReport');
    $router->get('/getTaskReports', 'TaskController@getTaskReports');
    $router->get('/getTaskReport', 'TaskController@getTaskReport');
    $router->delete('/deleteTaskReport', 'TaskController@deleteTaskReport');

    //Task Detail Routes
    $router->post('/addTaskDetail', 'TaskController@addTaskDetail');
    $router->put('/updateTaskDetail', 'TaskController@updateTaskDetail');
    $router->put('/assignTaskDetail', 'TaskController@assignTaskDetail');
    $router->put('/fillTaskDetail', 'TaskController@fillTaskDetail');
    $router->put('/fillTaskDetails', 'TaskController@fillTaskDetails');
    $router->delete('/deleteTaskDetail', 'TaskController@deleteTaskDetail');

    //Type Task Routes
    $router->get('/getTaskTypeCounts', 'TaskController@getTaskTypeCounts');
    $router->get('/getFilterTaskTypes', 'TaskController@getFilterTaskTypes');
    $router->get('/getTaskTypes', 'TaskController@getTaskTypes');
    $router->get('/getTaskType', 'TaskController@getTaskType');
    $router->post('/addTaskType', 'TaskController@addTaskType');
    $router->put('/updateTaskType', 'TaskController@updateTaskType');
    $router->delete('/deleteTaskType', 'TaskController@deleteTaskType');

    //Warehouse
    //Purchase Order
    $router->get('/getPurchaseOrders', 'WarehouseController@getPurchaseOrders');
    $router->get('/getPurchaseOrder', 'WarehouseController@getPurchaseOrder');
    $router->post('/addPurchaseOrder', 'WarehouseController@addPurchaseOrder');
    $router->put('/updatePurchaseOrder', 'WarehouseController@updatePurchaseOrder');
    $router->delete('/deletePurchaseOrder', 'WarehouseController@deletePurchaseOrder');
    $router->put('/acceptPurchaseOrder', 'WarehouseController@acceptPurchaseOrder');
    $router->put('/rejectPurchaseOrder', 'WarehouseController@rejectPurchaseOrder');
    $router->put('/sendPurchaseOrder', 'WarehouseController@sendPurchaseOrder');
    $router->put('/receivePurchaseOrder', 'WarehouseController@receivePurchaseOrder');

    //Detail Purchase Order
    $router->get('/getDetailPurchaseOrders', 'WarehouseController@getDetailPurchaseOrders');
    $router->post('/addDetailPurchaseOrder', 'WarehouseController@addDetailPurchaseOrder');
    $router->put('/updateDetailPurchaseOrder', 'WarehouseController@updateDetailPurchaseOrder');
    $router->delete('/deleteDetailPurchaseOrder', 'WarehouseController@deleteDetailPurchaseOrder');

    //Quality Control Purchase
    $router->get('/getQualityControlPurchases', 'WarehouseController@getQualityControlPurchases');
    $router->get('/getQualityControlPurchase', 'WarehouseController@getQualityControlPurchase');
    $router->put('/saveQC', 'WarehouseController@saveQC');


    //Attendance Management
    //Form Routes
    $router->get('/getAttendanceForms', 'AttendanceController@getAttendanceForms');
    $router->get('/getAttendanceForm', 'AttendanceController@getAttendanceForm');
    $router->post('/addAttendanceForm', 'AttendanceController@addAttendanceForm');
    $router->post('/addUserAttendanceForm', 'AttendanceController@addUserAttendanceForm');
    $router->put('/updateAttendanceForm', 'AttendanceController@updateAttendanceForm');
    $router->delete('/deleteAttendanceForm', 'AttendanceController@deleteAttendanceForm');
    $router->delete('/removeUserAttendanceForm', 'AttendanceController@removeUserAttendanceForm');

    //Activity Routes
    $router->get('/getAttendanceActivities', 'AttendanceController@getAttendanceActivities');
    $router->get('/getAttendanceActivitiesAdmin', 'AttendanceController@getAttendanceActivitiesAdmin');
    // $router->get('/getAttendanceActivity', 'AttendanceController@getAttendanceActivity');
    $router->post('/addAttendanceActivity', 'AttendanceController@addAttendanceActivity');
    $router->put('/updateAttendanceActivity', 'AttendanceController@updateAttendanceActivity');
    $router->post('/updateAttendanceActivity', 'AttendanceController@updateAttendanceActivity');
    $router->delete('/deleteAttendanceActivity', 'AttendanceController@deleteAttendanceActivity');
    $router->get('/getAttendanceTaskActivity', 'AttendanceController@getAttendanceTaskActivity');
    $router->get('/getAttendanceTaskActivities', 'AttendanceController@getAttendanceTaskActivities');
    $router->get('/getUserAttendanceTaskActivitiesAdmin', 'AttendanceController@getUserAttendanceTaskActivitiesAdmin');
    $router->get('/getAttendanceTaskActivitiesAdmin', 'AttendanceController@getAttendanceTaskActivitiesAdmin');
    $router->post('/addAttendanceTaskActivity', 'AttendanceController@addAttendanceTaskActivity');
    $router->post('/addAttendanceTaskActivitySubmit', 'AttendanceController@addAttendanceTaskActivitySubmit');
    $router->post('/addAttendanceTaskActivities', 'AttendanceController@addAttendanceTaskActivities');
    $router->put('/updateAttendanceTaskActivity', 'AttendanceController@updateAttendanceTaskActivity');
    $router->delete('/deleteAttendanceTaskActivity', 'AttendanceController@deleteAttendanceTaskActivity');
    $router->get('/getAttendanceLateCount', 'AttendanceController@getAttendanceLateCount');

    //Attendance User
    $router->get('/getAttendancesUsers', 'AttendanceController@getAttendancesUsers');
    $router->get('/getAttendancesClient', 'AttendanceController@getAttendancesClient');
    $router->get('/getAttendancesUserMonthly', 'AttendanceController@getAttendancesUserMonthly');
    $router->get('/getAttendancesUsersPaginate', 'AttendanceController@getAttendancesUsersPaginate');
    $router->get('/getAttendancesUser', 'AttendanceController@getAttendancesUser');
    $router->get('/getAttendanceUser', 'AttendanceController@getAttendanceUser');
    $router->get('/getAttendanceUserAdmin', 'AttendanceController@getAttendanceUserAdmin');
    $router->get('/exportAttendanceActivityUser', 'AttendanceController@exportAttendanceActivityUser');
    $router->get('/exportAttendanceActivityUsers', 'AttendanceController@exportAttendanceActivityUsers');
    $router->post('/setAttendanceToggle', 'AttendanceController@setAttendanceToggle');


    //Attendance Project Routes
    $router->get('/getAttendanceProjects', 'AttendanceController@getAttendanceProjects');
    $router->post('/addAttendanceProject', 'AttendanceController@addAttendanceProject');
    $router->put('/updateAttendanceProject', 'AttendanceController@updateAttendanceProject');
    $router->delete('/deleteAttendanceProject', 'AttendanceController@deleteAttendanceProject');

    //Attendance Project Category Routes
    $router->get('/getAttendanceProjectCategories', 'AttendanceController@getAttendanceProjectCategories');
    $router->post('/addAttendanceProjectCategory', 'AttendanceController@addAttendanceProjectCategory');
    $router->put('/updateAttendanceProjectCategory', 'AttendanceController@updateAttendanceProjectCategory');
    $router->delete('/deleteAttendanceProjectCategory', 'AttendanceController@deleteAttendanceProjectCategory');

    //Attendance Project Type Routes
    $router->get('/getAttendanceProjectTypes', 'AttendanceController@getAttendanceProjectTypes');
    $router->post('/addAttendanceProjectType', 'AttendanceController@addAttendanceProjectType');
    $router->put('/updateAttendanceProjectType', 'AttendanceController@updateAttendanceProjectType');
    $router->delete('/deleteAttendanceProjectType', 'AttendanceController@deleteAttendanceProjectType');

    //Attendance Project Status Routes
    $router->get('/getAttendanceProjectStatuses', 'AttendanceController@getAttendanceProjectStatuses');
    $router->post('/addAttendanceProjectStatus', 'AttendanceController@addAttendanceProjectStatus');
    $router->put('/updateAttendanceProjectStatus', 'AttendanceController@updateAttendanceProjectStatus');
    $router->delete('/deleteAttendanceProjectStatus', 'AttendanceController@deleteAttendanceProjectStatus');

    //Notification Routes
    $router->get('/getNotification', 'NotificationController@getNotification');
    $router->get('/getNotifications', 'NotificationController@getNotifications');
    $router->post('/readNotification', 'NotificationController@readNotification');
    $router->post('/readAllNotifications', 'NotificationController@readAllNotifications');

    //Career V2 Routes
    $router->group(['prefix' => 'v2'], function () use ($router) {
        $router->get('/getCareer', 'CareerV2Controller@getCareer');
        $router->get('/getCareers', 'CareerV2Controller@getCareers');
        $router->post('/addCareer', 'CareerV2Controller@addCareer');
        $router->put('/updateCareer', 'CareerV2Controller@updateCareer');
        $router->delete('/deleteCareer', 'CareerV2Controller@deleteCareer');

        $router->get('/getCountCareerApplicant', 'CareerV2Controller@getCountCareerApplicant');
        $router->get('/getCountCareersApplicant', 'CareerV2Controller@getCountCareersApplicant');
        $router->get('/getMostCareersApplicant', 'CareerV2Controller@getMostCareersApplicant');
        $router->get('/getCountCareerPosted', 'CareerV2Controller@getCountCareerPosted');
        $router->get('/getCountCareer', 'CareerV2Controller@getCountCareerPosted');
        $router->get('/exportCareersApplicant', 'CareerV2Controller@exportCareersApplicant');


        $router->get('/getCareerApply', 'CareerV2Controller@getCareerApply');
        $router->get('/getCareerApplys', 'CareerV2Controller@getCareerApplys');
        $router->post('/updateCareerApply', 'CareerV2Controller@updateCareerApply');
        $router->delete('/deleteCareerApply', 'CareerV2Controller@deleteCareerApply');
    });

    // RESUME
    $router->get('/getResumes', 'ResumeController@getResumes');
    $router->get('/getResume', 'ResumeController@getResume');
    $router->post('/addResume', 'ResumeController@addResume');
    $router->post('/addResumeSection', 'ResumeController@addResumeSection');
    $router->put('/updateResume', 'ResumeController@updateResume');
    $router->delete('/deleteResume', 'ResumeController@deleteResume');
    $router->get('/getCountResume', 'ResumeController@getCountResume');
    $router->delete('/deleteResumeAssessment', 'ResumeController@deleteResumeAssessment');
    $router->delete('deleteResumeSection', 'ResumeController@deleteResumeSection');

    $router->get('/getAssessment', 'ResumeController@getAssessment');
    $router->get('/getAssessments', 'ResumeController@getAssessments');
    $router->get('/getCountAssessment', 'ResumeController@getCountAssessment');
    $router->post('/addAssessment', 'ResumeController@addAssessment');
    $router->put('/updateAssessment', 'ResumeController@updateAssessment');
    $router->post('/addResumeAssessment', 'ResumeController@addResumeAssessment');
    $router->put('/updateResumeAssessment', 'ResumeController@updateResumeAssessment');
    $router->delete('/deleteAssessment', 'ResumeController@deleteAssessment');
    $router->get('/getAssessmentList', 'ResumeController@getAssessmentList');
    $router->get('/getSkillLists', 'ResumeController@getSkillLists');

    // TALENT POOL
    $router->get('/getTalentPools', 'TalentPoolController@getTalentPools');
    $router->get('/getTalentPool', 'TalentPoolController@getTalentPool');
    $router->post('/addTalentPool', 'TalentPoolController@addTalentPool');
    $router->delete('/deleteTalentPool', 'TalentPoolController@deleteTalentPool');
    $router->get('/getTalentPoolCandidates', 'TalentPoolController@getTalentPoolCandidates');
    $router->get('/getTalentPoolFilters', 'TalentPoolController@getTalentPoolFilters');

    // TALENT POOL CATOGORIES
    $router->get('/getTalentPoolCategories', 'TalentPoolController@getTalentPoolCategories');
    $router->post('/addTalentPoolCategory', 'TalentPoolController@addTalentPoolCategory');
    $router->delete('/deleteTalentPoolCategory', 'TalentPoolController@deleteTalentPoolCategory');

    // TALENT POOL SHARE
    $router->get('/getTalentPoolShares', 'TalentPoolController@getTalentPoolShares');
    $router->post('/addTalentPoolShare', 'TalentPoolController@addTalentPoolShare');
    $router->delete('/deleteTalentPoolShare', 'TalentPoolController@deleteTalentPoolShare');

    $router->get('/getTalentPoolShares', 'TalentPoolController@getTalentPoolShares');
    $router->post('/addTalentPoolShare', 'TalentPoolController@addTalentPoolShare');
    $router->delete('/deleteTalentPoolShare', 'TalentPoolController@deleteTalentPoolShare');

    // RECRUITMENT
    $router->get('/getRecruitmentExcelTemplate', 'RecruitmentController@getRecruitmentExcelTemplate');
    $router->get('/getRecruitment', 'RecruitmentController@getRecruitment');
    $router->get('/getRecruitments', 'RecruitmentController@getRecruitments');
    $router->post('/addRecruitment', 'RecruitmentController@addRecruitment');
    $router->post('/addRecruitments', 'RecruitmentController@addRecruitments');
    $router->put('/updateRecruitment', 'RecruitmentController@updateRecruitment');
    $router->delete('/deleteRecruitment', 'RecruitmentController@deleteRecruitment');
    $router->delete('/deleteRecruitments', 'RecruitmentController@deleteRecruitments');
    $router->get('/getCountRecruitment', 'RecruitmentController@getCountRecruitment');
    $router->put('/updateRecruitment/stage', 'RecruitmentController@updateRecruitment_stage');
    $router->put('/updateRecruitment/status', 'RecruitmentController@updateRecruitment_status');
    $router->post('/addRecruitmentLogNotes', 'RecruitmentController@addRecruitmentLogNotes');
    $router->put('/updateRecruitments/stage', 'RecruitmentController@updateRecruitments_stage');
    $router->put('/updateRecruitments/status', 'RecruitmentController@updateRecruitments_status');
    $router->get('/getRecruitmentPreviewStageStatus', 'RecruitmentController@getRecruitmentPreviewStageStatus');

    $router->post('/generateRecruitmentAccount', 'RecruitmentController@generateRecruitmentAccount');
    $router->get('/getRecruitmentAccountToken', 'RecruitmentController@getRecruitmentAccountToken');

    // RECRUITMENT ROLE
    $router->get('/getRecruitmentRole', 'RecruitmentController@getRecruitmentRole');
    $router->get('/getRecruitmentRoles', 'RecruitmentController@getRecruitmentRoles');
    $router->get('/getRecruitmentRolesList', 'RecruitmentController@getRecruitmentRolesList');
    $router->post('/addRecruitmentRole', 'RecruitmentController@addRecruitmentRole');
    $router->put('/updateRecruitmentRole', 'RecruitmentController@updateRecruitmentRole');
    $router->delete('/deleteRecruitmentRole', 'RecruitmentController@deleteRecruitmentRole');
    $router->get('/getRecruitmentRoleTypesList', 'RecruitmentController@getRecruitmentRoleTypesList');
    // RECRUITMENT STATUS
    $router->get('/getRecruitmentStatus', 'RecruitmentController@getRecruitmentStatus');
    $router->get('/getRecruitmentStatuses', 'RecruitmentController@getRecruitmentStatuses');
    $router->get('/getRecruitmentStatusesList', 'RecruitmentController@getRecruitmentStatusesList');
    $router->post('/addRecruitmentStatus', 'RecruitmentController@addRecruitmentStatus');
    $router->put('/updateRecruitmentStatus', 'RecruitmentController@updateRecruitmentStatus');
    $router->delete('/deleteRecruitmentStatus', 'RecruitmentController@deleteRecruitmentStatus');
    // RECRUITMENT STAGE
    $router->get('/getRecruitmentStage', 'RecruitmentController@getRecruitmentStage');
    $router->get('/getRecruitmentStages', 'RecruitmentController@getRecruitmentStages');
    $router->get('/getRecruitmentStagesList', 'RecruitmentController@getRecruitmentStagesList');
    $router->post('/addRecruitmentStage', 'RecruitmentController@addRecruitmentStage');
    $router->put('/updateRecruitmentStage', 'RecruitmentController@updateRecruitmentStage');
    $router->delete('/deleteRecruitmentStage', 'RecruitmentController@deleteRecruitmentStage');
    // RECRUITMENT JALUR DAFTAR
    $router->get('/getRecruitmentJalurDaftar', 'RecruitmentController@getRecruitmentJalurDaftar');
    $router->get('/getRecruitmentJalurDaftars', 'RecruitmentController@getRecruitmentJalurDaftars');
    $router->get('/getRecruitmentJalurDaftarsList', 'RecruitmentController@getRecruitmentJalurDaftarsList');
    $router->post('/addRecruitmentJalurDaftar', 'RecruitmentController@addRecruitmentJalurDaftar');
    $router->put('/updateRecruitmentJalurDaftar', 'RecruitmentController@updateRecruitmentJalurDaftar');
    $router->delete('/deleteRecruitmentJalurDaftar', 'RecruitmentController@deleteRecruitmentJalurDaftar');
    // RECRUITMENT EMAIL TEMPLATES
    $router->get('/getRecruitmentEmailTemplate', 'RecruitmentController@getRecruitmentEmailTemplate');
    $router->get('/getRecruitmentEmailTemplates', 'RecruitmentController@getRecruitmentEmailTemplates');
    $router->get('/getRecruitmentEmailTemplatesList', 'RecruitmentController@getRecruitmentEmailTemplatesList');
    $router->post('/addRecruitmentEmailTemplate', 'RecruitmentController@addRecruitmentEmailTemplate');
    $router->put('/updateRecruitmentEmailTemplate', 'RecruitmentController@updateRecruitmentEmailTemplate');
    $router->delete('/deleteRecruitmentEmailTemplate', 'RecruitmentController@deleteRecruitmentEmailTemplate');
    $router->post('/sendRecruitmentEmail', 'RecruitmentController@sendRecruitmentEmail');


    // Employee Module -- -- -- -- --
    // Employee
    $router->get('/getEmployee', 'EmployeeController@getEmployee');
    $router->get('/getEmployees', 'EmployeeController@getEmployees');
    $router->post('/addEmployee', 'EmployeeController@addEmployee');
    $router->post('/addEmployeeFromUser', 'EmployeeController@addEmployeeFromUser');
    $router->post('/updateEmployee', 'EmployeeController@updateEmployee');
    $router->delete('/deleteEmployee', 'EmployeeController@deleteEmployee');
    $router->get('/getEmployeePlacementsCount', 'EmployeeController@getEmployeePlacementsCount');
    $router->get('/getEmployeeRolesCount', 'EmployeeController@getEmployeeRolesCount');
    $router->get('/getEmployeeStatusesCount', 'EmployeeController@getEmployeeStatusesCount');
    $router->get('/getEmployeesDraft', 'EmployeeController@getEmployeesDraft');


    // Employee Contract
    $router->get('/getEmployeeContract', 'EmployeeController@getEmployeeContract');
    $router->get('/getEmployeeContracts', 'EmployeeController@getEmployeeContracts');
    $router->post('/addEmployeeContract', 'EmployeeController@addEmployeeContract');
    $router->post('/updateEmployeeContract', 'EmployeeController@updateEmployeeContract');
    $router->delete('/deleteEmployeeContract', 'EmployeeController@deleteEmployeeContract');

    // Employee Inventory
    $router->get('/getEmployeeInventory', 'EmployeeController@getEmployeeInventory');
    $router->get('/getEmployeeInventories', 'EmployeeController@getEmployeeInventories');
    $router->post('/addEmployeeInventory', 'EmployeeController@addEmployeeInventory');
    $router->post('/updateEmployeeInventory', 'EmployeeController@updateEmployeeInventory');
    $router->delete('/deleteEmployeeInventory', 'EmployeeController@deleteEmployeeInventory');

    // Employee Device
    $router->get('/getEmployeeDevice', 'EmployeeController@getEmployeeDevice');
    $router->get('/getEmployeeDevices', 'EmployeeController@getEmployeeDevices');
    $router->post('/addEmployeeDevice', 'EmployeeController@addEmployeeDevice');
    $router->put('/updateEmployeeDevice', 'EmployeeController@updateEmployeeDevice');
    $router->delete('/deleteEmployeeDevice', 'EmployeeController@deleteEmployeeDevice');

    // Employee Salary Column
    $router->get('/getEmployeeSalaryColumn', 'EmployeeController@getEmployeeSalaryColumn');
    $router->get('/getEmployeeSalaryColumns', 'EmployeeController@getEmployeeSalaryColumns');
    $router->post('/addEmployeeSalaryColumn', 'EmployeeController@addEmployeeSalaryColumn');
    $router->put('/updateEmployeeSalaryColumn', 'EmployeeController@updateEmployeeSalaryColumn');
    $router->delete('/deleteEmployeeSalaryColumn', 'EmployeeController@deleteEmployeeSalaryColumn');

    // Employee Payslip
    $router->get('/getEmployeePayslip', 'EmployeeController@getEmployeePayslip');
    $router->get('/getEmployeePayslips', 'EmployeeController@getEmployeePayslips');
    $router->get('/getEmployeesPayslip', 'EmployeeController@getEmployeesPayslip');
    $router->post('/addEmployeePayslip', 'EmployeeController@addEmployeePayslip');
    $router->put('/updateEmployeePayslip', 'EmployeeController@updateEmployeePayslip');
    $router->delete('/deleteEmployeePayslip', 'EmployeeController@deleteEmployeePayslip');
    $router->get('/getEmployeePayslipStatusCount', 'EmployeeController@getEmployeePayslipStatusCount');
    $router->get('/getEmployeePayslipsEmpty', 'EmployeeController@getEmployeePayslipsEmpty');
    $router->post('/downloadEmployeePayslip', 'EmployeeController@downloadEmployeePayslip');
    $router->get('/postedEmployeeLastPayslips', 'EmployeeController@postedEmployeeLastPayslips');
    $router->get('/raiseLastPeriodPayslip', 'EmployeeController@raiseLastPeriodPayslip');


    // Project & Task Management - Project
    $router->get('/getProject', 'ProjectTaskController@getProject');
    $router->get('/getProjects', 'ProjectTaskController@getProjects');
    $router->get('/getClientProjects', 'ProjectTaskController@getClientProjects');
    $router->get('/getProjectsList', 'ProjectTaskController@getProjectsList');
    $router->post('/addProject', 'ProjectTaskController@addProject');
    $router->put('/updateProject', 'ProjectTaskController@updateProject');
    $router->delete('/deleteProject', 'ProjectTaskController@deleteProject');
    $router->put('updateProject_status', 'ProjectTaskController@updateProject_status');
    $router->get('/getProjectsCount', 'ProjectTaskController@getProjectsCount');
    $router->get('/getClientProjectsCount', 'ProjectTaskController@getClientProjectsCount');
    $router->get('/getProjectsDeadline', 'ProjectTaskController@getProjectsDeadline');
    $router->get('/getClientProjectsDeadline', 'ProjectTaskController@getClientProjectsDeadline');
    $router->get('/exportProjects', 'ProjectTaskController@exportProjects');
    // Project & Task Management - Task
    $router->get('/getProjectTask', 'ProjectTaskController@getProjectTask');
    $router->get('/getProjectTasks', 'ProjectTaskController@getProjectTasks');
    $router->post('/addProjectTask', 'ProjectTaskController@addProjectTask');
    $router->put('/updateProjectTask', 'ProjectTaskController@updateProjectTask');
    $router->delete('/deleteProjectTask', 'ProjectTaskController@deleteProjectTask');
    $router->put('/updateProjectTask_status', 'ProjectTaskController@updateProjectTask_status');
    $router->get('/getProjectTasksCount', 'ProjectTaskController@getProjectTasksCount');
    $router->get('/getProjectTasksCountClient', 'ProjectTaskController@getProjectTasksCountClient');
    $router->get('/getProjectTasksDeadline', 'ProjectTaskController@getProjectTasksDeadline');
    $router->get('/getProjectTasksAdmin', 'ProjectTaskController@getProjectTasksAdmin');
    $router->get('/getProjectTaskStaffCount', 'ProjectTaskController@getProjectTaskStaffCount');
    // Project & Task Management - Project Status
    $router->get('/getProjectStatus', 'ProjectTaskController@getProjectStatus');
    $router->get('/getProjectStatuses', 'ProjectTaskController@getProjectStatuses');
    $router->post('/addProjectStatus', 'ProjectTaskController@addProjectStatus');
    $router->put('/updateProjectStatus', 'ProjectTaskController@updateProjectStatus');
    $router->delete('/deleteProjectStatus', 'ProjectTaskController@deleteProjectStatus');
    // Project & Task Management - Project Log
    $router->get('/getProjectLogs', 'ActivityLogController@getProjectLogs');
    $router->post('/addProjectLogNotes', 'ProjectTaskController@addProjectLogNotes');
    $router->get('/getProjectLogNotes', 'ActivityLogController@getProjectLogNotes');
    $router->delete('/deleteProjectLogNotes', 'ProjectTaskController@deleteProjectLogNotes');
    // Project & Task Management -
    $router->get('/getProjectCategoryList', 'ProjectTaskController@getProjectCategoryList');
    $router->get('/getProjectCategoryListClient', 'ProjectTaskController@getProjectCategoryListClient');
    $router->put('/updateProjectCategoryListClient', 'ProjectTaskController@updateProjectCategoryListClient');

    $router->get('/getBankLists', "BankListController@getBankLists");

    //Contract Routes
    $router->get('/getContracts', 'ContractController@getContracts');
    $router->get('/getContract', 'ContractController@getContract');
    $router->post('/addContract', 'ContractController@addContract');
    $router->post('/updateContract', 'ContractController@updateContract');
    $router->delete('/deleteContract', 'ContractController@deleteContract');
    $router->get('/getContractActiveCount', 'ContractController@getContractActiveCount');
    // Contract Management - Contract Log
    $router->get('/getContractLogs', 'ActivityLogController@getContractLogs');
    $router->post('/addContractLogNotes', 'ContractController@addContractLogNotes');
    $router->get('/getContractLogNotes', 'ActivityLogController@getContractLogNotes');
    $router->delete('/deleteContractLogNotes', 'ContractController@deleteContractLogNotes');
    // Contract Management - Template
    $router->get('/getContractTemplate', 'ContractController@getContractTemplate');
    $router->put('/updateContractTemplate', 'ContractController@updateContractTemplate');
    // Contract Management - Invoice
    $router->get('/getContractInvoices', 'ContractController@getContractInvoices');
    $router->get('/getContractInvoice', 'ContractController@getContractInvoice');
    $router->post('/addContractInvoice', 'ContractController@addContractInvoice');
    $router->put('/updateContractInvoice', 'ContractController@updateContractInvoice');
    $router->delete('/deleteContractInvoice', 'ContractController@deleteContractInvoice');

    // Contract Histories Routes
    $router->get('/getContractHistories', 'ContractController@getContractHistories');
    $router->get('/getContractHistory', 'ContractController@getContractHistory');
    $router->post('/addContractHistory', 'ContractController@addContractHistory');
    $router->post('/updateContractHistory', 'ContractController@updateContractHistory');
    $router->delete('/deleteContractHistory', 'ContractController@deleteContractHistory');
    // Contract Histories Routes - Log
    $router->get('/getContractHistoryLogs', 'ActivityLogController@getContractHistoryLogs');
});

// TALENT POOL SHARE PUBLIC
$router->group([],function ($router) {
    $router->get('/getTalentPoolSharePublics', 'TalentPoolController@getTalentPoolSharePublics');
    $router->get('/getTalentPoolSharePublic', 'TalentPoolController@getTalentPoolSharePublic');
    $router->post('/markTalentPoolSharePublic', 'TalentPoolController@markTalentPoolSharePublic');
    $router->post('/eliminateTalentPoolSharePublic', 'TalentPoolController@eliminateTalentPoolSharePublic');
    $router->post('/cancelTalentPoolSharePublic', 'TalentPoolController@cancelTalentPoolSharePublic');
});

// //Incident Routes
// $router->get('/getIncidents', 'IncidentController@getIncidents');
// $router->post('/addIncident', 'IncidentController@addIncident');
// $router->post('/updateIncident', 'IncidentController@updateIncident');
// $router->delete('/deleteIncident', 'IncidentController@deleteIncident');

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



//Depreciation Routes
$router->get('/getDepreciations', 'DepreciationController@getDepreciations');
$router->post('/addDepreciation', 'DepreciationController@addDepreciation');
$router->put('/updateDepreciation', 'DepreciationController@updateDepreciation');
$router->delete('/deleteDepreciation', 'DepreciationController@deleteDepreciation');


//Career V2 Routes
$router->group(['prefix' => 'v2'], function () use ($router) {

    $router->post('/addCareerApply', 'CareerV2Controller@addCareerApply');
    $router->get('/getPostedCareer', 'CareerV2Controller@getPostedCareer');
    $router->get('/getPostedCareers', 'CareerV2Controller@getPostedCareers');
    $router->get('/getCareerApplyStatuses', 'CareerV2Controller@getCareerApplyStatuses');
    $router->get('/getCareerExperiences', 'CareerV2Controller@getCareerExperiences');
    $router->get('/getCareerRoleTypes', 'CareerV2Controller@getCareerRoleTypes');
});

$router->post('/recaptcha', 'CareerV2Controller@recaptcha');

// $router->get('/', function () use ($router){ phpinfo(); });




// //Terms of Payment
// $router->post('/addDefaultPayments', 'ServiceController@addDefaultPayments');
