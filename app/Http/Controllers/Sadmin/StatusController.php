<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Task;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index()
    {
        // Fetch all statuses
        $statuses = \App\Models\Status::all();
        return response()->json(['statuses' => $statuses], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7', //hex color code
            'description' => 'nullable|string|max:500',
        ]);

        $status = Status::create($request->all());

        return response()->json(['status' => $status], 201);
    }


    //delete function
    public function destroy($id)
    {
        $status = Status::find($id);
        if (!$status) {
            return response()->json(['message' => 'Status not found'], 404);
        }

        // Find the pending status to move tasks to
        $pendingStatus = Status::where('name', 'pending')->first();
        if (!$pendingStatus) {
            return response()->json(['message' => 'Pending status not found. Cannot delete status.'], 400);
        }

        // Don't allow deletion of the pending status itself
        if ($status->name === 'pending') {
            return response()->json(['message' => 'Cannot delete the pending status as it is used as a fallback.'], 400);
        }

        // Move all tasks with this status to pending status
        \App\Models\Task::where('status_id', $id)->update(['status_id' => $pendingStatus->id]);

        $status->delete();
        return response()->json(['message' => 'Status deleted successfully. All associated tasks moved to pending status.'], 200);
    }
}
