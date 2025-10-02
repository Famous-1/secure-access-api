<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitorCode;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VisitorCodeController extends Controller
{
    /**
     * Get visitor codes for the authenticated user
     */
    public function index(Request $request)
    {
        $query = VisitorCode::where('user_id', auth()->id());
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $visitorCodes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $visitorCodes
        ]);
    }

    /**
     * Create a new visitor code
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitor_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'destination' => 'required|string|max:255',
            'number_of_visitors' => 'required|integer|min:1|max:10',
            'expires_at' => 'required|date|after:now',
            'additional_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate unique code
        do {
            $code = strtoupper(Str::random(6));
        } while (VisitorCode::where('code', $code)->exists());

        $visitorCode = VisitorCode::create([
            'user_id' => auth()->id(),
            'visitor_name' => $request->visitor_name,
            'phone_number' => $request->phone_number,
            'destination' => $request->destination,
            'number_of_visitors' => $request->number_of_visitors,
            'code' => $code,
            'expires_at' => $request->expires_at,
            'additional_notes' => $request->additional_notes,
            'status' => 'pending'
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'visitor_code_created',
            'description' => "Generated visitor code for {$request->visitor_name}",
            'related_type' => 'App\Models\VisitorCode',
            'related_id' => $visitorCode->id,
            'metadata' => [
                'visitor_name' => $request->visitor_name,
                'destination' => $request->destination,
                'code' => $code
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor code generated successfully',
            'data' => $visitorCode
        ], 201);
    }

    /**
     * Get a specific visitor code
     */
    public function show($id)
    {
        $visitorCode = VisitorCode::where('user_id', auth()->id())->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $visitorCode
        ]);
    }

    /**
     * Verify visitor code (Admin/Maintainer only)
     */
    public function verify(Request $request, $id)
    {
        // Check if user is admin or maintainer
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins and maintainers can verify codes.'
            ], 403);
        }

        $visitorCode = VisitorCode::findOrFail($id);
        
        if ($visitorCode->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code has expired'
            ], 400);
        }

        if ($visitorCode->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code has already been verified'
            ], 400);
        }

        $visitorCode->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'status' => 'active',
            'time_in' => now()
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'visitor_code_verified',
            'description' => "Verified visitor code for {$visitorCode->visitor_name} (Time In)",
            'related_type' => 'App\Models\VisitorCode',
            'related_id' => $visitorCode->id,
            'metadata' => [
                'visitor_name' => $visitorCode->visitor_name,
                'destination' => $visitorCode->destination,
                'code' => $visitorCode->code
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor code verified successfully',
            'data' => $visitorCode
        ]);
    }

    /**
     * Cancel visitor code
     */
    public function cancel($id)
    {
        $visitorCode = VisitorCode::where('user_id', auth()->id())->findOrFail($id);
        
        if ($visitorCode->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel verified visitor code'
            ], 400);
        }

        $visitorCode->update(['status' => 'cancelled']);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'visitor_code_cancelled',
            'description' => "Cancelled visitor code for {$visitorCode->visitor_name}",
            'related_type' => 'App\Models\VisitorCode',
            'related_id' => $visitorCode->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor code cancelled successfully'
        ]);
    }

    /**
     * Get all visitor codes (Admin/Maintainer only)
     */
    public function adminIndex(Request $request)
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = VisitorCode::with(['user', 'verifiedBy']);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $visitorCodes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $visitorCodes
        ]);
    }

    /**
     * Verify visitor code by code string (Admin/Maintainer only)
     */
    public function verifyByCode(Request $request)
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $visitorCode = VisitorCode::where('code', strtoupper($request->code))->first();
        
        if (!$visitorCode) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code not found'
            ], 404);
        }

        if ($visitorCode->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code has expired',
                'data' => $visitorCode
            ], 400);
        }

        if ($visitorCode->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code has already been verified',
                'data' => $visitorCode
            ], 400);
        }

        $visitorCode->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'status' => 'active',
            'time_in' => now()
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'visitor_code_verified_by_code',
            'description' => "Verified visitor code: {$visitorCode->code} for {$visitorCode->visitor_name} (Time In)",
            'related_type' => 'App\Models\VisitorCode',
            'related_id' => $visitorCode->id,
            'metadata' => [
                'visitor_name' => $visitorCode->visitor_name,
                'destination' => $visitorCode->destination,
                'code' => $visitorCode->code
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visitor code verified successfully',
            'data' => $visitorCode->load(['user', 'verifiedBy'])
        ]);
    }

    /**
     * Set time out for visitor code (Admin/Maintainer only)
     */
    public function setTimeOut(Request $request, $id)
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins and maintainers can set time out.'
            ], 403);
        }

        $visitorCode = VisitorCode::findOrFail($id);
        
        if ($visitorCode->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code must be active to set time out'
            ], 400);
        }

        $visitorCode->update([
            'time_out' => now(),
            'status' => 'complete'
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'visitor_code_time_out',
            'description' => "Set time out for visitor code: {$visitorCode->code} for {$visitorCode->visitor_name}",
            'related_type' => 'App\Models\VisitorCode',
            'related_id' => $visitorCode->id,
            'metadata' => [
                'visitor_name' => $visitorCode->visitor_name,
                'destination' => $visitorCode->destination,
                'code' => $visitorCode->code,
                'time_out' => now()->toISOString()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time out set successfully',
            'data' => $visitorCode
        ]);
    }

    /**
     * Set time in for visitor code (Admin/Maintainer only)
     */
    public function setTimeIn(Request $request, $id)
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins and maintainers can set time in.'
            ], 403);
        }

        $visitorCode = VisitorCode::findOrFail($id);
        
        if ($visitorCode->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Visitor code must be pending to set time in'
            ], 400);
        }

        $visitorCode->update([
            'time_in' => now(),
            'status' => 'active'
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'visitor_code_time_in',
            'description' => "Set time in for visitor code: {$visitorCode->code} for {$visitorCode->visitor_name}",
            'related_type' => 'App\Models\VisitorCode',
            'related_id' => $visitorCode->id,
            'metadata' => [
                'visitor_name' => $visitorCode->visitor_name,
                'destination' => $visitorCode->destination,
                'code' => $visitorCode->code,
                'time_in' => now()->toISOString()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Time in set successfully',
            'data' => $visitorCode
        ]);
    }
}