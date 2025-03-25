<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Models\CallHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ApiUserController extends Controller
{
  
    public function index()
    {
        //
        // $users=User::all();
    
        // return response()->json( $users);

            // Fetch all users with their roles
    $users = User::with('roles')->get();
    
    return response()->json($users);

    }



    public function store(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'selectedRole' => 'required|exists:roles,name', // Ensure role exists
    ]);

    // Generate a random password
    $password = Str::random(8);
    $validatedData['password'] = Hash::make($password);

    // Create the user
    $user = User::create($validatedData);

    // Assign the selected role to the user
    $role = Role::where('name', $validatedData['selectedRole'])->first();
    if ($role) {
        $user->assignRole($role);
    } else {
        return response()->json(['message' => 'Role not found'], 404);
    }

    // Dispatch an event or send a welcome email
    event(new UserCreated($user, $password));

    // Return a response
    return response()->json(['message' => 'User created successfully', 'data' => $user], 201);
}

    

    public function update(Request $request, string $id)
{
    $user = User::findOrFail($id);

    $validatedData = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|required|string|min:8',
    ]);

    if ($request->has('password')) {
        $validatedData['password'] = bcrypt($validatedData['password']);
    }

    $user->update($validatedData);

    if ($request->has('role')) {
        $roleName = $request->input('role');
        $user->syncRoles([$roleName]);
    }

    return response()->json(['message' => 'User updated successfully', 'data' => $user]);
}


   

    public function destroy(string $id)
    {
        //

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }


    public function updateRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $roleName = $request->input('role');

        // Remove all current roles and assign the new role
        $user->syncRoles([$roleName]);

        return response()->json(['message' => 'Role updated successfully.']);
    }

    public function updatePermissions(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $permissions = $request->input('permissions', []);

        // Remove all current permissions and assign new ones
        $user->syncPermissions($permissions);

        return response()->json(['message' => 'Permissions updated successfully.']);
    }




     // Get all call agents (excluding soft-deleted ones)
     public function getCallAgentList()
     {
         $callAgents = User::all();
         return response()->json($callAgents);
     }
 
     // Get all available call agents
     public function getCallAgentListAvailable()
     {
         $callAgents = User::where('status', 'available')->get();
         return response()->json($callAgents);
     }
 
     // Get call agent details by user ID
     public function getCallAgentDetails(Request $request)
     {
         $request->validate(['id' => 'required|exists:users,id']);
         
         $user = user::withTrashed()->find($request->id);
         $callAgent = User::where('user_id', $request->id)->first();
 
         if (!$callAgent) {
             return response()->json(['error' => 'Call agent not found'], 404);
         }
 
         $response = [
             'id' => $callAgent->id,
             'phone_number' => $callAgent->phone_number,
             'client_name' => $callAgent->client_name,
             'user_id' => $callAgent->user_id,
             'status' => $callAgent->status,
             'sessionId' => $callAgent->sessionId,
             'token' => $callAgent->token,
             'first_name' => $user->first_name,
             'last_name' => $user->last_name,
             'email' => $user->email,
             'role' => $user->role,
             'created_at' => $callAgent->created_at,
             'updated_at' => $callAgent->updated_at,
         ];
 
         return response()->json($response);
     }
 
     // Create a new call agent
     public function createCallAgentDetails(Request $request)
     {
         $request->validate([
             'name' => 'required|string|max:255',
             'address' => 'required|string|max:255',
             'phone_number' => 'required|string|max:15',
             'email' => 'required|email|max:255|unique:call_agents,email',
             'country_id' => 'required|integer|exists:countries,id',
         ]);
 
         $callAgent = User::create($request->all());
 
         return response()->json([
             'success' => 1,
             'redirect' => route('user.call-agent'),
         ]);
     }
 
     // Update call agent details
     public function editCallAgentDetails(Request $request)
     {
         $request->validate([
             'id' => 'required|exists:call_agents,id',
             'phone_number' => 'required|string|max:15',
             'client_name' => 'required|string|max:255',
             'user_id' => 'required|exists:users,id',
             'status' => 'required|string|in:available,busy,offline',
         ]);
 
         $callAgent = User::findOrFail($request->id);
         $callAgent->update($request->all());
 
         return response()->json(['success' => 1]);
     }
 
     // Update call agent status
     public function editCallAgentStatus(Request $request)
     {
         $request->validate([
             'id' => 'required|exists:users,id',
             'status' => 'required|string|in:available,busy,offline',
         ]);
 
         $callAgent = User::where('user_id', $request->id)->first();
 
         if (!$callAgent) {
             return response()->json(['success' => 0], 404);
         }
 
         $callAgent->update(['status' => $request->status]);
         return response()->json(['success' => 1]);
     }
 
     // Soft delete a call agent
     public function deleteCallAgentDetails(Request $request)
     {
         $request->validate(['id' => 'required|exists:call_agents,id']);
 
         $callAgent = User::findOrFail($request->id);
         $callAgent->delete();
 
         return response()->json([
             'success' => 1,
             'redirect' => route('user.call_agent'),
         ]);
     }
 
     // Get call agent summary
     public function getCallAgentSummary(Request $request)
     {
         $request->validate(['id' => 'required|exists:users,id']);
 
         $userId = $request->id;
 
         $summary = [
             'summary_call_completed' => CallHistory::where('userId', $userId)
                 ->where('isActive', 0)
                 ->whereDate('created_at', Carbon::today())
                 ->count(),
             'summary_call_duration' => CallHistory::where('userId', $userId)
                 ->where('isActive', 0)
                 ->whereDate('created_at', Carbon::today())
                 ->sum('durationInSeconds'),
             'summary_call_missed' => CallHistory::where('userId', $userId)
                 ->where('isActive', 0)
                 ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
                 ->whereDate('created_at', Carbon::today())
                 ->count(),
             'summary_call_waiting' => CallHistory::where('isActive', 1)
                 ->where('nextCallStep', 'enqueue')
                 ->whereNotNull('conference')
                 ->count(),
 
                 // officer hangup 
                 'summary_call_hangedup' => CallHistory::where('userId', $userId)
                 ->where('isActive', 0)
                 ->whereIn('hangupCause', ['USER BUSY'])
                 ->whereDate('created_at', Carbon::today())
                 ->count(),
 
                 
 
         ];
 
         return response()->json($summary);
     }
}
