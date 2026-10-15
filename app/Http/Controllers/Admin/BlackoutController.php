<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blackout;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlackoutController extends Controller
{
    public function index(Request $request): View
    {
        $query = Blackout::with('item.category');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->integer('item_id'));
        }

        $blackouts = $query->latest('start_at')->paginate(20)->withQueryString();
        $items = Item::active()->with(['category', 'images'])->orderBy('name')->get();

        return view('admin.blackouts.index', compact('blackouts', 'items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ], [
            'item_id.required' => 'Unit wajib dipilih.',
            'start_at.required' => 'Waktu mulai blackout wajib diisi.',
            'end_at.required' => 'Waktu selesai blackout wajib diisi.',
            'end_at.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        Blackout::create($validated);

        return redirect()->route('admin.blackouts.index')
            ->with('success', 'Jadwal blackout berhasil ditambahkan.');
    }

    public function destroy(Blackout $blackout): RedirectResponse
    {
        $blackout->delete();

        return redirect()->route('admin.blackouts.index')
            ->with('success', 'Jadwal blackout berhasil dihapus.');
    }
}
