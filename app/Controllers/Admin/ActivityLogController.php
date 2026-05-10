<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLogController extends BaseController
{
    public function index()
    {
        $logModel = new ActivityLogModel();
        
        /**
         * We want to fetch the latest single row per user to avoid duplicates.
         * We use a subquery to find the MAX(id) for each user_identity,
         * then fetch the full rows corresponding to those IDs.
         */
        $db = \Config\Database::connect();
        $subQuery = $db->table('activity_logs')
                       ->select('MAX(id) as max_id')
                       ->groupBy('user_identity')
                       ->getCompiledSelect();

        $logs = $logModel->select('activity_logs.*, users.last_active, users.username as user_name')
                         ->join("($subQuery) as latest_logs", "latest_logs.max_id = activity_logs.id")
                         ->join('users', 'users.id = activity_logs.user_id', 'left')
                         ->orderBy('activity_logs.created_at', 'DESC')
                         ->paginate(20);

        $data = [
            'title' => 'System Activity Monitor',
            'logs'  => $logs,
            'pager' => $logModel->pager,
        ];

        return view('admin/activity/index', $data);
    }

    public function userTimeline($userId)
    {
        $userModel = new UserModel();
        $logModel = new ActivityLogModel();

        $user = $userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $logs = $logModel->where('user_id', $userId)
                         ->orderBy('created_at', 'DESC')
                         ->findAll();

        $data = [
            'title' => 'User Timeline: ' . ($user['username'] ?? 'Unknown'),
            'user'  => $user,
            'logs'  => $logs,
        ];

        return view('admin/activity/timeline', $data);
    }
}
