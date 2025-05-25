<?php

namespace App\Http\Controllers\sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;

class commonController extends Controller
{

    //  get all users
    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }
}
