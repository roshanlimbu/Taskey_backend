<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{

    // add task to the project
    public function addTask(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $project = Project::findOrFail($projectId);
        Log::info('Authenticated user role:', ['role' => $request->user()->role, 'user_id' => $request->user()->user_id]);
        if ($request->user()->role != 1 || $request->user()->user_id!= $project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $projectId,
        ]);
        // DB::commit();
        return response()->json(['task' => $task], 200);
    }


    // edit task
    public function editTask(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 || $request->user()->user_id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return response()->json(['task' => $task], 200);
    }
    // update task status
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|string|max:255',
        ]);
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 || $request->user()->user_id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'status' => $request->status,
        ]);
        return response()->json(['task' => $task], 200);
    }


    // delete task
    public function deleteTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 || $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully'], 200);
    }

    // assign task to a user
    public function assignTask(Request $request, $taskId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 || $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'user_id' => $request->user_id,
        ]);
    }
    // remove user from task
    public function removeUserFromTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 || $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'user_id' => null,
        ]);
        return response()->json(['message' => 'User removed from task successfully'], 200);
    }
}
