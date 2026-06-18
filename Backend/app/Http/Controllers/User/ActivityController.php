<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LicenseActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = LicenseActivity::where('user_id', auth()->id());

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $activities = $query->latest()->paginate(30);
        $actions = LicenseActivity::where('user_id', auth()->id())
            ->distinct()->pluck('action')->sort()->values();

        return view('dashboard.user.activities.index', compact('activities', 'actions'));
    }
}
