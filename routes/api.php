<?php

use App\Http\Controllers\Sadmin\commonController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sadmin\projectController;
use App\Http\Controllers\Sadmin\TaskController;
use App\Http\Controllers\Sadmin\profileController;
use App\Http\Controllers\Sadmin\activitiesController;
use App\Http\Controllers\User\UserDashboardController;

// Project routes
Route::prefix('sadmin')->middleware('auth:api')->group(function () {
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



    // Task routes
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'addTask']); // add task to project
    Route::put('/tasks/{taskId}', [TaskController::class, 'editTask']); // edit task
    Route::delete('/tasks/{taskId}', [TaskController::class, 'deleteTask']); // delete task
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignTask']); // assign task to user
    Route::post('/tasks/{taskId}/remove-user', [TaskController::class, 'removeUserFromTask']); // remove user from task


    Route::get('/users', [commonController::class, 'getAllUsers']); // get all users



    Route::prefix('profile')->middleware('auth:api')->group(function () {
        Route::put('/update', [profileController::class, 'updateProfile']);
    });
});
Route::prefix('activities')->middleware('auth:api')->group(function () {
    Route::get('/recent', [activitiesController::class, 'activities']);
});


Route::put('/tasks/{taskId}/status', [TaskController::class, 'updateTaskStatus'])->middleware('auth:api'); // update task status

Route::prefix('user')->middleware('auth:api')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'getUserDashboardData']);
});
