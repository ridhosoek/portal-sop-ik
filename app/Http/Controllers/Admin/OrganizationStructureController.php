<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\OrganizationStructure;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationStructureController extends Controller
{
    public function index(Request $request): View
    {
        $structures = OrganizationStructure::query()
            ->with(['department', 'uploader'])
            ->when($request->query('q'), function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('update_note', 'like', "%{$search}%")
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('department_id'), fn ($query, $department) => $query->where('department_id', $department))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.organization-structures.index', [
            'structures' => $structures,
            'departments' => Department::query()->where('active', true)->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        return view('admin.organization-structures.form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $data = $this->validatedData($request);
        $filePayload = $this->storeFile($request->file('file'), $data['department_id'], $data['title']);

        $structure = OrganizationStructure::create($data + $filePayload + [
            'uploaded_by' => $request->user()->id,
            'published_at' => $data['status'] === OrganizationStructure::STATUS_PUBLISHED ? now() : null,
        ]);

        AuditLogger::record('organization_structure.created', $structure, [], $structure->toArray());

        return redirect()->route('admin.organization-structures.show', $structure)->with('status', 'Struktur organisasi berhasil ditambahkan.');
    }

    public function show(OrganizationStructure $organizationStructure): View
    {
        $organizationStructure->load(['department', 'uploader']);

        return view('admin.organization-structures.show', [
            'structure' => $organizationStructure,
        ]);
    }

    public function edit(Request $request, OrganizationStructure $organizationStructure): View
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        return view('admin.organization-structures.form', $this->formData($organizationStructure));
    }

    public function update(Request $request, OrganizationStructure $organizationStructure): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $data = $this->validatedData($request, $organizationStructure);
        $old = $organizationStructure->getOriginal();
        $filePayload = [];

        if ($request->hasFile('file')) {
            $filePayload = $this->storeFile($request->file('file'), $data['department_id'], $data['title']);
            Storage::disk('local')->delete($organizationStructure->file_path);
        }

        $organizationStructure->update($data + $filePayload + [
            'uploaded_by' => $request->user()->id,
            'published_at' => $data['status'] === OrganizationStructure::STATUS_PUBLISHED
                ? ($organizationStructure->published_at ?? now())
                : null,
        ]);

        AuditLogger::record('organization_structure.updated', $organizationStructure, $old, $organizationStructure->fresh()->toArray());

        return redirect()->route('admin.organization-structures.show', $organizationStructure)->with('status', 'Struktur organisasi berhasil diperbarui.');
    }

    public function archive(Request $request, OrganizationStructure $organizationStructure): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $old = $organizationStructure->getOriginal();

        $organizationStructure->update([
            'status' => OrganizationStructure::STATUS_ARCHIVED,
            'published_at' => null,
            'uploaded_by' => $request->user()->id,
        ]);

        AuditLogger::record('organization_structure.archived', $organizationStructure, $old, $organizationStructure->fresh()->toArray());

        return redirect()->route('admin.organization-structures.index')->with('status', 'Struktur organisasi dipindahkan ke arsip.');
    }

    private function validatedData(Request $request, ?OrganizationStructure $organizationStructure = null): array
    {
        $data = $request->validate([
            'department_id' => ['required', Rule::exists('departments', 'id')->where('active', true)],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'update_note' => ['nullable', 'string', 'max:4000'],
            'effective_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in($this->statuses())],
            'file' => [
                $organizationStructure ? 'nullable' : 'required',
                'file',
                'mimes:pdf,png,jpg,jpeg,webp',
                'max:10240',
            ],
        ]);

        unset($data['file']);

        return $data;
    }

    private function storeFile(UploadedFile $file, int $departmentId, string $title): array
    {
        $department = Department::findOrFail($departmentId);
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = now()->format('YmdHis').'-'.Str::slug($title).'.'.$extension;
        $path = $file->storeAs('organization-structures/'.Str::slug($department->code), $fileName, 'local');
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        return [
            'file_path' => $path,
            'original_file_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'file_type' => str_starts_with($mimeType, 'image/') ? 'image' : 'pdf',
            'file_size' => $file->getSize() ?: 0,
        ];
    }

    private function formData(?OrganizationStructure $organizationStructure = null): array
    {
        return [
            'structure' => $organizationStructure,
            'departments' => Department::query()->where('active', true)->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ];
    }

    private function statuses(): array
    {
        return [
            OrganizationStructure::STATUS_DRAFT,
            OrganizationStructure::STATUS_PUBLISHED,
            OrganizationStructure::STATUS_ARCHIVED,
        ];
    }
}
