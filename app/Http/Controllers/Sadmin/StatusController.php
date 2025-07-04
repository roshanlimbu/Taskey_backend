<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Status;
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
        $status->delete();
        return response()->json(['message' => 'Status deleted successfully'], 200);
    }
}
