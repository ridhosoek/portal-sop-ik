@extends('layouts.app')

@section('content')
    <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <h1 class="text-2xl font-semibold">Kelola Struktur Organisasi</h1>
                <p class="mt-1 text-sm text-slate-500">Upload PDF atau gambar struktur organisasi per departemen.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <form method="GET" class="flex flex-wrap gap-2">
                    <input name="q" value="{{ request('q') }}" placeholder="Cari" class="rounded border border-slate-300 px-3 py-2 text-sm">
                    <select name="department_id" class="rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua departemen</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button class="rounded border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">Filter</button>
                </form>
                @if(auth()->user()->canManageDocuments())
                    <a href="{{ route('admin.organization-structures.create') }}" class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Tambah Struktur</a>
                @endif
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Struktur</th>
                        <th class="px-4 py-3">Departemen</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">File</th>
                        <th class="px-4 py-3">Updated</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($structures as $structure)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.organization-structures.show', $structure) }}" class="font-semibold hover:text-red-800">{{ $structure->title }}</a>
                                <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $structure->summary ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3">{{ $structure->department->name }}</td>
                            <td class="px-4 py-3"><span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $structure->status }}</span></td>
                            <td class="px-4 py-3">{{ strtoupper($structure->file_type) }}</td>
                            <td class="px-4 py-3">{{ $structure->updated_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.organization-structures.show', $structure) }}" class="text-sm font-medium text-red-700 hover:text-red-900">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada struktur organisasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $structures->links() }}</div>
    </div>
@endsection
