@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <h1 class="text-2xl font-semibold">Katalog Dokumen</h1>
                <p class="mt-1 text-sm text-slate-500">SOP dan IK berstatus published dan masih berlaku.</p>
            </div>
        </div>

        <form class="rounded border border-slate-200 bg-white p-4 shadow-sm" method="GET" action="{{ route('documents.index') }}">
            <div class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_150px_170px_170px_130px_auto]">
                <input name="q" value="{{ request('q') }}" type="search" placeholder="Cari dokumen" class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                <select name="type_id" class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                    <option value="">Semua jenis</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" @selected(request('type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                <select name="department_id" class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                    <option value="">Semua departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <select name="category_id" class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                    <option value="newest" @selected(request('sort') === 'newest')>Terbaru</option>
                    <option value="title" @selected(request('sort') === 'title')>Judul</option>
                    <option value="number" @selected(request('sort') === 'number')>Nomor</option>
                </select>
                <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Filter</button>
            </div>
        </form>

        @if($documents->isEmpty())
            <div class="rounded border border-dashed border-slate-300 bg-white px-5 py-10 text-center shadow-sm">
                <p class="text-sm font-medium text-slate-700">Tidak ada dokumen sesuai filter.</p>
                <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci, jenis, departemen, atau kategori dokumen.</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($documents as $document)
                    <article class="group flex h-full flex-col rounded border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <span class="rounded bg-red-50 px-3 py-1 text-xs font-semibold text-red-800">{{ $document->type->name }}</span>
                            <span class="rounded bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ $document->document_number }}</span>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('documents.show', $document) }}" class="text-lg font-semibold leading-snug text-slate-950 hover:text-red-800">
                                {{ $document->title }}
                            </a>
                            <p class="mt-3 line-clamp-3 min-h-16 text-sm leading-6 text-slate-600">{{ $document->summary ?: 'Tidak ada ringkasan.' }}</p>
                        </div>

                        <dl class="mt-5 grid grid-cols-3 gap-3 border-y border-slate-100 py-4 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Departemen</dt>
                                <dd class="mt-1 break-words font-medium text-slate-800">{{ $document->department->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Versi</dt>
                                <dd class="mt-1 font-medium text-slate-800">{{ $document->activeVersion?->version ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Review</dt>
                                <dd class="mt-1 font-medium text-slate-800">{{ $document->activeVersion?->review_at?->format('d M Y') ?? '-' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="rounded bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $document->category->name }}</span>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-3 pt-5">
                            <a href="{{ route('documents.show', $document) }}" class="rounded border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:border-red-300 hover:bg-red-50 hover:text-red-800">Detail</a>
                            <a href="{{ route('documents.open', $document) }}" target="_blank" rel="noopener noreferrer" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Buka</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div>
            {{ $documents->links() }}
        </div>
    </div>
@endsection
