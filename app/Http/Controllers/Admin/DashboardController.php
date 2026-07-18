<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BrokenLinkReport;
use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'draft' => Document::query()->where('status', Document::STATUS_DRAFT)->count(),
                'published' => Document::query()->where('status', Document::STATUS_PUBLISHED)->count(),
                'expired' => Document::query()->where('status', Document::STATUS_EXPIRED)->count(),
                'archived' => Document::query()->where('status', Document::STATUS_ARCHIVED)->count(),
                'review_due' => DocumentVersion::query()
                    ->whereNotNull('review_at')
                    ->whereDate('review_at', '<=', now()->addDays(30))
                    ->count(),
                'broken_links' => BrokenLinkReport::query()->where('status', BrokenLinkReport::STATUS_OPEN)->count(),
            ],
            'recentAudits' => AuditLog::query()->with('actor')->latest()->limit(8)->get(),
        ]);
    }
}
