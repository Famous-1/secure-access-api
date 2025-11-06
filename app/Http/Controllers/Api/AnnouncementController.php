<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    /**
     * Get current user's estate ID
     */
    protected function getEstateId()
    {
        return auth()->user()->estate_id;
    }

    /**
     * Get all announcements (for all users)
     */
    public function index(Request $request)
    {
        $estateId = $this->getEstateId();
        $query = Announcement::with('user')
            ->where('estate_id', $estateId)
            ->active()
            ->published();
        
        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        $announcements = $query->orderBy('priority', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }

    /**
     * Get a specific announcement
     */
    public function show($id)
    {
        $estateId = $this->getEstateId();
        $announcement = Announcement::with('user')
            ->where('estate_id', $estateId)
            ->active()
            ->published()
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $announcement
        ]);
    }

    /**
     * Get all announcements including inactive (Admin only)
     */
    public function adminIndex(Request $request)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $estateId = $this->getEstateId();
        $query = Announcement::with('user')
            ->where('estate_id', $estateId);
        
        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        $announcements = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }

    /**
     * Create a new announcement (Admin only)
     */
    public function store(Request $request)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can create announcements.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $estateId = $this->getEstateId();
        $announcement = Announcement::create([
            'user_id' => auth()->id(),
            'estate_id' => $estateId,
            'title' => $request->title,
            'content' => $request->content,
            'priority' => $request->priority,
            'published_at' => $request->published_at ?? now(),
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active ?? true
        ]);

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'estate_id' => $estateId,
            'action' => 'announcement_created',
            'description' => "Created announcement: {$request->title}",
            'related_type' => 'App\Models\Announcement',
            'related_id' => $announcement->id,
            'metadata' => [
                'title' => $request->title,
                'priority' => $request->priority
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully',
            'data' => $announcement
        ], 201);
    }

    /**
     * Update an announcement (Admin only)
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can update announcements.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $estateId = $this->getEstateId();
        $announcement = Announcement::where('estate_id', $estateId)->findOrFail($id);
        
        $announcement->update($request->only([
            'title', 'content', 'priority', 'published_at', 'expires_at', 'is_active'
        ]));

        // Log activity
        Activity::create([
            'user_id' => auth()->id(),
            'estate_id' => $estateId,
            'action' => 'announcement_updated',
            'description' => "Updated announcement: {$announcement->title}",
            'related_type' => 'App\Models\Announcement',
            'related_id' => $announcement->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => $announcement
        ]);
    }

    /**
     * Delete an announcement (Admin only)
     */
    public function destroy($id)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can delete announcements.'
            ], 403);
        }

        $estateId = $this->getEstateId();
        $announcement = Announcement::where('estate_id', $estateId)->findOrFail($id);
        
        // Log activity before deleting
        Activity::create([
            'user_id' => auth()->id(),
            'estate_id' => $estateId,
            'action' => 'announcement_deleted',
            'description' => "Deleted announcement: {$announcement->title}",
            'related_type' => 'App\Models\Announcement',
            'related_id' => $announcement->id
        ]);

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    }
}

