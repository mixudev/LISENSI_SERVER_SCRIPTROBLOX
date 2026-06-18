<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Services\ScriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ApiLog::query()->with('license:id,license_key,license_type,status');

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

        if ($product = $request->input('product_name')) {
            $query->where('product_name', 'like', "%{$product}%");
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->latest()->paginate(50);

        $summary = [
            'total_today' => ApiLog::whereDate('created_at', today())->count(),
            'success_today' => ApiLog::whereDate('created_at', today())->success()->count(),
            'failed_today' => ApiLog::whereDate('created_at', today())->failed()->count(),
            'avg_response' => (int) ApiLog::whereDate('created_at', today())->avg('response_time_ms'),
            'inject_today' => ApiLog::whereDate('created_at', today())
                ->where(function ($q) {
                    $q->where('endpoint', 'like', '%inject%')
                        ->orWhere('endpoint', 'like', '%license/get%')
                        ->orWhere('endpoint', 'like', 's/%');
                })->count(),
        ];

        return view('dashboard.admin.api-logs.index', compact('logs', 'summary'));
    }

    public function show(ApiLog $apiLog): JsonResponse
    {
        $apiLog->load('license:id,license_key,license_type,status,hwid,roblox_username,roblox_place_id');

        return response()->json([
            'ok' => true,
            'log' => $apiLog,
            'map_name' => ScriptService::getMapNameFromPlaceId($apiLog->roblox_place_id),
        ]);
    }
}
