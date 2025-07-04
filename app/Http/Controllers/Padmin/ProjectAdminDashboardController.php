<?php

namespace App\Http\Controllers\Padmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Project;

class ProjectAdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = Project::where('project_lead_id', $user->id)->get();

        $tasks = Task::whereIn('project_id', $projects->pluck('id'))->get();

        $memberIds = $projects->pluck('members')->map(function ($members) {
            return json_decode($members, true);
        })->flatten()->unique()->filter()->values();
        $members = User::whereIn('id', $memberIds)->get();

        $chat = [];
        $chatMessages = [];

        return [
            true,
            [
                'projects' => $projects,
                'tasks' => $tasks,
                'members' => $members,

            ],
            'Project Admin Dashboard'
        ];
    }

    public function addTask($projectId, Request $request)
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $projectId,
            'status_id' => $request->status_id,
            'assigned_to' => $request->assigned_to,
        ]);
        return [true, $task, 'Task added successfully'];
    }
    public function editTask(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // $oldTitle = $task->title;
        // $oldDescription = $task->description;
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'need_help' => $request->has('need_help') ? $request->boolean('need_help') : $task->need_help,
        ]);

        return response()->json(['task' => $task], 200);
    }
    public function deleteTask($taskId)
    {
        $task = Task::find($taskId);
        $task->delete();
        return [true, $task, 'Task deleted successfully'];
    }
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status_id' => 'required|exists:status,id',
        ]);
        $task = Task::findOrFail($taskId);
        $task->update([
            'status_id' => $request->status_id,
        ]);
        return response()->json(['task' => $task], 200);
    }
    public function addMember($projectId, Request $request)
    {
        $member = User::create($request->all());
        return [true, $member, 'Member added successfully'];
    }
    public function deleteMember($memberId)
    {
        $member = User::find($memberId);
        $member->delete();
        return [true, $member, 'Member deleted successfully'];
    }


    public function assignTask(Request $request, $taskId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'assigned_to' => $request->user_id,
        ]);
        return response()->json(['message' => 'Task assigned successfully'], 200);
    }


    public function removeUserFromTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'assigned_to' => null,
        ]);
        return response()->json(['message' => 'User removed from task successfully'], 200);
    }
}
