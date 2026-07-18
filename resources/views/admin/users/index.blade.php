@extends('layouts.app')

@section('content')
    <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">User & Role</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola akun, status, departemen, dan role pengguna.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Tambah User</a>
        </div>
        <div class="mt-5 space-y-4">
            @foreach($users as $user)
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded border border-slate-200 p-4">
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
                            <option value="">Tanpa departemen</option>
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
            @endforeach
        </div>
        <div class="mt-5">{{ $users->links() }}</div>
    </section>
@endsection

