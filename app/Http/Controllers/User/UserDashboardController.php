<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class UserDashboardController extends Controller
{
    public function getUserDashboardData(Request $request)
    {
        $user = $request->user();
        $projects = Project::whereJsonContains('members', (string)$user->id)->get();
        $tasks = Task::where('assigned_to', $user->id)->get();
        Log::info($projects);
        Log::info($tasks);
        return response()->json([
            'projects' => $projects,
            'tasks' => $tasks
        ]);
    }
}
