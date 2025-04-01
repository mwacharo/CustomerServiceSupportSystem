<?php

namespace App\Http\Controllers;

use App\Models\IvrOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiIvrOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Assuming you have a model named IvrOption
            $ivrOptions = IvrOption::all();

            Log::info('ApiIvrOptionController@index: Successfully fetched the resource listing.');

            return response()->json(['ivrOptions' => $ivrOptions], 200);
        } catch (\Exception $e) {
            Log::error('ApiIvrOptionController@index: Error occurred - ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch resource listing.'], 500);
        }
    }

  
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'option_number' => 'required|integer',
            'description' => 'required|string|max:255',
            'forward_number' => 'required|string|max:15',
            'status' => 'required|boolean',
        ]);

        try {
            // Assuming you have a model named IvrOption
            $ivrOption = new \App\Models\IvrOption();
            $ivrOption->option_number = $validatedData['option_number'];
            $ivrOption->description = $validatedData['description'];
            $ivrOption->forward_number = $validatedData['forward_number'];
            $ivrOption->status = $validatedData['status'];
            $ivrOption->save();

            Log::info('ApiIvrOptionController@store: Successfully stored the resource.', ['id' => $ivrOption->id]);

            return response()->json(['message' => 'Resource stored successfully.', 'data' => $ivrOption], 201);
        } catch (\Exception $e) {
            Log::error('ApiIvrOptionController@store: Error occurred - ' . $e->getMessage());
            return response()->json(['error' => 'Failed to store resource.'], 500);
        }
    }

    
  
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Your logic here
            Log::info("ApiIvrOptionController@update: Successfully updated the resource with ID {$id}.");
        } catch (\Exception $e) {
            Log::error("ApiIvrOptionController@update: Error occurred while updating resource with ID {$id} - " . $e->getMessage());
            return response()->json(['error' => 'Failed to update resource.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Your logic here
            Log::info("ApiIvrOptionController@destroy: Successfully deleted the resource with ID {$id}.");
        } catch (\Exception $e) {
            Log::error("ApiIvrOptionController@destroy: Error occurred while deleting resource with ID {$id} - " . $e->getMessage());
            return response()->json(['error' => 'Failed to delete resource.'], 500);
        }
    }
}
