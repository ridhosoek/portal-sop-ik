<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrokenLinkReportRequest;
use App\Models\BrokenLinkReport;
use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Rules\AllowedDocumentUrl;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DocumentCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $documents = Document::query()
            ->with(['type', 'department', 'category', 'activeVersion'])
            ->visibleToUser($user)
            ->search($request->query('q'))
            ->when($request->query('type_id'), fn ($query, $type) => $query->where('type_id', $type))
            ->when($request->query('department_id'), fn ($query, $department) => $query->where('department_id', $department))
            ->when($request->query('category_id'), fn ($query, $category) => $query->where('category_id', $category))
            ->when($request->query('year'), function ($query, $year): void {
                $query->whereHas('versions', fn ($versionQuery) => $versionQuery->whereYear('effective_at', (int) $year));
            });

        match ($request->query('sort', 'newest')) {
            'title' => $documents->orderBy('title'),
            'number' => $documents->orderBy('document_number'),
            default => $documents->latest('published_at'),
        };

        return view('documents.index', [
            'documents' => $documents->paginate(12)->withQueryString(),
            'types' => DocumentType::query()->where('active', true)->orderBy('name')->get(),
            'departments' => $this->departmentOptions($user),
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, Document $document): View
    {
        abort_unless($document->isVisibleTo($request->user()) || $request->user()->canReadGovernance(), 404);

        $document->load(['type', 'department', 'category', 'activeVersion', 'versions.creator']);

        return view('documents.show', [
            'document' => $document,
        ]);
    }

    public function open(Request $request, Document $document): RedirectResponse
    {
        abort_unless($document->isVisibleTo($request->user()) || $request->user()->canReadGovernance(), 404);

        $version = $document->activeVersion()->firstOrFail();

        Validator::make(['url' => $version->url], [
            'url' => [new AllowedDocumentUrl()],
        ])->validate();

        AuditLogger::record('document.opened', $document, [], [
            'document_number' => $document->document_number,
            'version' => $version->version,
        ]);

        return redirect()->away($version->url);
    }

    public function report(StoreBrokenLinkReportRequest $request, Document $document): RedirectResponse
    {
        abort_unless($document->isVisibleTo($request->user()), 404);

        $report = BrokenLinkReport::create([
            'document_id' => $document->id,
            'reporter_id' => $request->user()->id,
            'note' => $request->validated('note'),
            'status' => BrokenLinkReport::STATUS_OPEN,
        ]);

        AuditLogger::record('broken_link.reported', $report, [], [
            'document_id' => $document->id,
            'note' => $report->note,
        ]);

        return back()->with('status', 'Laporan link bermasalah sudah masuk ke dashboard admin.');
    }

    private function departmentOptions($user)
    {
        return Department::query()
            ->where('active', true)
            ->when(! $user->canViewAllPublishedDocuments(), fn ($query) => $query->whereKey($user->department_id ?? 0))
            ->orderBy('name')
            ->get();
    }
}
