<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class profileController extends Controller
{

    public function updateProfile(Request $request) {
        $validated = $request->validate([
            'github_id' => 'required|exists:users,github_id',
            'dev_role' => 'string|max:255',
            'role' => 'required|integer|in:1,2,3',
        ]);

        $user = User::where('github_id', $validated['github_id'])->first();
        $user->dev_role = $validated['dev_role'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }
}
