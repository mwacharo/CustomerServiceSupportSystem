<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class ApiBranchController extends Controller
{
    //



    public function index()
    {
        $branches = Branch::all();

        return response()->json([
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:255',
            'headquarters' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $branch = Branch::create($request->only('name', 'headquarters', 'phone_number', 'email'));

        return response()->json([
            'branch' => $branch,
            'message' => 'Branch created successfully',
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'headquarters' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $branch = Branch::findOrFail($id);
        $branch->update($request->only('name', 'headquarters', 'phone_number', 'email'));

        return response()->json([
            'branch' => $branch,
            'message' => 'Branch updated successfully',
        ]);
    }


    public function destroy(string $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json([
            'message' => 'Branch deleted successfully',
        ]);
    }
}



