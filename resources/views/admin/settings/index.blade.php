@extends('layouts.app')

@section('content')
    <section class="rounded border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-semibold">Konfigurasi</h1>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 max-w-3xl space-y-5">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700">Domain URL Diizinkan</label>
                <textarea name="allowed_document_hosts" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('allowed_document_hosts', $settings['allowed_document_hosts']) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Host Intranet HTTP Diizinkan</label>
                <textarea name="allowed_intranet_hosts" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm">{{ old('allowed_intranet_hosts', $settings['allowed_intranet_hosts']) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Timeout Idle Admin (menit)</label>
                <input type="number" name="admin_idle_timeout_minutes" value="{{ old('admin_idle_timeout_minutes', $settings['admin_idle_timeout_minutes']) }}" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded bg-red-700 px-4 py-2 font-medium text-white">Simpan Konfigurasi</button>
        </form>
    </section>
@endsection

