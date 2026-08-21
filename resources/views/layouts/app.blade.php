<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

        <!-- Theme: apply instantly to avoid flash before CSS loads -->
        <script>
            (function () {
                const stored = localStorage.getItem('theme') ?? '{{ auth()->user()->theme ?? "system" }}';
                const isDark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="h-screen overflow-hidden bg-gradient-to-br from-brand-100 via-brand-50 to-white dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 p-2.5 sm:p-5">
            <div class="h-full flex rounded-[28px] bg-white dark:bg-slate-800 shadow-sm ring-1 ring-black/5 dark:ring-white/10 overflow-hidden">
                @include('layouts.navigation')

                <div class="flex-1 flex flex-col overflow-hidden bg-brand-50/70 dark:bg-slate-900/70">
                    <!-- Top bar -->
                    <div class="h-16 shrink-0 flex items-center justify-between px-4 sm:px-6">
                        <button @click="sidebarOpen = true" class="sm:hidden text-gray-400 dark:text-slate-500">
                            <x-icon name="menu" class="w-6 h-6" />
                        </button>

                        <div class="hidden sm:block"></div>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('lorries.index') }}" wire:navigate title="Manage Lorries" class="{{ request()->routeIs('lorries.*') ? 'text-brand-600 dark:text-brand-400' : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300' }}">
                                <x-icon name="lorries" class="w-6 h-6" />
                            </a>

                            <livewire:theme-toggle />

                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center gap-2 pl-1.5 pr-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-full text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-800 shadow-sm hover:text-gray-900 dark:hover:text-slate-100 focus:outline-none transition ease-in-out duration-150">
                                        <span class="flex items-center justify-center w-7 h-7 rounded-full bg-brand-600 dark:bg-brand-500 text-white text-xs font-semibold shrink-0">
                                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                        </span>
                                        <span>{{ Auth::user()->name }}</span>
                                        <x-icon name="chevron-down" class="w-4 h-4" />
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
