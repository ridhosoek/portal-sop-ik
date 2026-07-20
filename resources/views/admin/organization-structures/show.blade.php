@extends('layouts.app')

@section('content')
    <div class="space-y-5">
        <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-red-700">{{ $structure->department->name }}</p>
                    <h1 class="mt-1 text-2xl font-semibold">{{ $structure->title }}</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ $structure->original_file_name }} - {{ number_format($structure->file_size / 1024, 1) }} KB</p>
                </div>
                <span class="rounded bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">{{ $structure->status }}</span>
            </div>

            <dl class="mt-5 grid gap-4 border-y border-slate-100 py-4 text-sm sm:grid-cols-4">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Departemen</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $structure->department->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Berlaku</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $structure->effective_at?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Upload</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $structure->uploader?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Updated</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $structure->updated_at->format('d M Y H:i') }}</dd>
                </div>
            </dl>

            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                <div>
                    <h2 class="font-semibold">Ringkasan</h2>
                    <p class="mt-2 leading-7 text-slate-600">{{ $structure->summary ?: '-' }}</p>
                </div>
                <div>
                    <h2 class="font-semibold">Informasi Update</h2>
                    <p class="mt-2 leading-7 text-slate-600">{{ $structure->update_note ?: '-' }}</p>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('organization-structures.file', $structure) }}" target="_blank" rel="noopener noreferrer" class="rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Buka File</a>
                @if(auth()->user()->canManageDocuments())
                    <a href="{{ route('admin.organization-structures.edit', $structure) }}" class="rounded border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Ubah</a>
                    @if($structure->status !== \App\Models\OrganizationStructure::STATUS_ARCHIVED)
                        <form method="POST" action="{{ route('admin.organization-structures.archive', $structure) }}">
                            @csrf
                            <button class="rounded border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Archive</button>
                        </form>
                    @endif
                @endif
            </div>
        </section>

        <section class="rounded border border-slate-200 bg-white p-4 shadow-sm">
            @if($structure->isImage())
                <img src="{{ route('organization-structures.file', $structure) }}" alt="{{ $structure->title }}" class="max-h-[680px] w-full rounded border border-slate-200 bg-slate-50 object-contain">
            @else
                <iframe src="{{ route('organization-structures.file', $structure) }}" title="{{ $structure->title }}" class="h-[620px] w-full rounded border border-slate-200 bg-slate-50"></iframe>
            @endif
        </section>
    </div>
@endsection
