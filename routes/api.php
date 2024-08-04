<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth', 'namespace' => 'Api'], function() {
    Route::post(PATH_API_LOGIN, 'AuthController@login');
    Route::post(PATH_API_LOGOUT, 'AuthController@logout');
    Route::post(PATH_API_RESET_PASSWORD, 'AuthController@resetPassword');
});

Route::group(['namespace' => 'Api'], function() {
    //user
    Route::post(PATH_SET_MOBILE_DEVICE_TOKEN, 'UserController@setDeviceToken');
    Route::get(PATH_GET_USER_INFO . "/{userId}", 'UserController@getUserInfo');
    Route::get(PATH_GET_ALL_USER, 'UserController@getAllUser');
    Route::get(PATH_GET_LIST_SELECT_SALE, 'UserController@getListSelectSale');
    Route::post(PATH_CREATE_USER, 'UserController@createUser');
    Route::post(PATH_UPDATE_USER, 'UserController@updateUser');
    Route::post(PATH_UPDATE_USER_AVATAR, 'UserController@updateUserAvatar');
    
    //customer
    Route::get(PATH_GET_CUSTOMER_INFO . "/{customerId}", 'CustomerController@getCustomerInfo');
    Route::get(PATH_GET_ALL_CUSTOMER, 'CustomerController@getAllCustomer');
    Route::post(PATH_CREATE_CUSTOMER, 'CustomerController@createCustomer');
    Route::put(PATH_UPDATE_CUSTOMER, 'CustomerController@updateCustomer');
    Route::delete(PATH_DELETE_CUSTOMER, 'CustomerController@deleteCustomer');
    
    //customer budget
    Route::get(PATH_GET_ALL_CUSTOMER_BUDGET, 'CustomerBudgetController@getAllCustomerBudget');
    Route::get(PATH_GET_LIST_CUSTOMER_BUDGET_ACTIVE, 'CustomerBudgetController@getListCustomerBudgetActive');
    Route::put(PATH_UPDATE_CUSTOMER_BUDGET, 'CustomerBudgetController@updateCustomerBudget');
    
    //cycle budget
    Route::get(PATH_GET_ALL_CYCLE_BUDGET, 'CycleBudgetController@getAllCycleBudget');
    Route::post(PATH_CREATE_CYCLE_BUDGET, 'CycleBudgetController@createCycleBudget');
    Route::put(PATH_UPDATE_CYCLE_BUDGET, 'CycleBudgetController@updateCycleBudget');

    //task
    Route::get(PATH_GET_LIST_USER_IN_TASK . "/{taskId}", 'TaskController@getListUserInTask');
    Route::get(PATH_GET_TASK_INFO . "/{taskId}", 'TaskController@getTaskInfo');
    Route::get(PATH_GET_ALL_TASK, 'TaskController@getAllTask');
    Route::post(PATH_CREATE_TASK, 'TaskController@createTask');
    Route::put(PATH_UPDATE_TASK, 'TaskController@updateTask');
    Route::delete(PATH_DELETE_TASK, 'TaskController@deleteTask');
    Route::post(PATH_DUPLICATE_TASK, 'TaskController@duplicateTask');

    //sub task
    Route::get(PATH_GET_LIST_SUB_TASK_BY_TASK_ID . "/{taskId}", 'SubTaskController@getListSubTaskByTaskId');
    Route::get(PATH_GET_LIST_FILE_BY_SUB_TASK_ID . "/{subTaskId}", 'SubTaskController@getListFileBySubTaskId');
    Route::get(PATH_GET_SUB_TASK_INFO . "/{subTaskId}", 'SubTaskController@getSubTaskInfo');
    Route::post(PATH_CREATE_SUB_TASK, 'SubTaskController@createSubTask');
    Route::post(PATH_CREATE_SUB_TASK_ADS, 'SubTaskController@createSubTaskAds');
    Route::post(PATH_UPDATE_SUB_TASK, 'SubTaskController@updateSubTask');
    Route::delete(PATH_DELETE_SUB_TASK, 'SubTaskController@deleteSubTask');

    //sub task comment
    Route::get(PATH_GET_SUB_TASK_COMMENT . "/{subTaskId}", 'SubTaskCommentController@getCommentBySubTaskId');
    Route::post(PATH_CREATE_SUB_TASK_COMMENT, 'SubTaskCommentController@createSubTaskComment');

    //Sub task file
    Route::delete(PATH_REMOVE_SUB_TASK_FILE, 'SubTaskFileController@removeFile');

    //notification
    Route::get(PATH_GET_NOTI_BY_USER . "/{userId}", 'NotificationController@getListNotiByUserId');
    Route::put(PATH_UPDATE_IS_SEEN, 'NotificationController@updateIsSeen');
    Route::put(PATH_UPDATE_ALL_NOTI_SEEN, 'NotificationController@updateAllNotiSeenByUserId');

    //history
    Route::get(PATH_GET_LIST_HISTORY . "/{subTaskId}", 'HistoryController@getListHistoryBySubTaskId');
});


