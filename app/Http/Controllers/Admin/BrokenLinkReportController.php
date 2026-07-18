<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokenLinkReport;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrokenLinkReportController extends Controller
{
    public function index(): View
    {
        return view('admin.broken-links.index', [
            'reports' => BrokenLinkReport::query()
                ->with(['document.department', 'reporter', 'resolver'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function resolve(Request $request, BrokenLinkReport $report): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $data = $request->validate([
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $old = $report->getOriginal();
        $report->update([
            'status' => BrokenLinkReport::STATUS_RESOLVED,
            'resolved_by' => $request->user()->id,
            'resolution_note' => $data['resolution_note'] ?? null,
            'resolved_at' => now(),
        ]);

        AuditLogger::record('broken_link.resolved', $report, $old, $report->fresh()->toArray());

        return back()->with('status', 'Laporan link ditandai selesai.');
    }
}
