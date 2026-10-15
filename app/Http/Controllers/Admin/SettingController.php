<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'RentalHub'),
            'store_phone' => Setting::get('store_phone', '081234567890'),
            'store_address' => Setting::get('store_address', 'Jl. Merdeka No. 45, Jakarta'),
            'bank_name' => Setting::get('bank_name', 'BCA'),
            'bank_account_number' => Setting::get('bank_account_number', '8830123456'),
            'bank_account_holder' => Setting::get('bank_account_holder', 'PT RentalHub Nusantara'),
            'qris_image' => Setting::get('qris_image', ''),
            'expires_hours' => (int) Setting::get('expires_hours', 2),
            'late_fee_per_day_percent' => (int) Setting::get('late_fee_per_day_percent', 100),
            'cancellation_refund_percent' => (int) Setting::get('cancellation_refund_percent', 80),
            'weekly_discount_percent' => (int) Setting::get('weekly_discount_percent', 15),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_phone' => ['required', 'string', 'max:30'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'qris_file' => ['nullable', 'image', 'max:2048'],
            'expires_hours' => ['required', 'integer', 'min:1', 'max:72'],
            'late_fee_per_day_percent' => ['required', 'integer', 'min:0', 'max:500'],
            'cancellation_refund_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'weekly_discount_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'qris_file') {
                continue;
            }
            Setting::set($key, $value);
        }

        if ($request->hasFile('qris_file')) {
            $path = $request->file('qris_file')->store('settings', 'public');
            Setting::set('qris_image', Storage::url($path));
        }

        return back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
