@extends('layouts.app')

@section('content')
    <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <h1 class="text-2xl font-semibold">Audit Trail</h1>
                <p class="mt-1 text-sm text-slate-500">Jejak aktivitas administrasi dan akses dokumen.</p>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                <input name="actor" value="{{ request('actor') }}" placeholder="Aktor" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <input name="action" value="{{ request('action') }}" placeholder="Aksi" class="rounded border border-slate-300 px-3 py-2 text-sm">
                <button class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Filter</button>
            </form>
        </div>

        <div class="mt-5 overflow-hidden rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Aktor</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Objek</th>
                        <th class="px-4 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($logs as $log)
                        <tr class="align-top">
                            <td class="px-4 py-3">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3">{{ $log->actor?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                            <td class="px-4 py-3">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $logs->links() }}</div>
    </section>
@endsection

