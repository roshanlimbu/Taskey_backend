<?php

namespace App\Http\Controllers\sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class commonController extends Controller
{
    // Get all users of the current user's company (with verification status)
    public function getCompanyUsers()
    {
        $companyId = Auth::user()->company_id;
        $users = User::where('company_id', $companyId)->get();
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    // Toggle verification status for a user (only for same company)
    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = Auth::user();
        if ($user->company_id !== $currentUser->company_id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'is_user_verified' => 'required|boolean',
        ]);
        $user->is_user_verified = $request->input('is_user_verified');
        $user->save();
        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

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
