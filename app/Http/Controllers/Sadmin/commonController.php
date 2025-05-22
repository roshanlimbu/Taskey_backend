<?php

namespace App\Http\Controllers\sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class commonController extends Controller
{
    
    //  get all users
    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    
}
