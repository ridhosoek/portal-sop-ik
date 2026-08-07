@extends('layouts.app')

@section('content')
    <div class="grid gap-5 lg:grid-cols-[1.6fr_0.8fr]">
        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-red-700">{{ $document->type->name }} - {{ $document->document_number }}</p>
                    <h1 class="mt-1 text-2xl font-semibold">{{ $document->title }}</h1>
                </div>
                <span class="rounded bg-red-100 px-3 py-1 text-sm font-medium text-red-800">{{ $document->status }}</span>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Departemen</dt>
                    <dd class="mt-1 font-medium">{{ $document->department->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Kategori</dt>
                    <dd class="mt-1 font-medium">{{ $document->category->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Versi aktif</dt>
                    <dd class="mt-1 font-medium">{{ $document->activeVersion?->version ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Efektif</dt>
                    <dd class="mt-1 font-medium">{{ $document->activeVersion?->effective_at?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Expired</dt>
                    <dd class="mt-1 font-medium">{{ $document->activeVersion?->expired_at?->format('d M Y') ?? '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <h2 class="font-semibold">Ringkasan</h2>
                <p class="mt-2 leading-7 text-slate-700">{{ $document->summary ?: '-' }}</p>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('documents.open', $document) }}" target="_blank" rel="noopener noreferrer" class="rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Buka Dokumen</a>
                @if(auth()->user()->canReadGovernance())
                    <a href="{{ route('admin.documents.show', $document) }}" class="rounded border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Kelola</a>
                @endif
            </div>
        </section>

        <aside class="space-y-5">
            <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Laporkan Link</h2>
                <form method="POST" action="{{ route('documents.broken-link-reports.store', $document) }}" class="mt-4 space-y-3">
                    @csrf
                    <textarea name="note" rows="4" class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100" placeholder="Catatan opsional">{{ old('note') }}</textarea>
                    <button class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Kirim Laporan</button>
                </form>
            </section>

            <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold">Histori Versi</h2>
                <div class="mt-4 space-y-3">
                    @foreach($document->versions as $version)
                        <div class="rounded border border-slate-200 p-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium">{{ $version->version }}</span>
                                <span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $version->status }}</span>
                            </div>
                            <p class="mt-2 text-slate-600">{{ $version->change_summary }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
@endsection

