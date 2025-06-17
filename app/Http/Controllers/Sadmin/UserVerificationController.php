<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserVerificationController extends Controller
{


    public function getAllUsers()
    {
        $users = User::all();
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function verifyUser(Request $request)
    {
        $user = User::find($request->user_id);

        if ($user) {
            DB::table('users')->where('id', $request->user_id)->update(['is_user_verified' => 1]);
        }
    }
}
