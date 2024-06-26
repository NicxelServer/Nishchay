<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\DeptController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\QueryController;
use App\Http\Controllers\TaskController;

use App\Http\Controllers\TechnologyController;

use App\Http\Controllers\DocTypeController;




 
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/',[AuthController::class,'loadLogin']);
Route::post('/login',[AuthController::class,'login']);
//Route::group(['middleware'=>'preventBackHistory'],function(){
    

Route::middleware(['validLogin','preventBackHistory'])->group(function () {

Route::post('/logout',[AuthController::class,'logout']);
Route::get('/dashboard',[AuthController::class,'dashboard']);


Route::group(['prefix' => '/admin','middleware'=>['web','isAdmin']],function(){
    Route::get('/technology',[AdminController::class,'showTechnologies']);
    Route::get('/document_type',[AdminController::class,'showDocumentType']);




    //if admin logs in show him admin dashboard
     //Route::get('/dashboard',[AdminController::class,'dashboard']);
     //if admin clicks on user in left menu
     Route::get('/users',[AdminController::class,'showUsers']);
     //admin clicks on create new user button
     Route::get('/createuser',[AdminController::class,'createUser']);
     //submitting the create new user form
     Route::post('/storeuser',[AdminController::class,'storeUser']);
     //edit user form admin/edituser/DZhu0d9k7PU=
     Route::get('/edituser/{id}',[AdminController::class,'editUserForm']);
     //upadte the user in db
     Route::post('/edituser',[AdminController::class,'editUser']);
     //delete user
     Route::get('/delete/{id}',[AdminController::class,'deleteUser']);

     //admin clicks on departments in left menu
     Route::get('/depts',[DeptController::class,'showDept']);
    //show new department form
     Route::get('/createdept',[DeptController::class,'createDept']);
     //create a new dept in db
     Route::post('/storedept',[DeptController::class,'storeDept']);
     //edit dept form
     Route::get('/editdept/{id}',[DeptController::class,'editDeptForm']);
     //edit dept and store into database
     Route::post('/editdept',[DeptController::class,'editDept']);
     //delete dept
     Route::get('/deletedept/{id}',[DeptController::class,'deleteDept']);

     //designations
     Route::get('/designations',[DesignationController::class,'showDesignation']);
     //show new department form
     Route::get('/createdesignationform',[DesignationController::class,'createDesignation']);
     //create a new dept in db
     Route::post('/storedesignation',[DesignationController::class,'storeDesignation']);
     //edit designation form
     Route::get('/editdesignation/{id}',[DesignationController::class,'editDesignationForm']);
    //edit designation and store in db
     Route::post('/editdesignation',[DesignationController::class,'editDesignation']);
     //delete designation
     Route::get('/deletedesignation/{id}',[DesignationController::class,'deleteDesignation']);

     //Role
     //show roles
     Route::get('/roles',[RoleController::class,'showRoles']);
     //display create role form
     Route::get('/createroleform',[RoleController::class,'createRoleForm']);
     //store new role into db
     Route::post('/storerole',[RoleController::class,'storeRole']);
     //edit existing role
     Route::get('/editrole/{id}',[RoleController::class,'editRoleForm']);
     //store existing edited role into db
     Route::post('/editrole',[RoleController::class,'editRole']);
     //delete role
     Route::get('/deleterole/{id}',[RoleController::class,'deleteRole']);
     //assign module form
     Route::get('/assignmodule',[AdminController::class,'showmodules']);
     Route::get('/assignmodule/{id}',[RoleController::class,'assignModuleForm']);
     //store assign module details into db
     Route::post('/assignmodule',[RoleController::class,'assignModule']);
     //delete assigned module
     Route::post('/deletemodule',[RoleController::class,'deleteModule']);

     Route::get('/auditlogdetails',[AdminController::class,'auditlogDetails']);

     

     //technologies
     Route::get('/technologies',[TechnologyController::class,'technologies']);
    //store technology
     Route::post('/storetechnology',[TechnologyController::class,'storeTechnology']);

     //EDIT TECHNOLOGY
     Route::get('/edittechnology/{id}',[TechnologyController::class,'editTechnology']);
     
     //delete technology
     Route::get('/deletetechnology/{id}',[TechnologyController::class,'deleteTechnology']);


     Route::get('/documenttypes',[DocTypeController::class,'showDocType']);
     Route::post('/storedocumenttype',[DocTypeController::class,'createDocType']);
     Route::get('/deletedocumenttype/{id}',[DocTypeController::class,'deleteDocType']);
    

 });

 //     // Route::get('/users',[SuperAdminController::class,'users'])->name('superAdminUsers');
//     // Route::get('/manage-role',[SuperAdminController::class,'manageRole'])->name('manageRole');
//     // Route::post('/update-role',[SuperAdminController::class,'updateRole'])->name('updateRole');

         //Route::get('/hr/editemp/{id}',[HrController::class,'editEmpForm']);


Route::group(['prefix' => '/Employees','middleware'=>['web','isHr']],function(){
    //mention your routes here

    Route::post('/editemp/basicinfo',[HrController::class,'basicInfo']);


    Route::post('/storeempdetails',[HrController::class,'storeDetails']);



    Route::get('/',[HrController::class,'showEmployees']);
    Route::get('/editemp/{id}',[HrController::class,'editEmpForm']);
    // Route::post('/editemp/basicinfo',[HrController::class,'basicInfo']);
    //show prev employment details form
    //Route::get('/editemp/prevempdetailsform/{id}',[HrController::class,'prevEmpDetailsForm']);
    //store prevemp details upon clicking on save
    Route::post('/editemp/storeprevempdetails',[HrController::class,'storePrevEmpDetails']);
    Route::get('/deleteprevemploymentdetails/{id}',[HrController::class,'deletePrevEmploymentDetails']);

    //on clicking on next in prev emp form display store official details form

    Route::get('/deleteprevemploymentdetails/{id}',[HrController::class,'deletePrevEmploymentDetails']);
    Route::get('/editemp/officialdetailsform/{id}',[HrController::class,'officialDetailsForm']);

    //store official details and return view statutory complaince details
    Route::post('/editemp/storeofficialdetails',[HrController::class,'storeOfficialDetailsForm']);

    //store statutory compliance details and return then return view bank details
    Route::post('/editemp/statutorydetails',[HrController::class,'statutoryDetails']);

    //store bank details and then return salary structure form
    Route::post('/editemp/bankdetails',[HrController::class,'bankDetails']);

    //store salary details and then redirect to show employees page
    Route::post('/eidtemp/saldetails',[HrController::class,'salDetails']);

    //upload documents functionality

    Route::get('/uploaddoc/{id}',[HrController::class,'uploadDocumentsForm']);

    //upload the doc inside the empcode folder
    Route::post('/uploaddoc',[HrController::class,'uploadDocuments']);

    //verify the document
    Route::get('/verifydoc/{id}',[HrController::class,'verifyDoc']);

    //delete the document
    Route::get('/deletedoc/{id}',[HrController::class,'deleteDoc']);
    
});



//Routes accessible to developer

    //Routes accessible to developer
    Route::group(['prefix' => '/Tasks','middleware'=>['web','isDev']],function(){
    
    //when dev clicks on Tasks
    Route::get('/',[TaskController::class,'Tasks']);
    //view task page
    Route::get('/viewmytask/{id}',[TaskController::class,'viewTask']);

    Route::post('/updatemytaskstatus',[TaskController::class,'updateMyTaskStatus']);

    Route::get('/transfermytask/{id}',[TaskController::class,'transferMyTaskForm']);
    //Route::get('/showreassignedtasks',[TaskController::class,'showReassignedTasks']);
    Route::post('/transfermytask',[TaskController::class,'transferMyTask']);

    Route::get('/myinprogresstasks',[TaskController::class,'showInProgressTasks']);

    Route::get('/mycompletedtasks',[TaskController::class,'completedTasks']);

    //mng
    // Route::get('/showtasks',[TaskController::class,'showTasks']);

    // Route::get('/viewtask',[TaskController::class,'viewTask']);

     Route::get('/createtask',[TaskController::class,'createTask']);

    Route::post('/assigntask',[TaskController::class,'assignTask']);

    Route::get('/deletetask/{id}',[TaskController::class,'deleteTask']);

    // Route::get('/completedtask',[TaskController::class,'completedTask']);

    // Route::get('/inprogresstask',[TaskController::class,'inProgressTask']);

     Route::get('/showreassignedtask',[TaskController::class,'showReassignedTasks']);

     Route::get('/viewreassigntask/{id}',[TaskController::class,'viewReassignTask']);

     Route::post('/reassigntask',[TaskController::class,'reassignTask']);

     Route::get('/created/showtasks',[TaskController::class,'redirectToShowTasks']);

     Route::get('/created/mytasks',[TaskController::class,'redirectToMyTasks']);


});

Route::group(['prefix' => '/Queries','middleware'=>['web','queryAuth']],function(){


    //when user clicks on query
    Route::get('/',[QueryController::class,'showPendingQueries']);

});
   

});

//});

Route::get('/fetch-all-audit-log',[AdminController::class,'fetchAllAuditLog']);

