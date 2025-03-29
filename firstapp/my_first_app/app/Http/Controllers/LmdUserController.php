<?php
namespace App\Http\Controllers;

use App\Models\LmdUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController; // Import the correct base controller

class LmdUserController extends BaseController // Extend the correct base controller
{
    public function index()
    {
        return LmdUser::all(); // Retrieve all users
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:lmd_users,email',
            'phone_no' => 'required|unique:lmd_users,phone_no',
            'password' => 'required',
        ]);

        $user = LmdUser::create($validated); // Create new user
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = LmdUser::findOrFail($id); // Retrieve user by ID
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = LmdUser::findOrFail($id); // Find user to update
        $user->update($request->all()); // Update user
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = LmdUser::findOrFail($id); // Find user to delete
        $user->delete(); // Delete user
        return response()->json(null, 204); // Return no content response
    }
}
