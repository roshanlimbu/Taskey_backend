<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\activities;
use Illuminate\Support\Facades\Auth;

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


    public function commentOnActivity(Request $request)
    {
        $validator = validator($request->all(), [
            'activity_id' => 'required|exists:activities,id',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return res(false, [], $validator->errors()->all());
        }

        $activity = activities::find($request->activity_id);
        $activity->comments = json_encode([
            'user_id' => Auth::user()->id,
            'comment' => $request->comment,
        ]);
        $activity->save();
        return res(true, [], ['Comment added successfully']);
    }
    public function getComments($id)
    {
        $comments = activities::find($id)->comments;
        return res(true, $comments, ['Comments fetched successfully']);
    }
}
