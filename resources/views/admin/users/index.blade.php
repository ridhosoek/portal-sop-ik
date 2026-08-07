@extends('layouts.app')

@section('content')
    <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">User & Role</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola akun, status, departemen utama, role, dan reset password pengguna.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Tambah User</a>
        </div>
        <div class="mt-5 space-y-4">
            @foreach($users as $user)
                @php($isProtectedSuperAdmin = ! $canManageSuperAdmin && $user->hasRole('super-admin'))

                @if($isProtectedSuperAdmin)
                    <div class="rounded border border-slate-200 bg-slate-50 p-4">
                        <div class="grid gap-4 lg:grid-cols-[1fr_180px_220px_1.4fr_auto] lg:items-center">
                            <div>
                                <p class="font-semibold">{{ $user->name }}</p>
                                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                            </div>
                            <span class="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">{{ ucfirst($user->status) }}</span>
                            <span class="rounded border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">{{ $user->department?->name ?? 'Tanpa departemen utama' }}</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($user->roles as $role)
                                    <span class="rounded border border-slate-200 bg-white px-3 py-2 text-sm">{{ $role->display_name }}</span>
                                @endforeach
                            </div>
                            <span class="rounded bg-slate-200 px-4 py-2 text-center text-sm font-medium text-slate-700">Dilindungi</span>
                        </div>
                        <p class="mt-3 text-sm text-slate-500">Hanya super admin yang dapat mengubah akun super admin.</p>
                    </div>
                @else
                    <div class="rounded border border-slate-200 p-4">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('PATCH')
                            <div class="grid gap-4 lg:grid-cols-[1fr_180px_220px_1.4fr_auto] lg:items-center">
                                <div>
                                    <p class="font-semibold">{{ $user->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                                </div>
                                <select name="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
                                    <option value="active" @selected($user->status === 'active')>Active</option>
                                    <option value="inactive" @selected($user->status === 'inactive')>Inactive</option>
                                </select>
                                <select name="department_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">Tanpa departemen utama</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected($user->department_id === $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($roles as $role)
                                        <label class="inline-flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($user->roles->contains($role))>
                                            {{ $role->display_name }}
                                        </label>
                                    @endforeach
                                </div>
                                <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white">Simpan</button>
                            </div>
                        </form>

                        <details class="mt-4 border-t border-slate-200 pt-4">
                            <summary class="cursor-pointer text-sm font-semibold text-red-700 hover:text-red-900">Reset Password</summary>
                            <form method="POST" action="{{ route('admin.users.password.update', $user) }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_1fr_auto] lg:items-end">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="password_{{ $user->id }}" class="block text-sm font-medium text-slate-700">Password Baru</label>
                                    <input id="password_{{ $user->id }}" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                                </div>
                                <div>
                                    <label for="password_confirmation_{{ $user->id }}" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                                    <input id="password_confirmation_{{ $user->id }}" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                                </div>
                                <button class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Reset</button>
                            </form>
                            <p class="mt-2 text-xs text-slate-500">Minimal 8 karakter, berisi huruf dan angka.</p>
                        </details>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="mt-5">{{ $users->links() }}</div>
    </section>
@endsection

