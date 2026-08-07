<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\DocumentVersion;
use App\Rules\AllowedDocumentUrl;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = Document::query()
            ->with(['type', 'department', 'category', 'latestVersion'])
            ->search($request->query('q'))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.documents.index', [
            'documents' => $documents,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        return view('admin.documents.form', $this->formData());
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $document = DB::transaction(function () use ($data, $request): Document {
            $document = Document::create([
                'document_number' => Str::upper(trim($data['document_number'])),
                'title' => $data['title'],
                'type_id' => $data['type_id'],
                'department_id' => $data['department_id'],
                'category_id' => $data['category_id'],
                'summary' => $data['summary'] ?? null,
                'status' => Document::STATUS_DRAFT,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $version = $document->versions()->create([
                'version' => $data['version'],
                'url' => $data['url'],
                'effective_at' => $data['effective_at'] ?? null,
                'review_at' => $data['review_at'] ?? null,
                'expired_at' => $data['expired_at'] ?? null,
                'change_summary' => $data['change_summary'] ?? 'Versi awal',
                'status' => DocumentVersion::STATUS_DRAFT,
                'created_by' => $request->user()->id,
            ]);

            AuditLogger::record('document.created', $document, [], $document->fresh()->toArray());

            if ($data['status'] === Document::STATUS_PUBLISHED) {
                $this->publishVersion($document, $version, $request);
            }

            return $document;
        });

        return redirect()->route('admin.documents.show', $document)->with('status', 'Dokumen berhasil disimpan.');
    }

    public function show(Document $document): View
    {
        $document->load(['type', 'department', 'category', 'versions.creator', 'brokenLinkReports.reporter']);

        return view('admin.documents.show', [
            'document' => $document,
        ]);
    }

    public function edit(Request $request, Document $document): View
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        return view('admin.documents.form', $this->formData($document));
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = $request->validated();
        $oldDocument = $document->getOriginal();

        DB::transaction(function () use ($data, $document, $request, $oldDocument): void {
            $document->update([
                'document_number' => Str::upper(trim($data['document_number'])),
                'title' => $data['title'],
                'type_id' => $data['type_id'],
                'department_id' => $data['department_id'],
                'category_id' => $data['category_id'],
                'summary' => $data['summary'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            $latest = $document->latestVersion()->first();
            $versionPayload = [
                'version' => $data['version'],
                'url' => $data['url'],
                'effective_at' => $data['effective_at'] ?? null,
                'review_at' => $data['review_at'] ?? null,
                'expired_at' => $data['expired_at'] ?? null,
                'change_summary' => $data['change_summary'] ?? null,
                'created_by' => $request->user()->id,
            ];

            if (! $latest || $latest->status !== DocumentVersion::STATUS_DRAFT || $latest->version !== $data['version']) {
                $document->versions()->create($versionPayload + ['status' => DocumentVersion::STATUS_DRAFT]);
            } else {
                $latest->update($versionPayload);
            }

            AuditLogger::record('document.updated', $document, $oldDocument, $document->fresh()->toArray());
        });

        return redirect()->route('admin.documents.show', $document)->with('status', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        return $this->archive($request, $document);
    }

    public function publish(Request $request, Document $document): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $version = $document->latestVersion()->firstOrFail();
        $this->publishVersion($document, $version, $request);

        return back()->with('status', 'Dokumen berhasil dipublikasikan.');
    }

    public function archive(Request $request, Document $document): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $old = $document->getOriginal();

        $document->update([
            'status' => Document::STATUS_ARCHIVED,
            'archived_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        AuditLogger::record('document.archived', $document, $old, $document->fresh()->toArray());

        return redirect()->route('admin.documents.index')->with('status', 'Dokumen dipindahkan ke arsip.');
    }

    public function createDraftVersion(Request $request, Document $document): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $data = $request->validate([
            'version' => [
                'required',
                'string',
                'max:30',
                Rule::unique('document_versions', 'version')->where('document_id', $document->id),
            ],
            'change_summary' => ['required', 'string', 'max:2000'],
        ]);

        $source = $document->activeVersion()->first() ?? $document->latestVersion()->firstOrFail();

        $version = $document->versions()->create([
            'version' => $data['version'],
            'url' => $source->url,
            'effective_at' => $source->effective_at,
            'review_at' => $source->review_at,
            'expired_at' => $source->expired_at,
            'change_summary' => $data['change_summary'],
            'status' => DocumentVersion::STATUS_DRAFT,
            'created_by' => $request->user()->id,
        ]);

        AuditLogger::record('document.version_created', $version, [], $version->toArray());

        return redirect()->route('admin.documents.edit', $document)->with('status', 'Draft versi baru dibuat. Lengkapi metadata sebelum publish.');
    }

    private function publishVersion(Document $document, DocumentVersion $version, Request $request): void
    {
        Validator::make($version->toArray(), [
            'url' => ['required', 'url', new AllowedDocumentUrl()],
            'effective_at' => ['required', 'date'],
        ])->validate();

        DB::transaction(function () use ($document, $version, $request): void {
            $oldDocument = $document->getOriginal();

            $document->versions()
                ->whereKeyNot($version->id)
                ->where('status', DocumentVersion::STATUS_PUBLISHED)
                ->update(['status' => DocumentVersion::STATUS_SUPERSEDED]);

            $version->update([
                'status' => DocumentVersion::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);

            $document->update([
                'status' => Document::STATUS_PUBLISHED,
                'published_at' => now(),
                'archived_at' => null,
                'updated_by' => $request->user()->id,
            ]);

            AuditLogger::record('document.published', $document, $oldDocument, [
                'document' => $document->fresh()->toArray(),
                'version' => $version->fresh()->toArray(),
            ]);
        });
    }

    private function formData(?Document $document = null): array
    {
        return [
            'document' => $document,
            'version' => $document?->latestVersion()->first(),
            'types' => DocumentType::query()->where('active', true)->orderBy('name')->get(),
            'departments' => Department::query()->where('active', true)->orderBy('name')->get(),
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(),
        ];
    }
}
