<?php

namespace App\Http\Controllers\sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class commonController extends Controller
{

    //  get all users
    public function getVerifiedUser()
    {
        $users = User::all()->where('is_user_verified', '=', 1);
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }


    public function getUserByGithubId(Request $request)
    {
        $githubId = $request->input('github_id');
        $user = User::where('github_id', $githubId)->first();
        if ($user) {
            return response()->json([
                'is_user_verified' => $user->is_user_verified,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    }
}
