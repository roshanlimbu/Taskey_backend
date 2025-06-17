<?php

namespace App\Http\Controllers\sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;

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
}
