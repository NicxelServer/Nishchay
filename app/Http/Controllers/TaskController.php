<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\OfficialDetail;
use App\Models\TaskDetail;
use App\Models\Role;
use App\Helpers\EncryptionDecryptionHelper;
use App\Helpers\AuditLogHelper;
use App\Models\AuditLogDetail;
use App\Models\Module;
use App\Models\TaskActionDetail;
use App\Models\RoleModule;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;


class TaskController extends Controller
{
    //
     //show tasks
     public function Tasks()
     {
         $userdetails = session('user');
         $user_id = $userdetails->tbl_user_id;

         $moduleData = session('moduleData');
            $myTasksExist = false;
            $showTasksExist = false;
            $createNewTask = false;
            $deleteTask = false;
            $actionOnTask = false;
            $reassignTask = false;
            $showTasksButton = false;
            $myTasksButton = false;

            foreach ($moduleData as $data) {
                if ($data['module']->module_name === 'My Tasks') {
                    $myTasksExist = true;
                }
                if ($data['module']->module_name === 'Show Tasks') {
                    $showTasksExist = true;
                }

                if($data['module']->module_name === 'Create New Task'){
                    $createNewTask = true;
                }

                if($data['module']->module_name === 'Delete Task'){
                    $deleteTask = true;
                }

                if($data['module']->module_name === 'Reassign Task'){
                    $reassignTask = true;
                }

                if($data['module']->module_name === 'Action On Task'){
                    $actionOnTask = true;
                }
                // If both modules are found, exit the loop early
                if ($myTasksExist && $showTasksExist && $createNewTask && $deleteTask && $actionOnTask && $reassignTask) {
                    break;
                }
            }
        
            //Leader functionality
            //if both modules are assigned we will check if showTasks in session is true
            //if it is not true we will display My Tasks->in my Tasks we have to display show tasks button
            //to redirect to show tasks
            //else we will display show Tasks->in show Tasks we have to display my tasks button
            //to redirect to my tasks
            if($myTasksExist && $showTasksExist) {

            
                $redirectToShowTasks = session('showTasks');

                if($redirectToShowTasks){
                    $myTasksExist = false;
                    $showTasksExist = true;
                    $showTasksButton = false;
                    $myTasksButton = true;
                }
                else if($redirectToShowTasks == false){
                    $showTasksExist = false;
                    $myTasksExist = true;
                    $myTasksButton = false;
                    $createNewTask = false;
                    $showTasksButton = true;
                }
            }   
                
            if ($myTasksExist) {
                $tasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','Pending')->where('flag', 'show')->get();
                $pendingtaskCount = $tasks->count();
                $ctasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','Completed')->where('flag', 'show')->get();
                $completedtaskCount = $ctasks->count();
                $iptasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','In Progress')->where('flag', 'show')->get();
                $inprogresstaskCount = $iptasks->count();
                foreach ($tasks as $task) 
                {
                    // Encode the task ID using the helper function
                    $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

                    // Query the User model to get the name of the user who assigned the task
                    $assignedUser = User::find($task->add_by);
                    if ($assignedUser) {
                        $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                    }
                }
                    
                    $columnName = "Task Assigned By";
                return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'showTasksButton'=>$showTasksButton]);
                        
            } elseif ($showTasksExist) {
                 $tasks = TaskDetail::where('add_by',$user_id)->where('task_status','Pending')->where('flag', 'show')->where(function ($query) {
                    $query->where('transferred_status', '!=', 'Pending')
                          ->orWhereNull('transferred_status');
                })->get();
                 $pendingtaskCount = $tasks->count();
                 $ctasks = TaskDetail::where('add_by', $user_id)->where('task_status','Completed')->where('flag', 'show')->get();
                $completedtaskCount = $ctasks->count();
                $iptasks = TaskDetail::where('add_by', $user_id)->where('task_status','In Progress')->where('flag', 'show')->where(function ($query) {
                    $query->where('transferred_status', '!=', 'Pending')
                          ->orWhereNull('transferred_status');
                })->get();
                $inprogresstaskCount = $iptasks->count();
                $rtasks = TaskDetail::where('transferred_status','Pending')->where('flag','show')->where('add_by',$user_id)->get();
                $reassigntaskCount = $rtasks->count();

                foreach($tasks as $task)
                    {
                        // Encode the task ID using the helper function
                        $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

                        $assignedUser = User::find($task->selected_user_id);
                        if ($assignedUser) {
                            $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                        }
                    }
                    
                    $columnName = "Task Assigned To";
                    $role = "Manager";
                return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'role'=>$role,'createNewTask'=>$createNewTask,'deleteTask'=>$deleteTask,'reassignTask'=>$reassignTask,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'reassigntaskCount'=>$reassigntaskCount,'myTasksButton'=>$myTasksButton]);
             
            } else {
                // Neither module exists
                 // Do something else...
                 return redirect('/dashboard');
            }
     
     }

    public function viewTask($enc_task_id)
    {
        $moduleData = session('moduleData');
        $reassignTask = false;
        $deleteTask = false;
        $actionOnTask = false;
        $createNewTask = false;

        $showTasksExist = false;
        $myTasksExist = false;
        $changeStatus = true;

        $submitButton = true;
        $completedDate = false;

        foreach ($moduleData as $data) {
            if ($data['module']->module_name === 'Reassign Task') {
                $reassignTask = true;
            }
            if ($data['module']->module_name === 'Delete Task') {
                $deleteTask = true;
            }

            if ($data['module']->module_name === 'Show Tasks') {
                $showTasksExist = true;
            }

            if ($data['module']->module_name === 'My Tasks') {
                $myTasksExist = true;
            }

            if($data['module']->module_name === 'Action on Tasks'){
                $actionOnTask = true;
            }

            if($data['module']->module_name === 'Create New Task'){
                $createNewTask = true;
            }

            if ($reassignTask && $deleteTask && $actionOnTask && $createNewTask && $showTasksExist && $myTasksExist) {
                break;
            }

        }    

        if($createNewTask){
            $changeStatus = false;
            $submitButton = false;
        }
        

        
        if($myTasksExist && $showTasksExist)
        {
            $redirectToShowTasks = session('showTasks');


            if($redirectToShowTasks){
                $actionOnTask = false;
                $deleteTask = true;
                $submitButton = false;
                
            }
            else if($redirectToShowTasks == false){
                $actionOnTask = true;
                $deleteTask = false;
                $changeStatus = true;
                $submitButton = true;
                //$createNewTask = true;
            }
        }

        

        $dec_task_id = EncryptionDecryptionHelper::encdecId($enc_task_id,'decrypt');
        
        
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();
        if($task->task_status == 'Completed'){
            $completedDate = true;
        }

        if($task->task_status == 'Completed'){
            $completedDate = true;
        }

        $actionsOnTask = TaskActionDetail::where('tbl_task_detail_id',$dec_task_id)->get();
        if($deleteTask){
        if($actionsOnTask->isEmpty()){
            $deleteTask = true;
        }
        else{
            $firstAction = $actionsOnTask->first();
            if($firstAction->action_name == "reassign Task"){
                if ($actionsOnTask->count() > 1 && strpos($actionsOnTask[1]->action_name, "Task Assigned to") === 0){
                    $deleteTask == true;
                }
                
            }
            else{
                $deleteTask = false;
            }
            
        }
        }
        
         //check if transferred status is pending if it is pending then only set reassign task to true
        //as we want to provide this functionality only for manager so check if create task role module exists
        $transferredStatus = $task->transferred_status; 
        
        if($createNewTask){
                $reassignTask = false;
            }

            else{
                $reassignTask = true;
            }
        
        

        $assignedUser = User::find($task->selected_user_id);
        if ($assignedUser) {
            $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
        }
        
         $action_details = TaskActionDetail::where('tbl_task_detail_id',$dec_task_id)->get();
         foreach($action_details as $action_detail){
            $assignedUser = User::find($action_detail->action_by);
            if($assignedUser){
                $action_detail->user_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
            }
         
        }

        
        return view('frontend_tasks.view_task_page',['task'=>$task,'enc_task_id'=>$enc_task_id,'action_details'=>$action_details,'reassignTask'=>$reassignTask,'deleteTask'=>$deleteTask,'actionOnTask'=>$actionOnTask,'createNewTask'=>$createNewTask,'changeStatus'=>$changeStatus,'submitButton'=>$submitButton,'completedDate'=>$completedDate]);

    }

    public function updateMyTaskStatus(Request $request)
    {
        
        $dec_task_id = EncryptionDecryptionHelper::encdecId($request->input('enc_task_id'),'decrypt');
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();
        
        // If the 'status' field received in the request is 'Completed', update the 'task_completion_date' field with the current date
        $task->task_status = $request->status;


        if($request->status == 'Completed'){
            $task->task_completion_date = Date::now()->toDateString();
        }
        else{
            $task->task_completion_date = null;
        }
        
        $task->update_date = Date::now()->toDateString();
        $task->update_time = Date::now()->toTimeString();
        $task->save();

        //store details into tbl_task_action_detials
        $userdetails = session('user');
         $user_id = $userdetails->tbl_user_id;

        $action_details = new TaskActionDetail;
        $action_details->tbl_task_detail_id = $dec_task_id;
        $action_details->action_name = $request->action ?: 'task not approved';;
        $action_details->action_by = $user_id;
        $action_details->action_date = Date::now()->toDateString();
        $action_details->action_time = Date::now()->toTimeString();
        $action_details->save();
        $task->save();

        //auditlog entry
      
        AuditLogHelper::logDetails('update task status', $userdetails->tbl_user_id);

        return redirect('/Tasks');
    }
     
    public function transferMyTaskForm($enc_task_id)
    {
        $dec_task_id = EncryptionDecryptionHelper::encdecId($enc_task_id,'decrypt');
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();

        return view('frontend_tasks.reassign_tasks',['task'=>$task,'enc_task_id'=>$enc_task_id]);
    }
     
    public function transferMyTask(Request $request)
    {
        
        $dec_task_id = EncryptionDecryptionHelper::encdecId($request->input('enc_task_id'),'decrypt');
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();

        $task->remark = $request->remark;
        $task->transferred_status = 'Pending';
        
        $task->save();

         //store details into tbl_task_action_detials
         $userdetails = session('user');
         $user_id = $userdetails->tbl_user_id;

        $action_details = new TaskActionDetail;
        $action_details->tbl_task_detail_id = $dec_task_id;
        $action_details->action_name = $request->action ?: 'reassign Task';;
        $action_details->action_by = $user_id;
        $action_details->action_date = Date::now()->toDateString();
        $action_details->action_time = Date::now()->toTimeString();
        $action_details->save();
        


    
        AuditLogHelper::logDetails('reassign task', $userdetails->tbl_user_id);

        return redirect('/Tasks');
    }
 
    

    public function showInProgressTasks()
    {
        
         $userdetails = session('user');
         $user_id = $userdetails->tbl_user_id;

         $moduleData = session('moduleData');
            $myTasksExist = false;
            $showTasksExist = false;
            $createNewTask = false;
            $reassignTask = false;
            $showTasksButton = false;
            $myTasksButton = false;

            foreach ($moduleData as $data) {
                if ($data['module']->module_name === 'My Tasks') {
                    $myTasksExist = true;
                }
                if ($data['module']->module_name === 'Show Tasks') {
                    $showTasksExist = true;
                }
                if ($data['module']->module_name === 'Reassign Task') {
                    $reassignTask = true;
                }

            

                if($data['module']->module_name === 'Create New Task'){
                    $createNewTask = true;
                }
                // If both modules are found, exit the loop early
                if ($myTasksExist && $showTasksExist && $createNewTask && $reassignTask) {
                    break;
                }
            }

            //Leader functionality
            //if both modules are assigned we will check if showTasks in session is true
            //if it is not true we will display My Tasks->in my Tasks we have to display show tasks button
            //to redirect to show tasks
            //else we will display show Tasks->in show Tasks we have to display my tasks button
            //to redirect to my tasks
            if($myTasksExist && $showTasksExist) {

            
                $redirectToShowTasks = session('showTasks');

                if($redirectToShowTasks){
                    $myTasksExist = false;
                    $showTasksExist = true;
                    $showTasksButton = false;
                    $myTasksButton = true;
                }
                else if($redirectToShowTasks == false){
                    $showTasksExist = false;
                    $myTasksExist = true;
                    $myTasksButton = false;
                    $createNewTask = false;
                    $showTasksButton = true;
                }
            }  
            
            if ($myTasksExist) {
                //$tasks = TaskDetail::where('selected_user_id', $user_id)->where('flag', 'show')->where('task_status','In Progress')->where('transferred_status', '!=', 'Pending')->get();
               // $tasks = TaskDetail::where('selected_user_id', $user_id)
                                    // ->where('flag', 'show')
                                    // ->where('task_status', 'In Progress')
                                    // ->where(function ($query) {
                                    //     $query->where('transferred_status', '!=', 'Pending')
                                    //         ->orWhereNull('transferred_status');
                                    // })
                                    // ->get();

            $tasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','In Progress')->where('flag', 'show')->get();                    

                $inprogresstaskCount = $tasks->count();
                $ctasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','Completed')->where('flag', 'show')->get();
                $completedtaskCount = $ctasks->count();
                $iptasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','Pending')->where('flag', 'show')->get();
                $pendingtaskCount = $iptasks->count();
                
                
                foreach ($tasks as $task) 
                {
                    // Encode the task ID using the helper function
                    $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

                    // Query the User model to get the name of the user who assigned the task
                    $assignedUser = User::find($task->add_by);
                    if ($assignedUser) {
                        $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                    }
                }
                    $columnName = "Task Assigned By";
                    $title = "In Progress Tasks";
                return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'title'=>$title,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'showTasksButton'=>$showTasksButton]);
                        
            } elseif ($showTasksExist) {
                 $tasks = TaskDetail::where('add_by',$user_id)->where('task_status','In Progress')->where('flag', 'show')->where(function ($query) {
                    $query->where('transferred_status', '!=', 'Pending')
                          ->orWhereNull('transferred_status');
                })->get();
                 
                $inprogresstaskCount = $tasks->count();
                $ctasks = TaskDetail::where('add_by', $user_id)->where('task_status','Completed')->where('flag', 'show')->get();
                $completedtaskCount = $ctasks->count();
                $iptasks = TaskDetail::where('add_by', $user_id)->where('task_status','Pending')->where('flag', 'show')->where(function ($query) {
                    $query->where('transferred_status', '!=', 'Pending')
                          ->orWhereNull('transferred_status');
                })->get();
                $pendingtaskCount = $iptasks->count();
                $rtasks = TaskDetail::where('transferred_status','Pending')->where('flag','show')->where('add_by',$user_id)->get();
                $reassigntaskCount = $rtasks->count();

                 
                foreach($tasks as $task)
                    {
                        // Encode the task ID using the helper function
                        $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

                        $assignedUser = User::find($task->selected_user_id);
                        if ($assignedUser) {
                            $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                        }
                    }
                    $columnName = "Task Assigned To";
                    $title = "In Progress Tasks";
                    $role = "Manager";
                return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'title'=>$title,'role'=>$role,'createNewTask'=>$createNewTask,'reassignTask'=>$reassignTask,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'reassigntaskCount'=>$reassigntaskCount,'myTasksButton'=>$myTasksButton]);
             
            } else {
                // Neither module exists
                 // Do something else...
                 return redirect('/dashboard');
            }
 
    }


    public function completedTasks()
    {
        $userdetails = session('user');
        $user_id = $userdetails->tbl_user_id;

        $moduleData = session('moduleData');
           $myTasksExist = false;
           $showTasksExist = false;
           $createNewTask = false;
           $reassignTask = false;
           $showTasksButton = false;
           $myTasksButton = false;

           foreach ($moduleData as $data) {
               if ($data['module']->module_name === 'My Tasks') {
                   $myTasksExist = true;
               }
               if ($data['module']->module_name === 'Show Tasks') {
                   $showTasksExist = true;
               }

               if($data['module']->module_name === 'Create New Task'){
                $createNewTask = true;
                }
                if($data['module']->module_name === 'Reassign Task'){
                    $reassignTask = true;
                    }
                // If both modules are found, exit the loop early
                if ($myTasksExist && $showTasksExist && $createNewTask && $reassignTask) {
                    break;
                }
           }

           //Leader functionality
            //if both modules are assigned we will check if showTasks in session is true
            //if it is not true we will display My Tasks->in my Tasks we have to display show tasks button
            //to redirect to show tasks
            //else we will display show Tasks->in show Tasks we have to display my tasks button
            //to redirect to my tasks
            if($myTasksExist && $showTasksExist) {

            
                $redirectToShowTasks = session('showTasks');

                if($redirectToShowTasks){
                    $myTasksExist = false;
                    $showTasksExist = true;
                    $showTasksButton = false;
                    $myTasksButton = true;
                }
                else if($redirectToShowTasks == false){
                    $showTasksExist = false;
                    $myTasksExist = true;
                    $myTasksButton = false;
                    $createNewTask = false;
                    $showTasksButton = true;
                }
            } 
           
           if ($myTasksExist) {
               $tasks = TaskDetail::where('selected_user_id', $user_id)->where('flag', 'show')->where('task_status','Completed')->get();
                $completedtaskCount = $tasks->count();
                $ptasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','Pending')->where('flag', 'show')->get();
                $pendingtaskCount = $ptasks->count();
                $iptasks = TaskDetail::where('selected_user_id', $user_id)->where('task_status','In Progress')->where('flag', 'show')->get();
                $inprogresstaskCount = $iptasks->count();
                 
               foreach ($tasks as $task) 
               {
                   // Encode the task ID using the helper function
                   $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

                   // Query the User model to get the name of the user who assigned the task
                   $assignedUser = User::find($task->add_by);
                   if ($assignedUser) {
                       $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                   }
               }
                   $columnName = "Task Assigned By";
                   $title = "Completed Tasks";
               return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'title'=>$title,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'showTasksButton'=>$showTasksButton]);
                       
           } elseif ($showTasksExist) {
                $tasks = TaskDetail::where('add_by',$user_id)->where('task_status','Completed')->where('flag', 'show')->get();
                $completedtaskCount = $tasks->count();
                $ptasks = TaskDetail::where('add_by', $user_id)->where('task_status','Pending')->where('flag', 'show')->where(function ($query) {
                    $query->where('transferred_status', '!=', 'Pending')
                          ->orWhereNull('transferred_status');
                })->get();
                $pendingtaskCount = $ptasks->count();
                $iptasks = TaskDetail::where('add_by', $user_id)->where('task_status','In Progress')->where('flag', 'show')->where(function ($query) {
                    $query->where('transferred_status', '!=', 'Pending')
                          ->orWhereNull('transferred_status');
                })->get();
                $inprogresstaskCount = $iptasks->count();
                $rtasks = TaskDetail::where('transferred_status','Pending')->where('flag','show')->where('add_by',$user_id)->get();
                $reassigntaskCount = $rtasks->count();

               foreach($tasks as $task)
                   {
                       // Encode the task ID using the helper function
                       $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

                       $assignedUser = User::find($task->selected_user_id);
                        if ($assignedUser) {
                            $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                        }
                   }
                   $columnName = "Task Assigned To";
                   $title = "Completed Tasks";
                   $role = "Manager";
               return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'title'=>$title,'role'=>$role,'createNewTask'=>$createNewTask,'reassignTask'=>$reassignTask,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'reassigntaskCount'=>$reassigntaskCount,'myTasksButton'=>$myTasksButton]);
            
           } else {
               // Neither module exists
                // Do something else...
                return redirect('/dashboard');
           }

    }






    //mng show task
    

    //view particular task
    // public function viewTask($enc_task_id)
    // {
    //     $dec_task_id = EncryptionDecryptionHelper::encdecId($enc_task_id,'decrypt');
    //     $task = TaskDetail::where('tbl_task_details_id',$dec_task_id)->first();
    //     dd($task);
    //     return view('frontend_tasks.viewTask',['task'=>$task,'enc_task_id'=>$enc_task_id]);
    // }


    //delete a particular task
    public function deleteTask($enc_task_id)
    {   
        $user_details = session('user');
        $dec_task_id = EncryptionDecryptionHelper::encdecId($enc_task_id,'decrypt');
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();

        if($task->task_status == 'Completed' || $task->task_status == 'In Progress'){
            return redirect()->back()->withError('task cannot be deleted');
        }
        else{
            $task->flag = 'deleted';
            $task->update_by = $user_details->tbl_user_id;
            $task->update_date = Date::now()->toDateString();
            $task->update_time = Date::now()->toTimeString();
            
            $task->save();
        }


        $user_details = session('user');
        AuditLogHelper::logDetails('delete task', $user_details->tbl_user_id);

        return redirect('/Tasks');

    }
    //show create task form
    public function createTask()
    {
        
        $user_details = session('user');
        $mng_id = $user_details->tbl_user_id;

      
        $emps = OfficialDetail::where('reporting_manager_id',$mng_id)->get();
        

        $empsWithModulesAndNames = [];

        foreach ($emps as $emp) {
            // Retrieve the user associated with the employee
            $user = User::find($emp->tbl_user_id);
        
            if ($user) {
                $enc_user_id = EncryptionDecryptionHelper::encdecId($user->tbl_user_id, 'encrypt');
                // Retrieve the role ID directly from the user object
                $roleId = $user->tbl_role_id;

                //get the modules id from db
                $moduleId = Module::where('module_name','My Tasks')->first();
                
        
                // Check if  My Tasks module Id exists for the role
                $roleModules = RoleModule::where('tbl_role_id', $roleId)->where('tbl_module_id', $moduleId->tbl_module_id)->exists();

                 
                    //->whereIn('tbl_module_id', [20, 25]);
                    // ->pluck('tbl_module_id')
                    // ->toArray();
        
                // If either module ID exists for the role, include the employee and user's name in the result
                if (!empty($roleModules)) {
                    $empsWithModulesAndNames[] = [
                        'employee' => $emp,
                        'user_name' => $user->first_name . ' ' . $user->last_name,
                        'enc_user_id' => $enc_user_id,
                    ];
                }
            }
        }

       // dd($empsWithModulesAndNames);
        $user_details = session('user');
        AuditLogHelper::logDetails('create new task', $user_details->tbl_user_id);

        return view('frontend_tasks.create_task',['empsWithModulesAndNames'=>$empsWithModulesAndNames]);

    }

    //assign task to the emp
     public function assignTask(Request $request)
     {
        $user_details = session('user');
        $mng_id = $user_details->tbl_user_id;

        $dec_selected_user_id = EncryptionDecryptionHelper::encdecId($request->assigned_to,'decrypt');


        $task = new TaskDetail;
        $task->task_description = $request->task_description;
        $task->selected_user_id = $dec_selected_user_id;
        $task->task_delivery_date = $request->expected_delivery_date;
        $task->add_by = $mng_id;
        $task->task_status ="Pending";
        $task->add_date = Date::now()->toDateString();
        $task->add_time = Date::now()->toTimeString();
        $task->flag = 'show';
        
        $task->save();

        // $action_details = new TaskActionDetail;
        // $action_details->tbl_task_detail_id = $dec_task_id;
        // $action_details->action_name = $request->action ?: 'reassign Task';;
        // $action_details->action_by = $user_id;
        // $action_details->action_date = Date::now()->toDateString();
        // $action_details->action_time = Date::now()->toTimeString();
        // $action_details->save();

        AuditLogHelper::logDetails('assign task', $user_details->tbl_user_id);
        
        return redirect('/Tasks');
     }

     //completed task
    public function completedTask()
    {
        $tasks = TaskDetail::where('task_status','Completed')->get();
        foreach($tasks as $task)
        {
            // Encode the task ID using the helper function
            $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_details_id, 'encrypt');
        }
         return view('frontend_tasks.completedTask',['tasks'=>$tasks]);
    }

    //in progress task
    public function inProgressTask()
    {
        $tasks = TaskDetail::where('task_status','In Progress')->get();
        foreach($tasks as $task)
        {
            // Encode the task ID using the helper function
            $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_details_id, 'encrypt');
        }
         return view('frontend_tasks.inProgressTask',['tasks'=>$tasks]);
    }


    public function showReassignedTasks()
    {
        //if My Tasks And Show Tasks modules exist we want to display my tasks button
        $moduleData = session('moduleData');
        $myTasksExist = false;
        $showTasksExist = false;
        $myTasksButton = false;

        foreach ($moduleData as $data) {
            if ($data['module']->module_name === 'My Tasks') {
                $myTasksExist = true;
            }
            if ($data['module']->module_name === 'Show Tasks') {
                $showTasksExist = true;
            }
        }
        
        if($myTasksExist && $showTasksExist){
            $myTasksButton = true;
        }
        

        $user_details = session('user');
        $mng_id = $user_details->tbl_user_id;

        $ctasks = TaskDetail::where('add_by',$mng_id)->where('task_status','Completed')->where('flag', 'show')->get();
        $completedtaskCount = $ctasks->count();

        $ptasks = TaskDetail::where('add_by', $mng_id)->where('task_status','Pending')->where('flag', 'show')->where(function ($query) {
            $query->where('transferred_status', '!=', 'Pending')
                  ->orWhereNull('transferred_status');
        })->get();
        $pendingtaskCount = $ptasks->count();

        $iptasks = TaskDetail::where('add_by', $mng_id)->where('task_status','In Progress')->where('flag', 'show')->where(function ($query) {
            $query->where('transferred_status', '!=', 'Pending')
                  ->orWhereNull('transferred_status');
        })->get();
        $inprogresstaskCount = $iptasks->count();

        $tasks = TaskDetail::where('transferred_status','Pending')->where('flag','show')->where('add_by',$mng_id)->get();
        $reassigntaskCount = $tasks->count();

        foreach($tasks as $task)
        {
            // Encode the task ID using the helper function
            $task->enc_task_id = EncryptionDecryptionHelper::encdecId($task->tbl_task_detail_id, 'encrypt');

            $assignedUser = User::find($task->selected_user_id);
                   if ($assignedUser) {
                       $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
                   }
        }
        
        $role = "Manager";
        $columnName = "Task Assigned To";
        $reassign = "apply";
        $view = "reassign";
        $title ="Reassign Tasks";
        $reassignTask = true;
        return view('frontend_tasks.showTasks',['tasks'=>$tasks,'columnName'=>$columnName,'role'=>$role,'reassign'=>$reassign,'title'=>$title,'reassignTask'=>$reassignTask,'pendingtaskCount'=>$pendingtaskCount,'completedtaskCount'=>$completedtaskCount,'inprogresstaskCount'=>$inprogresstaskCount,'reassigntaskCount'=>$reassigntaskCount,'myTasksButton'=>$myTasksButton]);
    }


    public function viewReassignTask($enc_task_id)
    {
        //  $module_ids = [20, 25];
        //  $roles = RoleModule::whereIn('tbl_module_id', $module_ids)->get();

         
        //  $users = User::whereIn('tbl_role_id', $roles->pluck('tbl_role_id'))->get();
        //  dd($users);

        $user_details = session('user');
        $mng_id = $user_details->tbl_user_id;

      
        $emps = OfficialDetail::where('reporting_manager_id',$mng_id)->get();

        $empsWithModulesAndNames = [];

        foreach ($emps as $emp) {
            // Retrieve the user associated with the employee
            $user = User::find($emp->tbl_user_id);
        
            if ($user) {
                $enc_user_id = EncryptionDecryptionHelper::encdecId($user->tbl_user_id, 'encrypt');
                // Retrieve the role ID directly from the user object
                $roleId = $user->tbl_role_id;
        
                 //get the modules id from db
                 $moduleId = Module::where('module_name','My Tasks')->first();
                
        
                 // Check if  My Tasks module Id exists for the role
                 $roleModules = RoleModule::where('tbl_role_id', $roleId)->where('tbl_module_id', $moduleId->tbl_module_id)->exists();
 
                // Check if either of the module IDs exists for the role
                // $roleModules = RoleModule::where('tbl_role_id', $roleId)
                //     ->whereIn('tbl_module_id', [1, 2])
                //     ->pluck('tbl_module_id')
                //     ->toArray();
        
                // If either module ID exists for the role, include the employee and user's name in the result
                if (!empty($roleModules)) {
                    $empsWithModulesAndNames[] = [
                        'employee' => $emp,
                        'user_name' => $user->first_name . ' ' . $user->last_name,
                        'enc_user_id' => $enc_user_id,
                    ];
                }
            }
        }
        
       // dd($empsWithModulesAndNames);
        // Pass $empsWithModulesAndNames to the view or perform further actions
        
        //get data of users and check the role of users
        


        

        $dec_task_id = EncryptionDecryptionHelper::encdecId($enc_task_id,'decrypt');
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();
        $assignedUser = User::find($task->selected_user_id);
        if ($assignedUser) {
            $task->assigned_name = $assignedUser->first_name . ' ' . $assignedUser->last_name;
        }

        return view('frontend_tasks.view_reassign_task',['task'=>$task,'enc_task_id'=>$enc_task_id,'empsWithModulesAndNames'=>$empsWithModulesAndNames]);

    }

    public function reassignTask(Request $request)
    {
        
        $user_details = session('user');
        $mng_id = $user_details->tbl_user_id;

        $dec_selected_user_id = EncryptionDecryptionHelper::encdecId($request->assigned_to,'decrypt');

        $dec_task_id = EncryptionDecryptionHelper::encdecId($request->input('enc_task_id'),'decrypt');
        
        $task = TaskDetail::where('tbl_task_detail_id',$dec_task_id)->first();

        $task->selected_user_id = $dec_selected_user_id;
        $task->task_delivery_date = $request->expected_delivery_date;
        $task->add_date = Date::now()->toDateString();
        $task->add_time = Date::now()->toTimeString();
        $task->transferred_status = "success";
        $task->remark=null;

        //get the user name to whom task was reassigned by using dec_selected_id
        $user = User::where('tbl_user_id',$dec_selected_user_id)->first();
        $userName = $user->first_name . " " . $user->last_name;

        $action_details = new TaskActionDetail;
        $action_details->tbl_task_detail_id = $dec_task_id;
        $action_details->action_name = "Task Reassigned to ". $userName;
        $action_details->action_by = $mng_id;
        $action_details->action_date = Date::now()->toDateString();
        $action_details->action_time = Date::now()->toTimeString();
        $action_details->save();
        

        
        $task->save();

        $user_details = session('user');
        AuditLogHelper::logDetails('reassign task', $user_details->tbl_user_id);

        return redirect('/Tasks/showreassignedtask');

    }

    public function redirectToShowTasks()
    {
        Session::put('showTasks',true);
        return redirect('/Tasks');
    }

    public function redirectToMyTasks()
    {
        Session::put('showTasks',false);
        return redirect('/Tasks');
    }

   
}