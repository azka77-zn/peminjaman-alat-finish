@extends('layouts.app')

@section('title', 'Dashboard Peminjam')
@section('header-title', 'Dashboard Peminjam')

@section('content')

<div class="space-y-6">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 shadow-xl">

        {{-- Background decoration --}}
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 p-7 md:flex-row md:items-center md:justify-between md:p-9">

            {{-- Welcome --}}
            <div class="text-white">

                <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-100 backdrop-blur-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    AKUN AKTIF
                </div>

                <p class="text-sm font-medium text-blue-200">
                    Selamat datang kembali,
                </p>

                <h1 class="mt-1 text-3xl font-bold tracking-tight md:text-4xl">
                    {{ auth()->user()->name }}
                </h1>
            </div>

            {{-- Profile mini card --}}
            <div class="w-full md:w-auto">

                <div class="min-w-[230px] rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-md">

                    <div class="flex items-center gap-4">

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-2xl ring-1 ring-white/10">
                            👤
                        </div>

                        <div>
                            <p class="text-xs uppercase tracking-wider text-blue-200">
                                Role
                            </p>

                            <p class="mt-1 text-lg font-bold text-white">
                                {{ ucfirst(auth()->user()->role) }}
                            </p>
                        </div>

                    </div>

                    <div class="mt-4 border-t border-white/10 pt-4">

                        <div class="flex items-center justify-between text-sm">

                            <span class="text-slate-300">
                                Status
                            </span>

                            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-300">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                Aktif
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>


    {{-- STATISTIK --}}
    <div>

        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Ringkasan Peminjaman
                </h2>

                <p class="text-sm text-slate-500">
                    Informasi aktivitas peminjaman Anda.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            {{-- Total --}}
            <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">

                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total Peminjaman
                        </p>

                        <p class="mt-2 text-3xl font-bold text-slate-800">
                            {{ $stats['total'] ?? 0 }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Seluruh transaksi
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-xl transition group-hover:scale-110">
                        📦
                    </div>

                </div>

            </div>


            {{-- Dipinjam --}}
            <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">

                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Sedang Dipinjam
                        </p>

                        <p class="mt-2 text-3xl font-bold text-emerald-600">
                            {{ $stats['dipinjam'] ?? 0 }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Belum dikembalikan
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-xl transition group-hover:scale-110">
                        📤
                    </div>

                </div>

            </div>


            {{-- Menunggu --}}
            <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg">

                <div class="flex items-start justify-between">

                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Menunggu Pengembalian
                        </p>

                        <p class="mt-2 text-3xl font-bold text-amber-500">
                            {{ $stats['menunggu'] ?? 0 }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            Perlu diperhatikan
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-xl transition group-hover:scale-110">
                        ⏳
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- MENU UTAMA --}}
    <div>

        <div class="mb-4">
            <h2 class="text-lg font-bold text-slate-800">
                Menu Utama
            </h2>

            <p class="text-sm text-slate-500">
                Akses fitur peminjaman dengan cepat.
            </p>
        </div>


        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

            {{-- Katalog --}}
            <a href="{{ route('peminjam.katalog') }}"
               class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl">

                <div class="p-6">

                    <div class="flex items-center justify-between">

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl transition group-hover:scale-110">
                            📚
                        </div>

                        <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500">
                            →
                        </span>

                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-800">
                        Katalog Alat
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Lihat daftar alat yang tersedia dan ajukan peminjaman.
                    </p>

                    <div class="mt-5 text-sm font-semibold text-blue-600">
                        Buka katalog →
                    </div>

                </div>

            </a>


            {{-- Riwayat --}}
            <a href="{{ route('peminjam.riwayat') }}"
               class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl">

                <div class="p-6">

                    <div class="flex items-center justify-between">

                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-2xl transition group-hover:scale-110">
                            🧾
                        </div>

                        <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-500">
                            →
                        </span>

                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-800">
                        Riwayat Peminjaman
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Lihat semua transaksi peminjaman dan statusnya.
                    </p>

                    <div class="mt-5 text-sm font-semibold text-emerald-600">
                        Lihat riwayat →
                    </div>

                </div>

            </a>

        </div>

    </div>
    
        </div>

    </div>

</div>

@endsection