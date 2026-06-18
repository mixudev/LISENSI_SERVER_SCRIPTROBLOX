<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = LicenseActivity::with(['user', 'license']);

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $activities = $query->latest()->paginate(50);
        $actions = LicenseActivity::distinct()->pluck('action')->sort()->values();

        return view('dashboard.admin.activities.index', compact('activities', 'actions'));
    }
}
