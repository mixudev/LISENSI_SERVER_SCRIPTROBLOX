<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        $userId = auth()->id();

        if ($userId && ! auth()->user()?->isAdmin()) {
            \App\Models\LicenseActivity::log(
                action: \App\Models\LicenseActivity::ACTION_LOGOUT,
                userId: $userId,
                request: $request
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
