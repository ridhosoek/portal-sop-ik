@extends('layouts.app')

@section('content')
    <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-semibold">Laporan Link Bermasalah</h1>
        <div class="mt-5 overflow-hidden rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Dokumen</th>
                        <th class="px-4 py-3">Pelapor</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($reports as $report)
                        <tr class="align-top">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.documents.show', $report->document) }}" class="font-medium hover:text-red-800">{{ $report->document->title }}</a>
                                <p class="text-xs text-slate-500">{{ $report->document->document_number }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $report->reporter->name }}</td>
                            <td class="px-4 py-3">{{ $report->note ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $report->status }}</td>
                            <td class="px-4 py-3">
                                @if($report->status !== 'resolved' && auth()->user()->canManageDocuments())
                                    <form method="POST" action="{{ route('admin.broken-links.resolve', $report) }}" class="flex gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input name="resolution_note" placeholder="Catatan selesai" class="w-48 rounded border border-slate-300 px-2 py-1 text-xs">
                                        <button class="rounded bg-red-700 px-3 py-1 text-xs font-medium text-white">Selesai</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500">{{ $report->resolver?->name }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $reports->links() }}</div>
    </section>
@endsection

