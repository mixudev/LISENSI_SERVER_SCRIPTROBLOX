<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiKeyController extends Controller
{
    public function index(): View
    {
        $keys = AiKey::orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.admin.ai-keys.index', compact('keys'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider'  => ['required', 'string', 'in:gemini,groq,openrouter'],
            'api_key'   => ['required', 'string', 'max:255'],
            'model'     => ['required', 'string', 'max:100'],
            'priority'  => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'provider.required' => 'Provider wajib dipilih.',
            'provider.in'       => 'Provider harus berupa Gemini, Groq, atau OpenRouter.',
            'api_key.required'  => 'API Key wajib diisi.',
            'model.required'    => 'Model wajib diisi.',
            'priority.required' => 'Prioritas wajib diisi.',
        ]);

        AiKey::create([
            'provider'    => $validated['provider'],
            'api_key'     => $validated['api_key'],
            'model'       => $validated['model'],
            'priority'    => $validated['priority'],
            'is_active'   => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            'error_count' => 0,
        ]);

        return redirect()->route('admin.ai-keys.index')->with('success', 'API Key AI berhasil disimpan.');
    }

    public function update(Request $request, AiKey $aiKey): RedirectResponse
    {
        $validated = $request->validate([
            'provider'  => ['required', 'string', 'in:gemini,groq,openrouter'],
            'api_key'   => ['nullable', 'string', 'max:255'],
            'model'     => ['required', 'string', 'max:100'],
            'priority'  => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'provider.required' => 'Provider wajib dipilih.',
            'provider.in'       => 'Provider harus berupa Gemini, Groq, atau OpenRouter.',
            'model.required'    => 'Model wajib diisi.',
            'priority.required' => 'Prioritas wajib diisi.',
        ]);

        $updateData = [
            'provider'  => $validated['provider'],
            'model'     => $validated['model'],
            'priority'  => $validated['priority'],
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : false,
        ];

        // Hanya update api_key jika diisi (tidak kosong)
        if (!empty($validated['api_key'])) {
            $updateData['api_key'] = $validated['api_key'];
        }

        $aiKey->update($updateData);

        return redirect()->route('admin.ai-keys.index')->with('success', "API Key {$aiKey->provider} ({$aiKey->model}) berhasil diperbarui.");
    }

    public function toggleActive(AiKey $aiKey): RedirectResponse
    {
        $aiKey->update(['is_active' => !$aiKey->is_active]);
        $status = $aiKey->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "API Key untuk {$aiKey->provider} ({$aiKey->model}) berhasil {$status}.");
    }

    public function resetErrors(AiKey $aiKey): RedirectResponse
    {
        $aiKey->update(['error_count' => 0]);

        return back()->with('success', "Error count untuk {$aiKey->provider} ({$aiKey->model}) berhasil di-reset.");
    }

    public function destroy(AiKey $aiKey): RedirectResponse
    {
        $aiKey->delete();

        return redirect()->route('admin.ai-keys.index')->with('success', 'API Key AI berhasil dihapus.');
    }
}
