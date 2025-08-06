<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\activities;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class activitiesController extends Controller
{


    public function getAllActivities()
    {
        $user = Auth::user();

        // Get activities only from projects that belong to the user's company
        $activities = activities::with(['user', 'comments.user'])
            ->whereHas('project', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['activities' => $activities]);
    }



    public function activities()
    {
        $user = Auth::user();

        // Get activities only from projects that belong to the user's company
        $activities = activities::with(['user', 'comments.user'])
            ->whereHas('project', function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
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

        $comment = Comment::create([
            'activity_id' => $request->activity_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        return res(true, $comment, ['Comment added successfully']);
    }
    public function getComments($id)
    {
        $comments = activities::find($id)->comments;
        return res(true, $comments, ['Comments fetched successfully']);
    }
}
