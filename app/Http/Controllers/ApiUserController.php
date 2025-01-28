<?php

namespace App\Http\Controllers;

use App\Events\UserCreated;
use App\Models\User;
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

    // public function store(Request $request)
    // {
    //     //

    //     // dd($request);

    //       //
    //       $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email',
    //         // 'phone' => 'required|string|max:255',
    //         // 'role_id' => 'required|string|max:255',
    //         // 'branch_id' => 'required|string|max:255',
    //         'selectedRole' => 'required|string|max:255',
            
        
    //     ]);
    //     $password = Str::random(8);
    // $validatedData['password'] = Hash::make($password);


    // dd($validatedData);

  
    //     $user = User::create($validatedData);

    //     // dd($validatedData);
        
    //     // dispatch  mail welcome maiil with message 
    //     event(new UserCreated($user, $password));
    //         //   Mail::to($user->email)->send(new WelcomeUser($user->email,$user->name, $password,));
        
    //     return response()->json(['message' => ' created successfully', 'data' => $user], 201);
    
    // }

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


    // public function update(Request $request, string $id)
    // {
    //     //

    //     $user = User::findOrFail($id);

    //     $validatedData = $request->validate([
    //         'name' => 'sometimes|required|string|max:255',
    //         'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
    //         'password' => 'sometimes|required|string|min:8',
    //         // Uncomment these lines if you have additional fields
    //         // 'phone' => 'sometimes|required|string|max:255',
    //         // 'role_id' => 'sometimes|required|string|max:255',
    //         // 'branch_id' => 'sometimes|required|string|max:255',
    //     ]);

    //     if ($request->has('password')) {
    //         $validatedData['password'] = bcrypt($validatedData['password']);
    //     }

    //     $user->update($validatedData);
    //     // $password = Str::random(8);
    


    //     return response()->json(['message' => 'User updated successfully', 'data' => $user]);
    // }

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
}
