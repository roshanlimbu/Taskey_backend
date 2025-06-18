<?php

use App\Http\Controllers\Sadmin\commonController;
use App\Http\Controllers\Sadmin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sadmin\projectController;
use App\Http\Controllers\Sadmin\TaskController;
use App\Http\Controllers\Sadmin\profileController;
use App\Http\Controllers\Sadmin\activitiesController;
use App\Http\Controllers\Sadmin\NotificationController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\Padmin\ProjectAdminDashboardController;
use App\Http\Controllers\Sadmin\OpenAiController;
use App\Http\Controllers\Sadmin\ReportsController;



// Project routes
Route::prefix('sadmin')->middleware('auth:sanctum')->group(function () {
    Route::post('/projects', [projectController::class, 'createProject']);
    Route::get('/projects', [projectController::class, 'index']);
    Route::post('/projects/{projectId}/members', [projectController::class, 'addMembers']);
    Route::post('/projects/{projectId}/remove-members', [projectController::class, 'removeMembers']);
    Route::post('/projects/{projectId}/tasks', [projectController::class, 'createTask']);
    Route::post('/tasks/{taskId}/assign', [projectController::class, 'assignTask']);
    Route::put('/projects/{projectId}', [projectController::class, 'editProject']);
    Route::post('/projects/{projectId}', [projectController::class, 'deleteProject']);
    Route::get('/projects/{id}', [projectController::class, 'show']);
    Route::post('/projects/{projectId}/assign-lead', [projectController::class, 'assignLead']);
    Route::post('/projects/{projectId}/remove-lead', [projectController::class, 'removeLead']);


    // reports
    Route::post('/reports/generate', [OpenAiController::class, 'prompt']);
    Route::get('/reports', [ReportsController::class, 'index']);
    Route::get('/reports/{projectId}', [ReportsController::class, 'getReport']);



    // Task routes
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'addTask']); // add task to project
    Route::put('/tasks/{taskId}', [TaskController::class, 'editTask']); // edit task
    Route::delete('/tasks/{taskId}', [TaskController::class, 'deleteTask']); // delete task
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignTask']); // assign task to user
    Route::post('/tasks/{taskId}/remove-user', [TaskController::class, 'removeUserFromTask']); // remove user from task


    // user management
    Route::get('/verifiedUsers', [commonController::class, 'getVerifiedUser']); // get all verified users
    Route::get("/users", [UserController::class, 'getAllUsers']); // get all users
    Route::put("/users/update/{id}", [UserController::class, 'update']); // update the user
    Route::delete("/users/delete/{id}", [UserController::class, 'destroy']); // delete the user











    Route::prefix('profile')->middleware('auth:api')->group(function () {
        Route::put('/update', [profileController::class, 'updateProfile']);
    });
});
Route::prefix('activities')->middleware('auth:api')->group(function () {
    Route::get('/recent', [activitiesController::class, 'activities']);
    Route::get("/all", [activitiesController::class, 'getAllActivities']);
    Route::delete("/delete/{id}", [activitiesController::class, 'deleteActivity']);
    Route::post('/comment', [activitiesController::class, 'commentOnActivity']);
    Route::get('/comments/{id}', [activitiesController::class, 'getComments']);
});


Route::put('/tasks/{taskId}/status', [TaskController::class, 'updateTaskStatus'])->middleware('auth:sanctum'); // update task status
Route::put('/tasks/{taskId}/need-help', [TaskController::class, 'updateNeedHelp'])->middleware('auth:sanctum'); // update need help status




Route::prefix('user')->middleware('auth:api')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'getUserDashboardData']);
});

Route::post('subscribe', [NotificationController::class, 'subscribe'])->middleware('auth:sanctum'); // save fcm token for push nofifications
Route::post('send-notification', [NotificationController::class, 'sendNotification'])->middleware('auth:sanctum'); // send push notification

Route::get('notifications', [NotificationController::class, 'getNotifications'])->middleware('auth:sanctum');
Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->middleware('auth:sanctum');
Route::delete('notifications/{id}', [NotificationController::class, 'deleteNotification'])->middleware('auth:sanctum');

Route::get('/tasks/{taskId}/chat', [TaskController::class, 'getTaskChat'])->middleware('auth:sanctum');
Route::post('/tasks/{taskId}/join-chat', [TaskController::class, 'joinTaskChat'])->middleware('auth:sanctum');

Route::prefix('padmin')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [ProjectAdminDashboardController::class, 'index']);
    Route::post('/tasks', [ProjectAdminDashboardController::class, 'addTask']);
    Route::put('/tasks/{taskId}', [ProjectAdminDashboardController::class, 'updateTask']);
    Route::delete('/tasks/{taskId}', [ProjectAdminDashboardController::class, 'deleteTask']);
    Route::put('/tasks/status/update/{taskId}', [ProjectAdminDashboardController::class, 'updateTaskStatus']);
    Route::post('/members', [ProjectAdminDashboardController::class, 'addMember']);
    Route::delete('/members/{memberId}', [ProjectAdminDashboardController::class, 'deleteMember']);
});
