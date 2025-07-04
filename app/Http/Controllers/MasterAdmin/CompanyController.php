<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return response()->json($companies);
    }
    public function store(Request $request)
    {

        // $user = Auth::user();
        // if ($user->role != 0) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        // Check if the company already exists
        $existingCompany = Company::where('email', $request->email)->first();
        if ($existingCompany) {
            return response()->json(['error' => 'Company with this email already exists'], 400);
        }


        $company = Company::create($request->all());

        // assign the company id to the authenticated user
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            $user->company_id = $company->id;
            $user->save();
        } else {
            return response()->json(['error' => 'Authenticated user is invalid'], 500);
        }

        return response()->json($company, 200);
    }

    public function show($id)
    {
        $company = Company::findOrFail($id);
        $users = User::where('company_id', $id)->get();
        $projects = DB::table('projects')
            ->where('company_id', $id)
            ->get();
        $tasks = DB::table('tasks')
            ->whereIn('project_id', $projects->pluck('id'))
            ->get();
        return response()->json([
            'company' => $company,
            'users' => $users,
            'projects' => $projects,
            'tasks' => $tasks
        ]);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        $company = Company::findOrFail($id);
        $company->update($request->all());
        return response()->json($company, 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role != 0) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $company = Company::findOrFail($id);
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully'], 200);
    }


    public function assignCompanyToUser(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        // $user = Auth::user();
        // if ($user->role != 0) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $company = Company::findOrFail($request->company_id);
        $user = Auth::user();
        if ($user instanceof \App\Models\User) {
            $user->company_id = $company->id;
            $user->save();
        } else {
            return response()->json(['error' => 'Authenticated user is invalid'], 500);
        }
        $user->save();


        return response()->json(['message' => 'Company assigned to user successfully'], 200);
    }


    // company details
    public function companyDetails($id)
    {
        $company = Company::findOrFail($id);
        $users = User::where('company_id', $id)->get();
        $projects = DB::table('projects')
            ->where('company_id', $id)
            ->get();
        return response()->json([
            'company' => $company,
            'users' => $users,
            'projects' => $projects
        ]);
    }
}
