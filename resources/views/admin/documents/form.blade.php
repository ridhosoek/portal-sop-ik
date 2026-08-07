@extends('layouts.app')

@section('content')
    @php
        $isEdit = $document !== null;
    @endphp

    <div class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Ubah Dokumen' : 'Tambah Dokumen' }}</h1>
        <form method="POST" action="{{ $isEdit ? route('admin.documents.update', $document) : route('admin.documents.store') }}" class="mt-6 space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <section class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nomor Dokumen</label>
                    <input name="document_number" value="{{ old('document_number', $document?->document_number) }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Judul</label>
                    <input name="title" value="{{ old('title', $document?->title) }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jenis</label>
                    <select name="type_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" @selected(old('type_id', $document?->type_id) == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Departemen</label>
                    <select name="department_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $document?->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kategori</label>
                    <select name="category_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $document?->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Ringkasan</label>
                    <textarea name="summary" rows="4" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">{{ old('summary', $document?->summary) }}</textarea>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Versi</label>
                    <input name="version" value="{{ old('version', $version?->version) }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">URL Dokumen</label>
                    <input name="url" value="{{ old('url', $version?->url) }}" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Efektif</label>
                    <input type="date" name="effective_at" value="{{ old('effective_at', $version?->effective_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Review</label>
                    <input type="date" name="review_at" value="{{ old('review_at', $version?->review_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Expired</label>
                    <input type="date" name="expired_at" value="{{ old('expired_at', $version?->expired_at?->format('Y-m-d')) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status Awal</label>
                    <select name="status" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" @if($isEdit) disabled @endif>
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                        <option value="published" @selected(old('status') === 'published')>Published</option>
                    </select>
                    @if($isEdit)
                        <input type="hidden" name="status" value="{{ $document->status }}">
                    @endif
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Ringkasan Perubahan</label>
                    <textarea name="change_summary" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">{{ old('change_summary', $version?->change_summary) }}</textarea>
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button class="rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Simpan</button>
                <a href="{{ $isEdit ? route('admin.documents.show', $document) : route('admin.documents.index') }}" class="rounded border border-slate-300 px-4 py-2 font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection

