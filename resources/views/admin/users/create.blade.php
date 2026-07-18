@extends('layouts.app')

@section('content')
    <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">Tambah User</h1>
                <p class="mt-1 text-sm text-slate-500">Buat akun internal dan langsung tetapkan role aksesnya.</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 max-w-3xl space-y-5">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
                    <input id="name" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                    <select id="status" name="status" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label for="department_id" class="block text-sm font-medium text-slate-700">Departemen</label>
                    <select id="department_id" name="department_id" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                        <option value="">Tanpa departemen</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <p class="block text-sm font-medium text-slate-700">Role</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <label class="inline-flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array((string) $role->id, old('roles', []), true))>
                            {{ $role->display_name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button class="rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Simpan User</button>
                <a href="{{ route('admin.users.index') }}" class="rounded border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </section>
@endsection

