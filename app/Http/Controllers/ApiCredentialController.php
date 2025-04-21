<?php

namespace App\Http\Controllers;

use App\Models\ChannelCredential;
use App\Models\Vendor;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ApiCredentialController extends Controller
{
    /**
     * Get the list of credentials for a specific owner
     */


     public function index () {


        $credentials = ChannelCredential::all();

     
            return response()->json([
                'success' => true,
                'credentials' => $credentials
            ]);
     }


//     public function index()
// {
//     $user = Auth::user();

//     $credentials = ChannelCredential::where('credentialable_id', $user->id)
//         ->get();

//     return response()->json([
//         'success' => true,
//         'credentials' => $credentials
//     ]);
// }
    public function fetchCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credentialable_type' => 'required|string',
            'credentialable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = ChannelCredential::where('credentialable_type', $request->credentialable_type)
                ->where('credentialable_id', $request->credentialable_id)
                ->get();

            return response()->json([
                'success' => true,
                'credentials' => $credentials
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving credentials: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve credentials',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store or update multiple credentials for an owner
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credentialable_type' => 'required|string',
            'credentialable_id' => 'required|integer',
            'credentials' => 'required|array',
            'credentials.*.channel' => 'required|string',
            'credentials.*.provider' => 'nullable|string',
            'credentials.*.status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $savedCredentials = [];
            $credentialableType = $request->credentialable_type;
            $credentialableId = $request->credentialable_id;

            // Get the owner model to check if it exists
            $owner = $this->getOwnerModel($credentialableType, $credentialableId);
            if (!$owner) {
                return response()->json([
                    'message' => 'Owner not found'
                ], 404);
            }

            foreach ($request->credentials as $credentialData) {
                // Find existing credential or create new one
                $credential = ChannelCredential::firstOrNew([
                    'credentialable_type' => $credentialableType,
                    'credentialable_id' => $credentialableId,
                    'channel' => $credentialData['channel']
                ]);

                // Update credential fields
                $credential->provider = $credentialData['provider'] ?? null;
                $credential->api_key = $credentialData['api_key'] ?? null;
                $credential->api_secret = $credentialData['api_secret'] ?? null;
                $credential->access_token = $credentialData['access_token'] ?? null;
                $credential->access_token_secret = $credentialData['access_token_secret'] ?? null;
                $credential->auth_token = $credentialData['auth_token'] ?? null;
                $credential->client_id = $credentialData['client_id'] ?? null;
                $credential->client_secret = $credentialData['client_secret'] ?? null;
                $credential->user_name = $credentialData['user_name'] ?? null;
                $credential->password = $credentialData['password'] ?? null;
                $credential->account_sid = $credentialData['account_sid'] ?? null;
                $credential->account_id = $credentialData['account_id'] ?? null;
                $credential->app_id = $credentialData['app_id'] ?? null;
                $credential->app_secret = $credentialData['app_secret'] ?? null;
                $credential->page_access_token = $credentialData['page_access_token'] ?? null;
                $credential->page_id = $credentialData['page_id'] ?? null;
                $credential->phone_number = $credentialData['phone_number'] ?? null;
                $credential->email_address = $credentialData['email_address'] ?? null;
                $credential->webhook = $credentialData['webhook'] ?? null;
                $credential->value = $credentialData['value'] ?? null;
                $credential->description = $credentialData['description'] ?? null;
                $credential->status = $credentialData['status'];
                $credential->meta = $credentialData['meta'] ?? null;

                $credential->save();
                $savedCredentials[] = $credential;
            }

            return response()->json([
                'success' => true,
                'message' => 'Credentials saved successfully',
                'credentials' => $savedCredentials
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving credentials: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save credentials',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified credential
     */
    public function show(string $id)
    {
        try {
            $credential = ChannelCredential::findOrFail($id);
            return response()->json([
                'success' => true,
                'credential' => $credential
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Credential not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified credential
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            // Add other validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credential = ChannelCredential::findOrFail($id);
            
            // Update credential fields
            $credential->provider = $request->provider ?? $credential->provider;
            $credential->api_key = $request->api_key ?? $credential->api_key;
            $credential->api_secret = $request->api_secret ?? $credential->api_secret;
            $credential->access_token = $request->access_token ?? $credential->access_token;
            $credential->access_token_secret = $request->access_token_secret ?? $credential->access_token_secret;
            $credential->auth_token = $request->auth_token ?? $credential->auth_token;
            $credential->client_id = $request->client_id ?? $credential->client_id;
            $credential->client_secret = $request->client_secret ?? $credential->client_secret;
            $credential->user_name = $request->user_name ?? $credential->user_name;
            $credential->password = $request->password ?? $credential->password;
            $credential->account_sid = $request->account_sid ?? $credential->account_sid;
            $credential->account_id = $request->account_id ?? $credential->account_id;
            $credential->app_id = $request->app_id ?? $credential->app_id;
            $credential->app_secret = $request->app_secret ?? $credential->app_secret;
            $credential->page_access_token = $request->page_access_token ?? $credential->page_access_token;
            $credential->page_id = $request->page_id ?? $credential->page_id;
            $credential->phone_number = $request->phone_number ?? $credential->phone_number;
            $credential->email_address = $request->email_address ?? $credential->email_address;
            $credential->webhook = $request->webhook ?? $credential->webhook;
            $credential->value = $request->value ?? $credential->value;
            $credential->description = $request->description ?? $credential->description;
            $credential->status = $request->status;
            $credential->meta = $request->meta ?? $credential->meta;

            $credential->save();

            return response()->json([
                'success' => true,
                'message' => 'Credential updated successfully',
                'credential' => $credential
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update credential',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified credential
     */
    public function destroy(string $id)
    {
        try {
            $credential = ChannelCredential::findOrFail($id);
            $credential->delete();

            return response()->json([
                'success' => true,
                'message' => 'Credential deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete credential',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available credentialable types
     */
    public function getCredentialableTypes()
    {
        try {
            $types = [
                "App\\Models\\Vendor",
                "App\\Models\\Team",
                "App\\Models\\User"
            ];

            return response()->json([
                'success' => true,
                'types' => $types
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve credentialable types',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Helper function to get the owner model
     */
    private function getOwnerModel(string $type, int $id)
    {
        switch ($type) {
            case 'App\\Models\\Vendor':
                return Vendor::find($id);
            
            case 'App\\Models\\Team':
                return Team::find($id);
            
            case 'App\\Models\\User':
                return User::find($id);
            
            default:
                return null;
        }
    }



public function getOwnersByType(Request $request)
{
    $validator = Validator::make($request->all(), [
        'type' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Type parameter is required',
            'errors' => $validator->errors()
        ], 422);
    }

    $type = $request->type;

    try {
        $owners = [];

        switch ($type) {
            case 'App\\Models\\Vendor':
                $owners = Vendor::select('id', 'name')->get();
                break;
            
            case 'App\\Models\\Team':
                $owners = Team::select('id', 'name')->get();
                break;
            
            case 'App\\Models\\User':
                $owners = User::select('id', 'name')->get();
                break;
            
            default:
                return response()->json([
                    'message' => 'Invalid credentialable type'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'owners' => $owners
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to retrieve owners',
            'error' => $e->getMessage()
        ], 500);
    }
}
}