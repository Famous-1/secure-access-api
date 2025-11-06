<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Get current user's estate ID
     */
    protected function getEstateId()
    {
        return auth()->user()->estate_id;
    }

    /**
     * Get activities for the authenticated user
     */
    public function index(Request $request)
    {
        $estateId = $this->getEstateId();
        $query = Activity::where('estate_id', $estateId)
            ->where('user_id', auth()->id());
        
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
     * Get all activities (Admin and Maintainer only)
     */
    public function adminIndex(Request $request)
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $estateId = $this->getEstateId();
        $query = Activity::with('user')
            ->where('estate_id', $estateId);
        
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
        
        $estateId = $this->getEstateId();
        $query = Activity::recent($days)
            ->where('estate_id', $estateId);
        
        // If user is not admin or maintainer, only show their activities
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            $query->where('user_id', auth()->id());
        } else {
            // Admin and Maintainer can see all activities or filter by user
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
     * Get activity statistics (Admin and Maintainer only)
     */
    public function statistics(Request $request)
    {
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $days = $request->get('days', 30);
        
        $estateId = $this->getEstateId();
        $stats = [
            'total_activities' => Activity::where('estate_id', $estateId)->count(),
            'recent_activities' => Activity::where('estate_id', $estateId)->recent($days)->count(),
            'by_action' => Activity::where('estate_id', $estateId)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            'by_user' => Activity::with('user')
                ->where('estate_id', $estateId)
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'daily_activities' => Activity::where('estate_id', $estateId)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
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
        $estateId = $this->getEstateId();
        $activity = Activity::with('user')
            ->where('estate_id', $estateId)
            ->findOrFail($id);
        
        // Users can only view their own activities unless they're admin or maintainer
        if (!in_array(auth()->user()->usertype, ['admin', 'maintainer']) && $activity->user_id !== auth()->id()) {
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