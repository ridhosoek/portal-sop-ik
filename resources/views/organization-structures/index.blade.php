@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <h1 class="text-2xl font-semibold">Struktur Organisasi</h1>
                <p class="mt-1 text-sm text-slate-500">Informasi struktur dan pembaruan organisasi sesuai departemen.</p>
            </div>

            @if($canSelectDepartment)
                <form method="GET" action="{{ route('organization-structure.index') }}" class="flex flex-col gap-2 sm:flex-row">
                    <select name="department_id" class="rounded border border-slate-300 px-3 py-2 text-sm focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <button class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">Lihat</button>
                </form>
            @endif
        </div>

        @if($currentStructure)
            <section class="grid gap-5 xl:grid-cols-[1fr_0.55fr]">
                <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-red-700">{{ $currentStructure->department->name }}</p>
                            <h2 class="mt-1 text-2xl font-semibold">{{ $currentStructure->title }}</h2>
                        </div>
                        <span class="rounded bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                            Update {{ $currentStructure->updated_at->format('d M Y') }}
                        </span>
                    </div>

                    <dl class="mt-5 grid gap-4 border-y border-slate-100 py-4 text-sm sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Departemen</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $currentStructure->department->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Berlaku</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $currentStructure->effective_at?->format('d M Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Diunggah oleh</dt>
                            <dd class="mt-1 font-medium text-slate-800">{{ $currentStructure->uploader?->name ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 space-y-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Ringkasan</h3>
                            <p class="mt-2 leading-7 text-slate-600">{{ $currentStructure->summary ?: 'Belum ada ringkasan.' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Informasi Update</h3>
                            <p class="mt-2 leading-7 text-slate-600">{{ $currentStructure->update_note ?: 'Belum ada catatan update.' }}</p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('organization-structures.file', $currentStructure) }}" target="_blank" rel="noopener noreferrer" class="inline-flex rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">
                            Buka File Struktur
                        </a>
                    </div>
                </div>

                <aside class="rounded border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold">Riwayat Update</h3>
                    <div class="mt-4 space-y-3">
                        @foreach($history as $structure)
                            <a href="{{ route('organization-structures.file', $structure) }}" target="_blank" rel="noopener noreferrer" class="block rounded border border-slate-200 p-3 text-sm hover:border-red-200 hover:bg-red-50/40">
                                <span class="font-medium text-slate-900">{{ $structure->title }}</span>
                                <span class="mt-1 block text-xs text-slate-500">{{ $structure->updated_at->format('d M Y H:i') }}</span>
                                <span class="mt-2 line-clamp-2 text-slate-600">{{ $structure->update_note ?: $structure->summary ?: 'Tidak ada catatan.' }}</span>
                            </a>
                        @endforeach
                    </div>
                </aside>
            </section>

            <section class="rounded border border-slate-200 bg-white p-4 shadow-sm">
                @if($currentStructure->isImage())
                    <img src="{{ route('organization-structures.file', $currentStructure) }}" alt="{{ $currentStructure->title }}" class="max-h-[680px] w-full rounded border border-slate-200 bg-slate-50 object-contain">
                @else
                    <iframe src="{{ route('organization-structures.file', $currentStructure) }}" title="{{ $currentStructure->title }}" class="h-[620px] w-full rounded border border-slate-200 bg-slate-50"></iframe>
                @endif
            </section>
        @else
            <section class="rounded border border-dashed border-slate-300 bg-white px-5 py-10 text-center shadow-sm">
                <p class="text-sm font-medium text-slate-700">Struktur organisasi belum tersedia.</p>
                <p class="mt-1 text-sm text-slate-500">Admin dapat mengunggah file PDF atau gambar struktur organisasi untuk departemen terkait.</p>
            </section>
        @endif
    </div>
@endsection
