<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ $title ?? 'GuruHub' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-display {
            font-family: 'Outfit', sans-serif;
        }
    </style>

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="h-full antialiased text-slate-800 flex flex-col" x-data="{ sidebarOpen: false, profileOpen: false, notificationsOpen: false }">

    @auth
        @php
            $user = auth()->user();
            $unreadNotifications = $user ? $user->notifications()->where('is_read', false)->latest()->take(5)->get() : collect();
            $unreadCount = $user ? $user->notifications()->where('is_read', false)->count() : 0;
            $teacherProfile = $user && $user->role === 'TEACHER' ? $user->teacher : null;
            $userName = $user->role === 'ADMIN' ? 'Administrator' : ($teacherProfile ? $teacherProfile->name : $user->email);
        @endphp

        <div class="h-full flex overflow-hidden">
            <!-- Sidebar untuk Desktop -->
            <div class="hidden md:flex md:flex-shrink-0">
                <div class="flex flex-col w-64 border-r border-[#E2E8F0] bg-white">
                    <div class="flex items-center h-16 flex-shrink-0 px-6 border-b border-[#E2E8F0]">
                        <span class="text-xl font-bold tracking-tight text-[#4F46E5]">Guru<span class="text-white bg-[#0EA5E9] px-1.5 py-0.5 rounded ml-1 text-sm font-semibold inline-block align-middle transform -rotate-2">Hub</span></span>
                    </div>
                    <div class="flex-1 flex flex-col overflow-y-auto">
                        <nav class="flex-1 px-4 py-6 space-y-1 bg-white">
                            @if ($user->role === 'ADMIN')
                                <!-- Navigasi Admin -->
                                <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="layout-dashboard" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.dashboard') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.teachers') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.teachers') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="users" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.teachers') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Data Guru
                                </a>
                                <a href="{{ route('admin.students') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.students') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="graduation-cap" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.students') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Data Murid
                                </a>
                                <a href="{{ route('admin.classes') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.classes') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="school" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.classes') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Data Kelas
                                </a>
                                <a href="{{ route('admin.subjects') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.subjects') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="book-open" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.subjects') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Mata Pelajaran
                                </a>
                                <a href="{{ route('admin.schedules') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.schedules') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="calendar" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.schedules') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Jadwal Pelajaran
                                </a>
                                <a href="{{ route('admin.school-profile') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.school-profile') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="building" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.school-profile') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Profil Sekolah
                                </a>
                                <a href="{{ route('admin.school-years') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.school-years') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="calendar-days" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.school-years') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Tahun Pelajaran
                                </a>
                                <a href="{{ route('admin.perwalian') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.perwalian') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="heart-handshake" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.perwalian') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Penetapan Perwalian
                                </a>
                                <a href="{{ route('admin.account') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('admin.account') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="settings" class="mr-3 h-5 w-5 {{ request()->routeIs('admin.account') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Pengaturan Akun
                                </a>
                            @else
                                <!-- Navigasi Guru -->
                                <a href="{{ route('teacher.dashboard') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.dashboard') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="layout-dashboard" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.dashboard') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Dashboard
                                </a>
                                <a href="{{ route('teacher.attendance') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.attendance') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="check-square" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.attendance') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Absensi Kelas
                                </a>
                                <a href="{{ route('teacher.journals') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.journals') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="book-marked" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.journals') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Jurnal Harian
                                </a>
                                <a href="{{ route('teacher.scores') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.scores') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="file-text" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.scores') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Nilai TP Siswa
                                </a>
                                <a href="{{ route('teacher.agendas') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.agendas') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="list-todo" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.agendas') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Agenda Harian
                                </a>
                                <a href="{{ route('teacher.wali') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.wali') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="heart-handshake" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.wali') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Pembinaan Wali
                                </a>
                                <a href="{{ route('teacher.account') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('teacher.account') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <i data-lucide="settings" class="mr-3 h-5 w-5 {{ request()->routeIs('teacher.account') ? 'text-[#4F46E5]' : 'text-slate-400 group-hover:text-slate-500' }}"></i>
                                    Pengaturan Akun
                                </a>
                            @endif
                        </nav>
                        <div class="flex-shrink-0 flex border-t border-[#E2E8F0] p-4 bg-slate-50/50">
                            <a href="{{ route('logout') }}" class="flex items-center px-3 py-2 text-sm font-semibold rounded-xl text-rose-600 hover:bg-rose-50 w-full transition-all">
                                <i data-lucide="log-out" class="mr-3 h-5 w-5 text-rose-500"></i>
                                Keluar Aplikasi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bagian Konten Utama & Navbar Atas -->
            <div class="flex flex-col w-0 flex-1 overflow-hidden">
                <!-- Top Navbar -->
                <div class="relative z-10 flex-shrink-0 flex h-16 bg-white border-b border-[#E2E8F0] md:border-b-0 md:shadow-sm">
                    <button @click="sidebarOpen = true" class="px-4 border-r border-[#E2E8F0] text-slate-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#4F46E5] md:hidden">
                        <i data-lucide="menu" class="h-6 w-6"></i>
                    </button>
                    <div class="flex-1 px-6 flex justify-between">
                        <div class="flex-1 flex items-center">
                            <!-- Mobile Brand Logo -->
                            <div class="md:hidden">
                                <span class="text-xl font-bold tracking-tight text-[#4F46E5]">Guru<span class="text-white bg-[#0EA5E9] px-1.5 py-0.5 rounded ml-1 text-sm font-semibold inline-block align-middle transform -rotate-2">Hub</span></span>
                            </div>
                            <div class="hidden md:block">
                                <h1 class="text-lg font-semibold text-slate-900 font-display">
                                    {{ $user->role === 'ADMIN' ? 'Halaman Administrasi Sekolah' : 'Portal Mengajar Guru' }}
                                </h1>
                            </div>
                        </div>
                        <div class="ml-4 flex items-center md:ml-6 gap-4">
                            <!-- Dropdown Notifikasi -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" class="bg-white p-1.5 rounded-full text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none relative">
                                    <i data-lucide="bell" class="h-6 w-6"></i>
                                    @if ($unreadCount > 0)
                                        <span class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                                    @endif
                                </button>
                                <div x-show="open" x-transition class="origin-top-right absolute right-0 mt-2 w-80 rounded-2xl shadow-xl bg-white ring-1 ring-black/5 divide-y divide-slate-100 focus:outline-none" style="display: none;">
                                    <div class="px-4 py-3 flex justify-between items-center">
                                        <span class="text-sm font-semibold text-slate-900">Notifikasi</span>
                                        @if ($unreadCount > 0)
                                            <span class="bg-rose-50 text-rose-600 text-xs px-2 py-0.5 rounded-full font-medium">{{ $unreadCount }} Baru</span>
                                        @endif
                                    </div>
                                    <div class="max-h-60 overflow-y-auto">
                                        @forelse($unreadNotifications as $notif)
                                            <div class="px-4 py-3 hover:bg-slate-50 transition-all flex gap-3 text-xs">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    @if($notif->type === 'ATTENDANCE_OVERDUE')
                                                        <span class="p-1 bg-amber-50 text-amber-600 rounded-lg block"><i data-lucide="clock" class="h-4 w-4"></i></span>
                                                    @elseif($notif->type === 'SCORE_INCOMPLETE')
                                                        <span class="p-1 bg-red-50 text-red-600 rounded-lg block"><i data-lucide="file-warning" class="h-4 w-4"></i></span>
                                                    @else
                                                        <span class="p-1 bg-blue-50 text-blue-600 rounded-lg block"><i data-lucide="info" class="h-4 w-4"></i></span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-slate-800">{{ $notif->title }}</p>
                                                    <p class="text-slate-500 mt-0.5">{{ $notif->message }}</p>
                                                    <p class="text-[10px] text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-4 py-6 text-center text-slate-400">
                                                <i data-lucide="bell-off" class="h-8 w-8 mx-auto mb-2 text-slate-300"></i>
                                                <span class="text-xs">Tidak ada notifikasi baru</span>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- User Profile Info -->
                            <div class="flex items-center gap-3 border-l border-slate-200 pl-4">
                                <div class="hidden lg:block text-right">
                                    <p class="text-sm font-semibold text-slate-900 leading-none">{{ $userName }}</p>
                                    <p class="text-xs font-medium text-slate-500 mt-1 uppercase tracking-wider">{{ $user->role }}</p>
                                </div>
                                <div class="h-9 w-9 rounded-xl bg-[#4F46E5] text-white flex items-center justify-center font-bold text-sm tracking-wide shadow-sm shadow-[#4F46E5]/20">
                                    {{ substr($userName, 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Konten Halaman Sebenarnya -->
                <main class="flex-1 relative overflow-y-auto focus:outline-none bg-[#F8FAFC]">
                    <div class="py-8 px-4 sm:px-6 md:px-8 max-w-7xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <!-- Sidebar Mobile Panel (Drawer) -->
        <div x-show="sidebarOpen" class="fixed inset-0 flex z-40 md:hidden" style="display: none;">
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-600 bg-opacity-75" @click="sidebarOpen = false"></div>
            
            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                <div class="absolute top-0 right-0 -mr-12 pt-4">
                    <button @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <i data-lucide="x" class="h-6 w-6 text-white"></i>
                    </button>
                </div>
                <div class="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                    <div class="flex-shrink-0 flex items-center px-6">
                        <span class="text-xl font-bold tracking-tight text-[#4F46E5]">Guru<span class="text-white bg-[#0EA5E9] px-1.5 py-0.5 rounded ml-1 text-sm font-semibold inline-block align-middle transform -rotate-2">Hub</span></span>
                    </div>
                    <nav class="mt-8 px-4 space-y-1">
                        @if ($user->role === 'ADMIN')
                            <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="layout-dashboard" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Dashboard
                            </a>
                            <a href="{{ route('admin.teachers') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.teachers') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="users" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Data Guru
                            </a>
                            <a href="{{ route('admin.students') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.students') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="graduation-cap" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Data Murid
                            </a>
                            <a href="{{ route('admin.classes') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.classes') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="school" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Data Kelas
                            </a>
                            <a href="{{ route('admin.subjects') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.subjects') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="book-open" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Mata Pelajaran
                            </a>
                            <a href="{{ route('admin.schedules') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.schedules') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="calendar" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Jadwal Pelajaran
                            </a>
                            <a href="{{ route('admin.school-profile') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.school-profile') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="building" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Profil Sekolah
                            </a>
                            <a href="{{ route('admin.school-years') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.school-years') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="calendar-days" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Tahun Pelajaran
                            </a>
                            <a href="{{ route('admin.perwalian') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.perwalian') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="heart-handshake" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Penetapan Perwalian
                            </a>
                            <a href="{{ route('admin.account') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('admin.account') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="settings" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Pengaturan Akun
                            </a>
                        @else
                            <a href="{{ route('teacher.dashboard') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.dashboard') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="layout-dashboard" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Dashboard
                            </a>
                            <a href="{{ route('teacher.attendance') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.attendance') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="check-square" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Absensi Kelas
                            </a>
                            <a href="{{ route('teacher.journals') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.journals') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="book-marked" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Jurnal Harian
                            </a>
                            <a href="{{ route('teacher.scores') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.scores') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="file-text" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Nilai TP Siswa
                            </a>
                            <a href="{{ route('teacher.agendas') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.agendas') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="list-todo" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Agenda Harian
                            </a>
                            <a href="{{ route('teacher.wali') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.wali') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="heart-handshake" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Pembinaan Wali
                            </a>
                            <a href="{{ route('teacher.account') }}" class="group flex items-center px-3 py-2.5 text-base font-medium rounded-xl {{ request()->routeIs('teacher.account') ? 'bg-[#4F46E5]/10 text-[#4F46E5]' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i data-lucide="settings" class="mr-4 h-6 w-6 text-slate-500"></i>
                                Pengaturan Akun
                            </a>
                        @endif
                    </nav>
                </div>
                <div class="flex-shrink-0 flex border-t border-[#E2E8F0] p-4 bg-slate-50">
                    <a href="{{ route('logout') }}" class="flex items-center px-3 py-2 text-base font-semibold rounded-xl text-rose-600 hover:bg-rose-50 w-full transition-all">
                        <i data-lucide="log-out" class="mr-4 h-6 w-6 text-rose-500"></i>
                        Keluar Aplikasi
                    </a>
                </div>
            </div>
            <div class="flex-shrink-0 w-14">
                <!-- Dummy area to force sidebar to be smaller than screen width -->
            </div>
        </div>
    @else
        <!-- Renders directly for guests (Login page) -->
        {{ $slot }}
    @endauth

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        function initLucideIcons() {
            lucide.createIcons();
        }
        document.addEventListener("DOMContentLoaded", initLucideIcons);
        document.addEventListener("livewire:navigated", initLucideIcons);
        window.addEventListener("init-lucide", function() {
            setTimeout(initLucideIcons, 50);
        });
    </script>
</body>
</html>
