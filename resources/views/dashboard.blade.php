@extends('layouts.app')

@section('content')
    <section class="grid gap-5 lg:grid-cols-[1.6fr_1fr]">
        <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-2xl font-semibold">Portal informasi internal</h1>
            <p class="mt-1 text-sm text-slate-500">Temukan SOP, IK, dan struktur organisasi sesuai hak akses departemen.</p>
            <form action="{{ route('documents.index') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                <input name="q" type="search" class="min-h-11 flex-1 rounded border border-slate-300 px-4 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100" placeholder="Cari nomor, judul, ringkasan, atau tag">
                <button class="rounded bg-red-700 px-5 py-2 font-medium text-white hover:bg-red-800">Cari</button>
            </form>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">Dokumen aktif</p>
                    <p class="mt-1 text-3xl font-semibold">{{ $publishedCount }}</p>
                </div>
                <div class="rounded border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">Jenis dokumen</p>
                    <p class="mt-1 text-3xl font-semibold">{{ $typeCount }}</p>
                </div>
                <div class="rounded border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">Departemen</p>
                    <p class="mt-1 text-3xl font-semibold">{{ $departmentCount }}</p>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('documents.index') }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Katalog Dokumen</a>
                <a href="{{ route('organization-structure.index') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Struktur Organisasi</a>
            </div>
        </div>

        <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Kategori</h2>
            <div class="mt-4 space-y-2">
                @foreach($categories as $category)
                    <a href="{{ route('documents.index', ['category_id' => $category->id]) }}" class="flex items-center justify-between rounded border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                        <span>{{ $category->name }}</span>
                        <span class="rounded bg-slate-100 px-2 py-1 text-xs text-slate-600">{{ $category->documents_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mt-6 rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Dokumen terbaru</h2>
            <a href="{{ route('documents.index') }}" class="text-sm font-medium text-red-700 hover:text-red-900">Semua dokumen</a>
        </div>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse($latestDocuments as $document)
                <a href="{{ route('documents.show', $document) }}" class="rounded border border-slate-200 p-4 hover:border-red-300 hover:bg-red-50/40">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-semibold text-slate-900">{{ $document->title }}</p>
                        <span class="rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-800">{{ $document->type->code }}</span>
                    </div>
                    <p class="mt-2 text-xs font-medium text-slate-500">{{ $document->document_number }}</p>
                    <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $document->summary }}</p>
                    <p class="mt-3 text-xs text-slate-500">{{ $document->department->name }} - {{ $document->activeVersion?->version }}</p>
                </a>
            @empty
                <p class="text-sm text-slate-500">Belum ada dokumen published.</p>
            @endforelse
        </div>
    </section>

    <section class="mt-6 rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Departemen</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($departments as $department)
                <a href="{{ route('documents.index', ['department_id' => $department->id]) }}" class="rounded border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                    {{ $department->name }} <span class="text-slate-500">({{ $department->documents_count }})</span>
                </a>
            @endforeach
        </div>
    </section>
@endsection

