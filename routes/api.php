<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sadmin\projectController;

// Project routes
Route::prefix('sadmin')->middleware('auth:api')->group(function () {
    Route::post('/projects', [projectController::class, 'createProject']);
    Route::get('/projects', [projectController::class, 'index']);
    Route::post('/projects/{projectId}/members', [projectController::class, 'addMembers']);
    Route::post('/projects/{projectId}/tasks', [projectController::class, 'createTask']);
    Route::post('/tasks/{taskId}/assign', [projectController::class, 'assignTask']);
    Route::put('/projects/{projectId}', [projectController::class, 'editProject']);
    Route::delete('/projects/{projectId}', [projectController::class, 'deleteProject']);
});
