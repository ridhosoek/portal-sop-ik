@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold">Login</h1>
            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
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
        </div>
    </div>
@endsection

