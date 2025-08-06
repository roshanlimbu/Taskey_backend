<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class projectController extends Controller
{
    // middleware to ensure only company owner (role == 1)
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
        $user = Auth::user();
        $projects = Project::where('company_id', $user->company_id)->get();
        // calculate task completed
        $projects = $projects->map(function ($project) {
            $tasks = Task::with('status')->where('project_id', $project->id)->get();
            $completedStatus = Status::where('name', 'completed')->first();
            $completedTasks = $completedStatus
                ? $tasks->where('status_id', $completedStatus->id)->count()
                : 0;
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
        $tasks = Task::with('status')->where('project_id', $id)->get();
        // Replace assigned_to with user name and include status
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
            'repo_url' => 'nullable|url|max:255',
        ]);
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'company_id' => Auth::user()->company_id, // the user's company id
            'repo_url' => $request->repo_url, // optional repo url
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
            'status_id' => 'nullable|exists:status,id',
        ]);
        $project = Project::findOrFail($projectId);
        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status_id' => $request->status_id,
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
            'repo_url' => 'nullable|url|max:255',
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

    // update task status
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status_id' => 'required|exists:status,id',
        ]);

        $task = Task::findOrFail($taskId);
        $task->status_id = $request->status_id;
        $task->save();

        return response()->json(['message' => 'Task status updated successfully']);
    }

    // get all statuses
    public function getStatuses()
    {
        $statuses = Status::all();
        return response()->json(['statuses' => $statuses], 200);
    }
}
