<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserRepository $users) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'role', 'is_active']);
        $users = $this->users->paginateForAdmin($filters);
        $users->load(['licenses.product']);

        return view('dashboard.admin.users.index', compact('users', 'filters'));
    }

    public function show(User $user): View
    {
        $user->load(['licenses.product', 'activities' => fn ($q) => $q->latest()->limit(20)]);

        return view('dashboard.admin.users.show', compact('user'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        // Tidak boleh menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun user berhasil {$status}.");
    }
}
