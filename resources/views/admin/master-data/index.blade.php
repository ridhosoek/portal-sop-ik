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

        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-xl font-semibold">Tag</h2>
            @if(auth()->user()->canManageDocuments())
                <form method="POST" action="{{ route('admin.master-data.store', 'tags') }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]">
                    @csrf
                    <input name="name" placeholder="Nama tag" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white">Tambah</button>
                </form>
            @endif
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($tags as $tag)
                    <span class="rounded bg-slate-100 px-3 py-1 text-sm">{{ $tag->name }}</span>
                @endforeach
            </div>
        </section>
    </div>
@endsection

