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
}
