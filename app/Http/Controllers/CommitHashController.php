<?php

namespace App\Http\Controllers;

use App\Models\Task_commit_hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CommitHashController extends Controller
{

    public function getCommitHashesByTaskId(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'required|exists:tasks,id',
        ]);

        // Fetch commit hashes for the given task ID
        $commitHashes = Task_commit_hash::where('task_id', $request->task_id)
            ->where('project_id', $request->project_id)
            ->get();

        if ($commitHashes->isEmpty()) {
            return response()->json(['message' => 'No commit hashes found for this task'], 404);
        }

        return response()->json($commitHashes, 200);
    }


    public function add(Request $request)
    {

        $user = Auth::user();


        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'project_id' => 'required|exists:projects,id',
            'commit_hash' => 'required|string|max:40',
        ]);

        $commitHash = new Task_commit_hash();
        $commitHash->task_id = $request->task_id;
        $commitHash->project_id = $request->project_id;
        $commitHash->commit_hash = $request->commit_hash;
        $commitHash->user_id = $user->id;
        $commitHash->save();

        return response()->json(['message' => 'Commit hash stored successfully'], 201);
    }
}
