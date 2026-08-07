<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\Role;
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
            'roles' => Role::query()->with('departments')->orderBy('display_name')->get(),
            'activeDepartments' => Department::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $model = match ($type) {
            'departments' => Department::class,
            'document-types' => DocumentType::class,
            'categories' => Category::class,
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
        };

        if (isset($data['code'])) {
            $data['code'] = Str::upper(trim($data['code']));
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

    public function updateRoleDepartments(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->canManageDocuments(), 403);

        $data = $request->validate([
            'departments' => ['array'],
            'departments.*' => ['exists:departments,id'],
        ]);

        $old = $role->load('departments')->toArray();

        $role->departments()->sync($data['departments'] ?? []);

        AuditLogger::record('role_departments.updated', $role, $old, $role->fresh('departments')->toArray());

        return back()->with('status', 'Cakupan departemen role berhasil diperbarui.');
    }
}
