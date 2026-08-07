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

    <section class="grid gap-4 md:grid-cols-3 xl:grid-cols-7">
        @foreach($stats as $label => $value)
            <div class="rounded border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-6">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Menu Admin</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih area pengelolaan yang ingin dibuka.</p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('admin.documents.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Dokumen</span>
                <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">Kelola SOP & IK</span>
                <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Tambah, ubah, publish, dan archive dokumen internal.</span>
                <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
            </a>

            <a href="{{ route('admin.organization-structures.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Organisasi</span>
                <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">Struktur Organisasi</span>
                <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Upload PDF atau gambar struktur per departemen.</span>
                <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
            </a>

            <a href="{{ route('admin.master-data.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Referensi</span>
                <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">Master Data</span>
                <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Kelola departemen, kategori, dan jenis dokumen.</span>
                <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
            </a>

            <a href="{{ route('admin.broken-links.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Laporan</span>
                <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">Laporan Link</span>
                <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Tinjau dan selesaikan laporan link dokumen bermasalah.</span>
                <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
            </a>

            <a href="{{ route('admin.audit.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Audit</span>
                <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">Audit Trail</span>
                <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Lihat catatan aktivitas dan perubahan penting di portal.</span>
                <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
            </a>

            @if(auth()->user()->canManageUsers())
                <a href="{{ route('admin.users.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                    <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Akses</span>
                    <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">User & Role</span>
                    <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Tambah user dan atur role akses setiap pengguna.</span>
                    <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
                </a>
            @endif

            @if(auth()->user()->canManageSettings())
                <a href="{{ route('admin.settings.index') }}" class="group block rounded border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-300 hover:shadow-md">
                    <span class="text-xs font-semibold uppercase tracking-wide text-red-700">Sistem</span>
                    <span class="mt-2 block text-base font-semibold text-slate-950 group-hover:text-red-800">Konfigurasi</span>
                    <span class="mt-2 block min-h-10 text-sm leading-5 text-slate-500">Atur allowlist host, sesi admin, dan konfigurasi portal.</span>
                    <span class="mt-4 inline-flex text-sm font-medium text-red-700 group-hover:text-red-900">Buka menu ></span>
                </a>
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

