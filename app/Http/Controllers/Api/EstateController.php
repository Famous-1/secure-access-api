<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EstateController extends Controller
{
    /**
     * Get all estates (Admin only - for super admin scenarios)
     */
    public function index(Request $request)
    {
        // Only allow if user is admin and has no estate (super admin)
        if (auth()->user()->usertype !== 'admin' || auth()->user()->estate_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $estates = Estate::where('is_active', true)->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $estates
        ]);
    }

    /**
     * Create a new estate (typically during admin registration)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:estates,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate code if not provided
        $code = $request->code ?? Str::slug($request->name) . '-' . Str::random(6);

        $estate = Estate::create([
            'name' => $request->name,
            'code' => $code,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'description' => $request->description,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estate created successfully',
            'data' => $estate
        ], 201);
    }

    /**
     * Get current user's estate
     */
    public function show()
    {
        $user = auth()->user();
        
        if (!$user->estate_id) {
            return response()->json([
                'success' => false,
                'message' => 'User is not associated with an estate'
            ], 404);
        }

        $estate = Estate::findOrFail($user->estate_id);
        
        return response()->json([
            'success' => true,
            'data' => $estate
        ]);
    }

    /**
     * Update estate (Admin only for their estate)
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        if ($user->usertype !== 'admin' || !$user->estate_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $estate = Estate::findOrFail($user->estate_id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:estates,code,' . $estate->id,
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $estate->update($request->only([
            'name', 'code', 'address', 'phone', 'email', 'description', 'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Estate updated successfully',
            'data' => $estate
        ]);
    }
}
