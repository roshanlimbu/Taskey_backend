<?php

use App\Http\Controllers\Sadmin\commonController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sadmin\projectController;
use App\Http\Controllers\Sadmin\TaskController;
use App\Http\Controllers\Sadmin\profileController;

// Project routes
Route::prefix('sadmin')->middleware('auth:api')->group(function () {
    Route::post('/projects', [projectController::class, 'createProject']);
    Route::get('/projects', [projectController::class, 'index']);
    Route::post('/projects/{projectId}/members', [projectController::class, 'addMembers']);
    Route::post('/projects/{projectId}/remove-members', [projectController::class, 'removeMembers']);
    Route::post('/projects/{projectId}/tasks', [projectController::class, 'createTask']);
    Route::post('/tasks/{taskId}/assign', [projectController::class, 'assignTask']);
    Route::put('/projects/{projectId}', [projectController::class, 'editProject']);
    Route::delete('/projects/{projectId}', [projectController::class, 'deleteProject']);
    Route::get('/projects/{id}', [projectController::class, 'show']);
    Route::post('/projects/{projectId}/assign-lead', [projectController::class, 'assignLead']);
    Route::post('/projects/{projectId}/remove-lead', [projectController::class, 'removeLead']);



    // Task routes
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'addTask']);
    Route::put('/tasks/{taskId}', [TaskController::class, 'editTask']);
    Route::put('/tasks/{taskId}/status', [TaskController::class, 'updateTaskStatus']);
    Route::delete('/tasks/{taskId}', [TaskController::class, 'deleteTask']);
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignTask']);
    Route::post('/tasks/{taskId}/remove-user', [TaskController::class, 'removeUserFromTask']);


    Route::get('/users', [commonController::class, 'getAllUsers']);
    


    Route::prefix('profile')->middleware('auth:api')->group(function () {
        Route::put('/update', [profileController::class, 'updateProfile']);
    });
});
