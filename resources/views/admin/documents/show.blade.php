@extends('layouts.app')

@section('content')
    <div class="grid gap-5 lg:grid-cols-[1.4fr_0.9fr]">
        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-red-700">{{ $document->document_number }}</p>
                    <h1 class="mt-1 text-2xl font-semibold">{{ $document->title }}</h1>
                    <p class="mt-2 text-slate-600">{{ $document->summary }}</p>
                </div>
                <span class="rounded bg-slate-100 px-3 py-1 text-sm">{{ $document->status }}</span>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div><dt class="text-xs uppercase tracking-wide text-slate-500">Jenis</dt><dd class="mt-1 font-medium">{{ $document->type->name }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-slate-500">Departemen</dt><dd class="mt-1 font-medium">{{ $document->department->name }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wide text-slate-500">Kategori</dt><dd class="mt-1 font-medium">{{ $document->category->name }}</dd></div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                @if(auth()->user()->canManageDocuments())
                    <a href="{{ route('admin.documents.edit', $document) }}" class="rounded border border-slate-300 px-4 py-2 font-medium hover:bg-slate-50">Ubah</a>
                    <form method="POST" action="{{ route('admin.documents.publish', $document) }}">
                        @csrf
                        <button class="rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Publish</button>
                    </form>
                    <form method="POST" action="{{ route('admin.documents.archive', $document) }}">
                        @csrf
                        <button class="rounded bg-slate-900 px-4 py-2 font-medium text-white hover:bg-slate-700">Archive</button>
                    </form>
                @endif
                @if($document->status === 'published')
                    <a href="{{ route('documents.show', $document) }}" class="rounded border border-slate-300 px-4 py-2 font-medium hover:bg-slate-50">Lihat Katalog</a>
                @endif
            </div>
        </section>

        <aside class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Draft Versi Baru</h2>
            @if(auth()->user()->canManageDocuments())
                <form method="POST" action="{{ route('admin.documents.versions.store', $document) }}" class="mt-4 space-y-3">
                    @csrf
                    <input name="version" placeholder="Contoh: 1.1" class="w-full rounded border border-slate-300 px-3 py-2 text-sm">
                    <textarea name="change_summary" rows="3" placeholder="Ringkasan perubahan" class="w-full rounded border border-slate-300 px-3 py-2 text-sm"></textarea>
                    <button class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Buat Draft</button>
                </form>
            @endif
        </aside>
    </div>

    <section class="mt-6 rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Histori Versi</h2>
        <div class="mt-4 overflow-hidden rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Versi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Efektif</th>
                        <th class="px-4 py-3">Review</th>
                        <th class="px-4 py-3">Expired</th>
                        <th class="px-4 py-3">Ringkasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($document->versions as $version)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $version->version }}</td>
                            <td class="px-4 py-3">{{ $version->status }}</td>
                            <td class="px-4 py-3">{{ $version->effective_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $version->review_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $version->expired_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $version->change_summary }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-6 rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Laporan Link</h2>
        <div class="mt-4 space-y-3">
            @forelse($document->brokenLinkReports as $report)
                <div class="rounded border border-slate-200 p-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <span class="font-medium">{{ $report->reporter->name }}</span>
                        <span>{{ $report->status }}</span>
                    </div>
                    <p class="mt-2 text-slate-600">{{ $report->note ?: '-' }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada laporan.</p>
            @endforelse
        </div>
    </section>
@endsection

