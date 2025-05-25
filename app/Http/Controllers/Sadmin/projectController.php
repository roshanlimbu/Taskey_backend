<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        // calculate task completed 
        $projects = $projects->map(function ($project) {
            $tasks = Task::where('project_id', $project->id)->get();
            $completedTasks = $tasks->where('status', 'done')->count();
            $totalTasks = $tasks->count();
            $project->completed_tasks = $completedTasks;
            $project->total_tasks = $totalTasks;
            // Optionally, add percentage
            $project->progress_percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            return $project;
        });
        return response(
            ['projects' => $projects],
            200
        );
    }
    public function show($id){
        $project = Project::findOrFail($id);
        $tasks = Task::where('project_id', $id)->get();
        // Replace assigned_to with user name
        $tasks = $tasks->map(function ($task) {
            if ($task->assigned_to) {
                $user = \App\Models\User::find($task->assigned_to);
                $task->assigned_to = $user ? $user->name : null;
            } else {
                $task->assigned_to = null;
            }
            return $task;
        });
        $memberIds = $project->members ? json_decode($project->members, true) : [];
        $members = !empty($memberIds)
            ? User::whereIn('id', $memberIds)->get()
            : collect(); // empty collection if no members
        $projectLead = $project->project_lead_id ? User::find($project->project_lead_id) : null;
        $projectLeadName = $projectLead ? $projectLead->name : null;
        return response()->json([
            'project' => $project,
            'tasks'  => $tasks,
            'members' => $members,
            'project_lead_name' => $projectLeadName
        ], 200);
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

        // Get current members as array
        $currentMembers = $project->members ? json_decode($project->members, true) : [];
        // Merge and keep unique
        $allMembers = array_unique(array_merge($currentMembers, $request->member_ids));
        // Save back to the column
        $project->members = json_encode($allMembers);
        $project->save();

        return response()->json(['message' => 'Members added successfully']);
    }
    // remove members from a project
    public function removeMembers(Request $request, $projectId)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id',
        ]);
        $project = Project::findOrFail($projectId);
        $currentMembers = $project->members ? json_decode($project->members, true) : [];
        $project->members = json_encode(array_diff($currentMembers, $request->member_ids));
        $project->save();
        return response()->json(['message' => 'Members removed successfully']);
    }
    // assign lead of the prolject
    public function assignLead(Request $request, $projectId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $project = Project::findOrFail($projectId);
        $project->project_lead_id = $request->user_id;
        $project->save();
        return response()->json(['message' => 'Lead assigned successfully']);
    }
    // remove lead of the project
    public function removeLead(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $project->project_lead_id = null;
        $project->save();
        return response()->json(['message' => 'Lead removed successfully']);
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
            'due_date' => 'nullable|string',
        ]);
        // Log::info($request->all());
        
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
