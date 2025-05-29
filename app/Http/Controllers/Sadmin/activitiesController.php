<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\activities;

class activitiesController extends Controller
{


    public function getAllActivities()
    {
        $activities = activities::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['activities' => $activities]);
    }



    public function activities()
    {
        $activities = activities::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['activities' => $activities]);
    }


    public function deleteActivity($id)
    {
        $activity = activities::find($id);

        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        $activity->delete();

        return response()->json(['message' => 'Activity deleted successfully']);
    }
}
