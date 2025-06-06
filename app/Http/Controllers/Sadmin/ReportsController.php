<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\Reports;
use Illuminate\Support\Facades\Auth;


class ReportsController extends Controller
{
    public function index()
    {
        $reports = Reports::all();
        return response()->json([
            'success' => true,
            'data' => $reports,
            'message' => ['Reports retrieved successfully']
        ]);
    }
    public function getReport($projectId)
    {
        $query = Reports::where('project_id', $projectId);
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'No reports found for the specified project'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reports,
            'message' => 'Reports retrieved successfully'
        ]);
    }
}
