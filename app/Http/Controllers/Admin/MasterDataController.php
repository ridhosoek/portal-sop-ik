<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\Tag;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        return view('admin.master-data.index', [
            'departments' => Department::query()->orderBy('name')->get(),
            'types' => DocumentType::query()->orderBy('name')->get(),
            'categories' => Category::query()->with('parent')->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $model = match ($type) {
            'departments' => Department::class,
            'document-types' => DocumentType::class,
            'categories' => Category::class,
            'tags' => Tag::class,
            default => abort(404),
        };

        $data = match ($type) {
            'departments', 'document-types' => $request->validate([
                'code' => ['required', 'string', 'max:30'],
                'name' => ['required', 'string', 'max:255'],
            ]),
            'categories' => $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'parent_id' => ['nullable', 'exists:categories,id'],
            ]),
            'tags' => $request->validate([
                'name' => ['required', 'string', 'max:255'],
            ]),
        };

        if (isset($data['code'])) {
            $data['code'] = Str::upper(trim($data['code']));
        }

        if ($type === 'tags') {
            $data['slug'] = Str::slug($data['name']);
        }

        /** @var Model $record */
        $record = $model::create($data + ['active' => true]);

        AuditLogger::record('master_data.created', $record, [], $record->toArray());

        return back()->with('status', 'Master data berhasil ditambahkan.');
    }

    public function toggle(Request $request, string $type, int $id): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $model = match ($type) {
            'departments' => Department::class,
            'document-types' => DocumentType::class,
            'categories' => Category::class,
            default => abort(404),
        };

        /** @var Model $record */
        $record = $model::findOrFail($id);
        $old = $record->getOriginal();
        $record->update(['active' => ! $record->active]);

        AuditLogger::record('master_data.toggled', $record, $old, $record->fresh()->toArray());

        return back()->with('status', 'Status master data diperbarui.');
    }
}
