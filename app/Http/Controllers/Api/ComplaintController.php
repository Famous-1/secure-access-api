<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    /**
     * Get complaints for the authenticated user
     */
    public function index(Request $request)
    {
        $query = Complaint::where('user_id', auth()->id());
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by severity
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        
        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $complaints
        ]);
    }

    /**
     * Create a new complaint/suggestion
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:complaint,suggestion',
            'category' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $complaint = Complaint::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'category' => $request->category,
            'severity' => $request->severity,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'complaint_created',
            'description' => "Submitted {$request->type}: {$request->title}",
            'related_type' => 'App\Models\Complaint',
            'related_id' => $complaint->id,
            'metadata' => [
                'type' => $request->type,
                'category' => $request->category,
                'severity' => $request->severity,
                'title' => $request->title
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->type) . ' submitted successfully',
            'data' => $complaint
        ], 201);
    }

    /**
     * Get a specific complaint
     */
    public function show($id)
    {
        $complaint = Complaint::with(['replies.user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $complaint
        ]);
    }

    /**
     * Update complaint (Admin only)
     */
    public function update(Request $request, $id)
    {
        // Check if user is admin
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can update complaints.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,acknowledged,in_progress,resolved,closed',
            'admin_notes' => 'sometimes|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $complaint = Complaint::findOrFail($id);
        
        $updateData = $request->only(['status', 'admin_notes']);
        
        // If status is being changed to resolved, set resolved_at and resolved_by
        if ($request->has('status') && $request->status === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = auth()->id();
        }

        $complaint->update($updateData);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'complaint_updated',
            'description' => "Updated complaint: {$complaint->title}",
            'related_type' => 'App\Models\Complaint',
            'related_id' => $complaint->id,
            'metadata' => [
                'status' => $complaint->status,
                'admin_notes' => $request->admin_notes ?? null
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint updated successfully',
            'data' => $complaint->load(['user', 'resolvedBy'])
        ]);
    }

    /**
     * Get all complaints (Admin only)
     */
    public function adminIndex(Request $request)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Complaint::with(['user', 'resolvedBy']);
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by severity
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        
        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $complaints
        ]);
    }

    /**
     * Get complaint statistics (Admin only)
     */
    public function statistics()
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $stats = [
            'total' => Complaint::count(),
            'pending' => Complaint::where('status', 'pending')->count(),
            'acknowledged' => Complaint::where('status', 'acknowledged')->count(),
            'in_progress' => Complaint::where('status', 'in_progress')->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'closed' => Complaint::where('status', 'closed')->count(),
            'by_type' => [
                'complaints' => Complaint::where('type', 'complaint')->count(),
                'suggestions' => Complaint::where('type', 'suggestion')->count()
            ],
            'by_severity' => [
                'low' => Complaint::where('severity', 'low')->count(),
                'medium' => Complaint::where('severity', 'medium')->count(),
                'high' => Complaint::where('severity', 'high')->count(),
                'critical' => Complaint::where('severity', 'critical')->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get complaint categories
     */
    public function categories()
    {
        $categories = [
            'Security',
            'Maintenance',
            'Utilities',
            'Parking',
            'Common Areas',
            'Noise',
            'Cleaning',
            'Access Control',
            'Elevator',
            'Garden/Landscaping',
            'Waste Management',
            'Internet/WiFi',
            'Other'
        ];

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get a specific complaint (Admin only)
     */
    public function adminShow($id)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $complaint = Complaint::with(['user', 'resolvedBy', 'replies.user'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $complaint
        ]);
    }

    /**
     * Reply to a complaint (Admin only)
     */
    public function reply(Request $request, $id)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can reply to complaints.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $complaint = Complaint::findOrFail($id);

        $reply = ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'message' => $request->message
        ]);

        // Update complaint status to in_progress if it's pending
        if ($complaint->status === 'pending') {
            $complaint->update(['status' => 'in_progress']);
        }

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'action' => 'complaint_replied',
            'description' => "Replied to complaint: {$complaint->title}",
            'related_type' => 'App\Models\Complaint',
            'related_id' => $complaint->id,
            'metadata' => [
                'reply' => $request->message
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully',
            'data' => $reply->load('user')
        ], 201);
    }
}