<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => [
                'allowed_document_hosts' => Setting::value('allowed_document_hosts', implode(',', config('sop.allowed_document_hosts', []))),
                'allowed_intranet_hosts' => Setting::value('allowed_intranet_hosts', implode(',', config('sop.allowed_intranet_hosts', []))),
                'admin_idle_timeout_minutes' => Setting::value('admin_idle_timeout_minutes', (string) config('sop.admin_idle_timeout_minutes')),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'allowed_document_hosts' => ['required', 'string', 'max:2000'],
            'allowed_intranet_hosts' => ['nullable', 'string', 'max:2000'],
            'admin_idle_timeout_minutes' => ['required', 'integer', 'min:5', 'max:240'],
        ]);

        foreach ($data as $key => $value) {
            $setting = Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'type' => $key === 'admin_idle_timeout_minutes' ? 'integer' : 'string']
            );

            AuditLogger::record('setting.updated', $setting, [], $setting->toArray());
        }

        return back()->with('status', 'Konfigurasi sistem berhasil disimpan.');
    }
}
