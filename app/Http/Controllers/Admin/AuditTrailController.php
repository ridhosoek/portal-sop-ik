<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('actor')
            ->when($request->query('action'), fn ($query, $action) => $query->where('action', 'like', "%{$action}%"))
            ->when($request->query('actor'), fn ($query, $actor) => $query->whereHas('actor', fn ($actorQuery) => $actorQuery->where('name', 'like', "%{$actor}%")->orWhere('email', 'like', "%{$actor}%")))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
        ]);
    }
}
