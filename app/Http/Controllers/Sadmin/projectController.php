<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class projectController extends Controller
{
    // middleware to ensure only super admin (role == 1)
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role != 1) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return $next($request);
        });
    }


    public function index()
    {
        $projects = Project::all();
        return response(
            ['projects' => $projects],
            200
        );
    }
    public function show($id){
        $project = Project::findOrFail($id);
        $tasks = Task::where('project_id', $id)->get();
        return response()->json(['project' => $project, 'tasks' => $tasks], 200);
    }

    // create a new project
    public function createProject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return response()->json(['project' => $project], 201);
    }

    // add members to a project
    public function addMembers(Request $request, $projectId)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id',
        ]);
        $project = Project::findOrFail($projectId);
        $project->members()->syncWithoutDetaching($request->member_ids);
        return response()->json(['message' => 'Members added successfully']);
    }

    // create a task in a project
    public function createTask(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $project = Project::findOrFail($projectId);
        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return response()->json(['task' => $task], 201);
    }

    // assign a task to a user (lead or member)
    public function assignTask(Request $request, $taskId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $task = Task::findOrFail($taskId);
        $user = User::findOrFail($request->user_id);
        $task->assigned_to = $user->id;
        $task->save();
        return response()->json(['message' => 'Task assigned successfully']);
    }
    
    // edit func
    public function editProject(Request $request, $projectId){
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $project = Project::findOrFail($projectId);
        $project->update($request->all());
        return response()->json(['message' => 'Project updated successfully']);
    }

    // delete func
    public function deleteProject($projectId){
        $project = Project::findOrFail($projectId);
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully']);
    }
    
    
}
