@extends('layouts.app')

@section('content')
    <div class="grid gap-5 xl:grid-cols-2">
        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-xl font-semibold">Departemen</h1>
            @if(auth()->user()->canManageDocuments())
                <form method="POST" action="{{ route('admin.master-data.store', 'departments') }}" class="mt-4 grid gap-3 sm:grid-cols-[120px_1fr_auto]">
                    @csrf
                    <input name="code" placeholder="Kode" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <input name="name" placeholder="Nama departemen" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white">Tambah</button>
                </form>
            @endif
            <div class="mt-4 divide-y divide-slate-200 rounded border border-slate-200">
                @foreach($departments as $department)
                    <div class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                        <span><strong>{{ $department->code }}</strong> - {{ $department->name }}</span>
                        <form method="POST" action="{{ route('admin.master-data.toggle', ['departments', $department->id]) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded border border-slate-300 px-2 py-1 text-xs">{{ $department->active ? 'Aktif' : 'Nonaktif' }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-semibold">Jenis Dokumen</h2>
            @if(auth()->user()->canManageDocuments())
                <form method="POST" action="{{ route('admin.master-data.store', 'document-types') }}" class="mt-4 grid gap-3 sm:grid-cols-[120px_1fr_auto]">
                    @csrf
                    <input name="code" placeholder="Kode" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <input name="name" placeholder="Nama jenis" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white">Tambah</button>
                </form>
            @endif
            <div class="mt-4 divide-y divide-slate-200 rounded border border-slate-200">
                @foreach($types as $type)
                    <div class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                        <span><strong>{{ $type->code }}</strong> - {{ $type->name }}</span>
                        <form method="POST" action="{{ route('admin.master-data.toggle', ['document-types', $type->id]) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded border border-slate-300 px-2 py-1 text-xs">{{ $type->active ? 'Aktif' : 'Nonaktif' }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-semibold">Kategori</h2>
            @if(auth()->user()->canManageDocuments())
                <form method="POST" action="{{ route('admin.master-data.store', 'categories') }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                    @csrf
                    <input name="name" placeholder="Nama kategori" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <select name="parent_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Tanpa parent</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white">Tambah</button>
                </form>
            @endif
            <div class="mt-4 divide-y divide-slate-200 rounded border border-slate-200">
                @foreach($categories as $category)
                    <div class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                        <span>{{ $category->name }} <span class="text-slate-500">{{ $category->parent ? '- '.$category->parent->name : '' }}</span></span>
                        <form method="POST" action="{{ route('admin.master-data.toggle', ['categories', $category->id]) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded border border-slate-300 px-2 py-1 text-xs">{{ $category->active ? 'Aktif' : 'Nonaktif' }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
            <h2 class="text-xl font-semibold">Cakupan Departemen per Role</h2>
            <p class="mt-1 text-sm text-slate-500">Atur departemen yang bisa dilihat oleh role seperti Senior Manager.</p>

            <div class="mt-4 space-y-4">
                @foreach($roles as $role)
                    @php
                        $isGlobalRole = in_array($role->name, ['bod', 'document-admin', 'super-admin', 'auditor'], true);
                        $selectedDepartmentIds = $role->departments->pluck('id')->map(fn ($id) => (string) $id)->all();
                    @endphp

                    <div class="rounded border border-slate-200 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $role->display_name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $role->description ?: 'Role internal portal.' }}</p>
                            </div>
                            @if($isGlobalRole)
                                <span class="rounded bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">Akses global</span>
                            @endif
                        </div>

                        @if($isGlobalRole)
                            <p class="mt-3 text-sm text-slate-500">Role ini tidak dibatasi oleh cakupan departemen.</p>
                        @elseif(auth()->user()->canManageDocuments())
                            <form method="POST" action="{{ route('admin.master-data.roles.departments.update', $role) }}" class="mt-4">
                                @csrf
                                @method('PATCH')
                                <div class="flex flex-wrap gap-2">
                                    @foreach($activeDepartments as $department)
                                        <label class="inline-flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                                            <input type="checkbox" name="departments[]" value="{{ $department->id }}" @checked(in_array((string) $department->id, $selectedDepartmentIds, true))>
                                            {{ $department->name }}
                                        </label>
                                    @endforeach
                                </div>
                                <button class="mt-4 rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Simpan Cakupan</button>
                            </form>
                        @else
                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse($role->departments as $department)
                                    <span class="rounded bg-slate-100 px-3 py-1 text-sm">{{ $department->name }}</span>
                                @empty
                                    <span class="text-sm text-slate-500">Belum ada cakupan departemen.</span>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

    </div>
@endsection

