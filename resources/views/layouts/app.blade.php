<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Portal SOP & IK') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded bg-red-700 text-sm font-bold text-white">SOP</span>
                            <span>
                                <span class="block text-lg font-semibold">Portal SOP & IK</span>
                                <span class="block text-xs text-slate-500">Katalog dokumen internal</span>
                            </span>
                        </a>

                        @auth
                            <div class="flex items-center gap-3 text-sm">
                                <span class="hidden text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="rounded border border-slate-300 px-3 py-2 text-slate-700 hover:border-slate-400 hover:bg-slate-50">Logout</button>
                                </form>
                            </div>
                        @endauth
                    </div>

                    @auth
                        <nav class="flex flex-wrap gap-2 text-sm">
                            <a href="{{ route('dashboard') }}" class="rounded px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-red-700 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Beranda</a>
                            <a href="{{ route('documents.index') }}" class="rounded px-3 py-2 {{ request()->routeIs('documents.*') ? 'bg-red-700 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Katalog</a>
                            @if(auth()->user()->canReadGovernance())
                                <a href="{{ route('admin.dashboard') }}" class="rounded px-3 py-2 {{ request()->routeIs('admin.*') ? 'bg-red-700 text-white' : 'text-slate-700 hover:bg-slate-100' }}">Admin</a>
                            @endif
                        </nav>
                    @endauth
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @if(session('status'))
                    <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">Periksa input berikut:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>

