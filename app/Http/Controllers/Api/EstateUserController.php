<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class EstateUserController extends Controller
{
    /**
     * Get all users (Admin only)
     */
    public function index(Request $request)
    {
        // Admin middleware is already applied in routes
        
        $query = User::query();
        
        // Filter by user type
        if ($request->has('usertype')) {
            $query->where('usertype', $request->usertype);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Create a new user (Admin only)
     */
    public function store(Request $request)
    {
        // Admin middleware is already applied in routes
        
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'apartment_unit' => 'required|string|max:255',
            'full_address' => 'required|string',
            'usertype' => 'required|in:resident,admin,maintainer',
            'status' => 'required|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'apartment_unit' => $request->apartment_unit,
            'full_address' => $request->full_address,
            'usertype' => $request->usertype,
            'status' => $request->status,
            'password' => Hash::make('password123'), // Default password
            'email_verified_at' => now()
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'user_created',
            'description' => "Created user: {$user->firstname} {$user->lastname}",
            'related_type' => 'App\Models\User',
            'related_id' => $user->id,
            'metadata' => [
                'created_user_email' => $user->email,
                'created_user_type' => $user->usertype
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Get a specific user
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Users can only view their own profile unless they're admin
        if (auth()->user()->usertype !== 'admin' && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user (Admin only or user updating themselves)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Users can only update their own profile unless they're admin
        if (auth()->user()->usertype !== 'admin' && auth()->id() !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|string|max:20',
            'apartment_unit' => 'sometimes|string|max:255',
            'full_address' => 'sometimes|string',
            'usertype' => 'sometimes|in:resident,admin,maintainer',
            'status' => 'sometimes|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'firstname', 'lastname', 'email', 'phone', 
            'apartment_unit', 'full_address', 'usertype', 'status'
        ]));

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'user_updated',
            'description' => "Updated user: {$user->firstname} {$user->lastname}",
            'related_type' => 'App\Models\User',
            'related_id' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Delete user (Admin only)
     */
    public function destroy($id)
    {
        // Admin middleware is already applied in routes
        
        $user = User::findOrFail($id);
        
        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        $user->delete();

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'user_deleted',
            'description' => "Deleted user: {$user->firstname} {$user->lastname}",
            'related_type' => 'App\Models\User',
            'related_id' => $user->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
