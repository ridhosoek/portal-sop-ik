@extends('layouts.app')

@section('content')
    <div class="grid min-h-[calc(100vh-180px)] items-center gap-8 lg:grid-cols-[1.15fr_0.85fr]">
        <section class="space-y-6">
            <img src="{{ asset('images/logo-indra-angkola.png') }}" alt="Indra Angkola" class="h-24 w-auto max-w-full object-contain">

            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-red-700">Portal Internal Indra Angkola Group</p>
                <h1 class="mt-3 max-w-3xl text-4xl font-semibold leading-tight text-slate-950 sm:text-5xl">
                    Informasi internal perusahaan dalam satu portal
                </h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                    Portal ini menjadi pusat informasi internal untuk mengakses SOP, Instruksi Kerja, struktur organisasi, dan informasi departemen yang berlaku di lingkungan Indra Angkola Group.
                </p>
            </div>

            <div class="grid gap-4 border-y border-slate-200 py-5 sm:grid-cols-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">SOP & IK</p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Panduan kerja resmi yang aktif dan terkontrol.</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">Struktur Organisasi</p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Informasi struktur terbaru sesuai departemen.</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">Informasi Internal</p>
                    <p class="mt-1 text-sm leading-6 text-slate-500">Akses informasi mengikuti role dan departemen.</p>
                </div>
            </div>

            <p class="max-w-2xl text-sm leading-6 text-slate-500">
                Gunakan akun internal yang telah terdaftar untuk masuk ke katalog dokumen, struktur organisasi, dan area pengelolaan sesuai hak akses masing-masing.
            </p>
        </section>

        <section class="rounded border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-14 w-16 items-center justify-center overflow-hidden rounded border border-slate-200 bg-white">
                    <img src="{{ asset('images/logo-indra-angkola.png') }}" alt="Indra Angkola" class="h-12 w-14 object-contain">
                </span>
                <div>
                    <h2 class="text-2xl font-semibold">Masuk Portal</h2>
                    <p class="mt-1 text-sm text-slate-500">Akses informasi internal perusahaan.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-100">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-red-700 focus:ring-red-700">
                    Ingat sesi
                </label>
                <button class="w-full rounded bg-red-700 px-4 py-2 font-medium text-white hover:bg-red-800">Masuk</button>
            </form>

            <p class="mt-5 text-sm leading-6 text-slate-500">
                Jika akun belum aktif atau akses belum sesuai, hubungi Super Admin.
            </p>
        </section>
    </div>
@endsection
