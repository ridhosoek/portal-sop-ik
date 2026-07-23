<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with(['roles', 'department'])->orderBy('name')->paginate(20),
            'roles' => $this->manageableRoles($request->user()),
            'departments' => Department::query()->where('active', true)->orderBy('name')->get(),
            'canManageSuperAdmin' => $request->user()->hasRole('super-admin'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.create', [
            'roles' => $this->manageableRoles($request->user()),
            'departments' => Department::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $manageableRoleIds = $this->manageableRoleIds($request->user());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'status' => ['required', 'in:active,inactive'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,id', Rule::in($manageableRoleIds)],
        ], [
            'roles.*.in' => 'Role yang dipilih tidak dapat diberikan oleh akun Anda.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
            'department_id' => $data['department_id'] ?? null,
        ]);

        $user->roles()->sync($data['roles']);

        AuditLogger::record('user.created', $user, [], $user->fresh(['roles', 'department'])->toArray());

        return redirect()->route('admin.users.index')->with('status', 'User baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if(! $request->user()->hasRole('super-admin') && $user->hasRole('super-admin'), 403);

        $manageableRoleIds = $this->manageableRoleIds($request->user());

        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id', Rule::in($manageableRoleIds)],
        ], [
            'roles.*.in' => 'Role yang dipilih tidak dapat diberikan oleh akun Anda.',
        ]);

        $old = $user->load('roles')->toArray();

        $user->update([
            'status' => $data['status'],
            'department_id' => $data['department_id'] ?? null,
        ]);
        $user->roles()->sync($data['roles'] ?? []);

        AuditLogger::record('user_role.updated', $user, $old, $user->fresh('roles')->toArray());

        return back()->with('status', 'User dan role berhasil diperbarui.');
    }

    private function manageableRoles(User $actor)
    {
        return Role::query()
            ->when(! $actor->hasRole('super-admin'), fn ($query) => $query->where('name', '!=', 'super-admin'))
            ->orderBy('display_name')
            ->get();
    }

    private function manageableRoleIds(User $actor): array
    {
        return $this->manageableRoles($actor)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }
}
