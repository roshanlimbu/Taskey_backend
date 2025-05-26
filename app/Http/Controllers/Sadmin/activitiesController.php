<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\activities;

class activitiesController extends Controller
{
    public function activities()
    {
        $activities = activities::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['activities' => $activities]);
    }
}
