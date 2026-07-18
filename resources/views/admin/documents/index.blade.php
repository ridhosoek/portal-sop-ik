@extends('layouts.app')

@section('content')
    <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <h1 class="text-2xl font-semibold">Kelola Dokumen</h1>
                <p class="mt-1 text-sm text-slate-500">Metadata, status publikasi, versi, dan link resmi.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" class="flex flex-wrap gap-2">
                    <input name="q" value="{{ request('q') }}" placeholder="Cari" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <select name="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua status</option>
                        @foreach(['draft', 'published', 'expired', 'archived'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Filter</button>
                </form>
                @if(auth()->user()->canManageDocuments())
                    <a href="{{ route('admin.documents.create') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Tambah</a>
                @endif
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Dokumen</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Departemen</th>
                        <th class="px-4 py-3">Versi</th>
                        <th class="px-4 py-3">Updated</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($documents as $document)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.documents.show', $document) }}" class="font-semibold hover:text-red-800">{{ $document->title }}</a>
                                <p class="mt-1 text-xs text-slate-500">{{ $document->document_number }}</p>
                            </td>
                            <td class="px-4 py-3"><span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $document->status }}</span></td>
                            <td class="px-4 py-3">{{ $document->department->name }}</td>
                            <td class="px-4 py-3">{{ $document->latestVersion?->version ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $document->updated_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.documents.show', $document) }}" class="text-sm font-medium text-red-700 hover:text-red-900">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada dokumen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $documents->links() }}</div>
    </div>
@endsection

