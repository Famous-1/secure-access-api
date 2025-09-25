<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Get activities for the authenticated user
     */
    public function index(Request $request)
    {
        $query = Activity::where('user_id', auth()->id());
        
        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Filter by recent days
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get all activities (Admin only)
     */
    public function adminIndex(Request $request)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Activity::with('user');
        
        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Filter by recent days
        if ($request->has('recent_days')) {
            $query->recent($request->recent_days);
        }

        $activities = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get recent activities for dashboard
     */
    public function recent(Request $request)
    {
        $days = $request->get('days', 7);
        $limit = $request->get('limit', 10);
        
        $query = Activity::recent($days);
        
        // If user is not admin, only show their activities
        if (auth()->user()->usertype !== 'admin') {
            $query->where('user_id', auth()->id());
        } else {
            // Admin can see all activities or filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }

        $activities = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }

    /**
     * Get activity statistics (Admin only)
     */
    public function statistics(Request $request)
    {
        if (auth()->user()->usertype !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $days = $request->get('days', 30);
        
        $stats = [
            'total_activities' => Activity::count(),
            'recent_activities' => Activity::recent($days)->count(),
            'by_action' => Activity::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'by_user' => Activity::with('user')
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'daily_activities' => Activity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get activity by ID
     */
    public function show($id)
    {
        $activity = Activity::with('user')->findOrFail($id);
        
        // Users can only view their own activities unless they're admin
        if (auth()->user()->usertype !== 'admin' && $activity->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $activity
        ]);
    }

    /**
     * Get available action types
     */
    public function actions()
    {
        $actions = [
            'user_created',
            'user_updated',
            'user_deleted',
            'visitor_code_created',
            'visitor_code_verified',
            'visitor_code_cancelled',
            'visitor_code_verified_by_code',
            'complaint_created',
            'complaint_updated',
            'login',
            'logout',
            'password_changed',
            'profile_updated'
        ];

        return response()->json([
            'success' => true,
            'data' => $actions
        ]);
    }
}