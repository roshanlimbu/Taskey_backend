<?php

namespace App\Http\Controllers\Sadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // get all users of the company
    public function getAllUsers()
    {

        $user = Auth::user();
        $users = User::where('company_id', $user->company_id)->get();
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'role' => 'required|integer|max:50',
            'dev_role' => 'nullable|string|max:50',
            'is_user_verified' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ], 404);
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->role = $request->input('role');
        $user->dev_role = $request->input('dev_role');
        $user->is_user_verified = $request->input('is_user_verified');
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
