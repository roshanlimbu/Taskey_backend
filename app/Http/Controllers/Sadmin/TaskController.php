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
use App\Models\Chat;
use App\Models\ChatParticipant;

class TaskController extends Controller
{

    // add task to the project
    public function addTask(Request $request, $projectId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status_id' => 'nullable|exists:status,id',
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

        // If status_id is not provided, use the first id from status table
        $statusId = $request->status_id;
        if (!$statusId) {
            $statusId = DB::table('status')->orderBy('id')->value('id');
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status_id' => $statusId,
            'project_id' => $projectId,
            'need_help' => $request->has('need_help') ? $request->boolean('need_help') : false,
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
            'comments' => [],
            'reply' => [],
        ]);
        return response()->json(['task' => $task], 200);
    }



    // edit task
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
        $oldTitle = $task->title;
        $oldDescription = $task->description;
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'need_help' => $request->has('need_help') ? $request->boolean('need_help') : $task->need_help,
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
            'comments' => [],
            'reply' => [],
        ]);
        return response()->json(['task' => $task], 200);
    }
    // update task status
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status_id' => 'required|exists:status,id',
        ]);
        $task = Task::with('status')->findOrFail($taskId);
        // if ($request->user()->role != 1 && $request->user()->id != $task->project->project_lead_id) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }
        $oldStatus = $task->status ? $task->status->name : 'none';
        $task->update([
            'status_id' => $request->status_id,
        ]);
        // Reload task to get new status
        $task = Task::with('status')->findOrFail($taskId);
        $newStatus = $task->status ? $task->status->name : 'none';

        // Log activity for status update
        activities::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'type' => 'status_update',
            'title' => $task->title,
            'description' => 'Changed status from "' . $oldStatus . '" to "' . $newStatus . '"',
            'meta' => json_encode(['old_status' => $oldStatus, 'new_status' => $newStatus]),
            'comments' => [],
            'reply' => [],
        ]);
        return response()->json(['task' => $task], 200);
    }

    // update need help status
    public function updateNeedHelp(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update([
            'need_help' => $request->need_help,
        ]);
        // If marking as need_help, create chat if not exists and add owner as participant
        if ($request->need_help && !$task->chat) {
            $chat = Chat::create(['task_id' => $task->id]);
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $task->assigned_to ?? $request->user()->id,
            ]);
        }
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

    // Get chat info for a task
    public function getTaskChat($taskId)
    {
        $task = Task::findOrFail($taskId);
        $chat = $task->chat;
        if (!$chat) {
            return response()->json(['error' => 'No chat for this task'], 404);
        }
        $messages = $chat->messages()->with('user')->orderBy('created_at')->get();
        $participants = $chat->participants()->with('user')->get();
        return response()->json([
            'chat_id' => $chat->id,
            'messages' => $messages,
            'participants' => $participants,
        ]);
    }

    // Join chat as helper
    public function joinTaskChat(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $chat = $task->chat;
        if (!$chat) {
            return response()->json(['error' => 'No chat for this task'], 404);
        }
        $userId = $request->user()->id;
        $already = $chat->participants()->where('user_id', $userId)->exists();
        if (!$already) {
            ChatParticipant::create([
                'chat_id' => $chat->id,
                'user_id' => $userId,
            ]);
        }
        return response()->json(['message' => 'Joined chat', 'chat_id' => $chat->id]);
    }
}
