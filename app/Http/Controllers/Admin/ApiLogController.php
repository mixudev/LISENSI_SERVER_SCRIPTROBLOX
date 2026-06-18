<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ApiLog::query();

        if ($endpoint = $request->input('endpoint')) {
            $query->where('endpoint', 'like', "%{$endpoint}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($ip = $request->input('ip')) {
            $query->where('ip', $ip);
        }

        if ($robloxUser = $request->input('roblox_username')) {
            $query->where('roblox_username', 'like', "%{$robloxUser}%");
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->paginate(50);

        $summary = [
            'total_today'   => ApiLog::whereDate('created_at', today())->count(),
            'success_today' => ApiLog::whereDate('created_at', today())
                ->whereIn('status', ['success', 'inject_success'])->count(),
            'failed_today'  => ApiLog::whereDate('created_at', today())
                ->whereNotIn('status', ['success', 'inject_success'])->count(),
            'avg_response'  => (int) ApiLog::whereDate('created_at', today())->avg('response_time_ms'),
            'inject_today'  => ApiLog::whereDate('created_at', today())
                ->where('endpoint', 'like', '%inject%')->count(),
        ];

        return view('dashboard.admin.api-logs.index', compact('logs', 'summary'));
    }
}
