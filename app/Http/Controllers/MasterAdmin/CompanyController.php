<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use App\Models\company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return response()->json($companies);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $company = Company::create($request->all());
        return response()->json($company, 200);
    }

    public function show($id)
    {
        $company = Company::findOrFail($id);
        return response()->json($company);
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
}
