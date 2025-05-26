<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\activities;
use Illuminate\Support\Facades\Auth;

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
        Log::info('Add Task Debug', [
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->role,
            'project_lead_id' => $project->project_lead_id
        ]);
        if ($request->user()->role != 1 && $request->user()->id != $project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'project_id' => $projectId,
        ]);
        // Log activity for new task
        activities::create([
            'user_id' => Auth::id(),
            'project_id' => $projectId,
            'task_id' => $task->id,
            'type' => 'new_task',
            'title' => $task->title,
            'description' => 'Added a new task to the project',
            'meta' => null,
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
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $oldTitle = $task->title;
        $oldDescription = $task->description;
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        // Log activity for editing task
        activities::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'type' => 'edit_task',
            'title' => $task->title,
            'description' => 'Edited task',
            'meta' => json_encode(['old_title' => $oldTitle, 'old_description' => $oldDescription]),
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
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $oldStatus = $task->status;
        $task->update([
            'status' => $request->status,
        ]);
        // Log activity for status update
        activities::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'type' => 'status_update',
            'title' => $task->title,
            'description' => 'Changed status from "' . $oldStatus . '" to "' . $request->status . '"',
            'meta' => json_encode(['old_status' => $oldStatus, 'new_status' => $request->status]),
        ]);
        return response()->json(['task' => $task], 200);
    }


    // delete task
    public function deleteTask(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
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
        if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $task->update([
            'assigned_to' => $request->user_id,
        ]);
        return response()->json(['message' => 'Task assigned successfully'], 200);
    }
    // remove user from task
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
