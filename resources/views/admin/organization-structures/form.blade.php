@extends('layouts.app')

@section('content')
    @php
        $isEdit = $structure !== null;
    @endphp

    <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Ubah Struktur Organisasi' : 'Tambah Struktur Organisasi' }}</h1>
        <p class="mt-1 text-sm text-slate-500">Gunakan file PDF atau gambar JPG, PNG, dan WebP maksimal 10 MB.</p>

        <form method="POST" action="{{ $isEdit ? route('admin.organization-structures.update', $structure) : route('admin.organization-structures.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <section class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Departemen</label>
                    <select name="department_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $structure?->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status</label>
                    <select name="status" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $structure?->status ?? 'published') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Judul</label>
                    <input name="title" value="{{ old('title', $structure?->title) }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Berlaku</label>
                    <input type="date" name="effective_at" value="{{ old('effective_at', $structure?->effective_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Ringkasan Struktur</label>
                    <textarea name="summary" rows="4" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">{{ old('summary', $structure?->summary) }}</textarea>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Informasi Update</label>
                    <textarea name="update_note" rows="4" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">{{ old('update_note', $structure?->update_note) }}</textarea>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">File Struktur</label>
                    <input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg,.webp,application/pdf,image/png,image/jpeg,image/webp" @if(! $isEdit) required @endif class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-red-700 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                    @if($isEdit)
                        <p class="mt-2 text-sm text-slate-500">
                            File saat ini:
                            <a href="{{ route('organization-structures.file', $structure) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-red-700 hover:text-red-900">{{ $structure->original_file_name }}</a>
                        </p>
                    @endif
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button class="rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Simpan Struktur</button>
                <a href="{{ $isEdit ? route('admin.organization-structures.show', $structure) : route('admin.organization-structures.index') }}" class="rounded border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
