@extends('layouts.app')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Dashboard Admin</h1>
            <p class="mt-1 text-sm text-slate-500">Status katalog, review, laporan link, dan audit terbaru.</p>
        </div>
        @if(auth()->user()->canManageDocuments())
            <a href="{{ route('admin.documents.create') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Tambah Dokumen</a>
        @endif
    </div>

    <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
        @foreach($stats as $label => $value)
            <div class="rounded border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-6 rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('admin.documents.index') }}" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">Dokumen</a>
            <a href="{{ route('admin.master-data.index') }}" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">Master Data</a>
            <a href="{{ route('admin.broken-links.index') }}" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">Laporan Link</a>
            <a href="{{ route('admin.audit.index') }}" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">Audit Trail</a>
            @if(auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">User & Role</a>
                <a href="{{ route('admin.settings.index') }}" class="rounded border border-slate-300 px-3 py-2 hover:bg-slate-50">Konfigurasi</a>
            @endif
        </div>
    </section>

    <section class="mt-6 rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Audit terbaru</h2>
        <div class="mt-4 overflow-hidden rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Aktor</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($recentAudits as $audit)
                        <tr>
                            <td class="px-4 py-3">{{ $audit->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $audit->actor?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $audit->action }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

