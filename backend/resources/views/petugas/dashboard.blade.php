@extends('layouts.app')

@section('title', 'Dashboard Petugas - Sistem Peminjaman')
@section('header-title', 'Dashboard Petugas')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-emerald-700 px-6 py-7 text-white shadow-sm sm:px-8">
            <div class="relative z-10 max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-100">Ruang kerja petugas</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                    Selamat datang, {{ auth()->user()->name }}
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-emerald-50">
                    Pantau pengajuan, kelola pengembalian, dan pastikan setiap alat kembali tercatat dengan baik.
                </p>
            </div>
            <div class="absolute -right-10 -top-16 h-48 w-48 rounded-full border-[24px] border-emerald-500/40"></div>
            <div class="absolute -bottom-24 right-24 h-40 w-40 rounded-full border-[18px] border-emerald-800/30"></div>
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-amber-100 bg-amber-50 p-5">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-amber-800">Menunggu persetujuan</p>
                    <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">BARU</span>
                </div>
                <p class="mt-4 text-3xl font-bold text-amber-950">{{ $ringkasan['menunggu'] }}</p>
                <a href="{{ route('petugas.peminjaman.index') }}" class="mt-3 inline-flex text-xs font-semibold text-amber-800 hover:text-amber-950">
                    Lihat pengajuan <span class="ml-1" aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <div class="rounded-xl border border-sky-100 bg-sky-50 p-5">
                <p class="text-sm font-medium text-sky-800">Sedang dipinjam</p>
                <p class="mt-4 text-3xl font-bold text-sky-950">{{ $ringkasan['dipinjam'] }}</p>
                <a href="{{ route('petugas.pengembalian.index') }}" class="mt-3 inline-flex text-xs font-semibold text-sky-800 hover:text-sky-950">
                    Pantau pengembalian <span class="ml-1" aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <div class="rounded-xl border border-rose-100 bg-rose-50 p-5">
                <p class="text-sm font-medium text-rose-800">Terlambat</p>
                <p class="mt-4 text-3xl font-bold text-rose-950">{{ $ringkasan['terlambat'] }}</p>
                <p class="mt-3 text-xs font-medium text-rose-700">Peminjaman melewati batas waktu</p>
            </div>

            <div class="rounded-xl border border-violet-100 bg-violet-50 p-5">
                <p class="text-sm font-medium text-violet-800">Sudah kembali</p>
                <p class="mt-4 text-3xl font-bold text-violet-950">{{ $ringkasan['sudah_kembali'] }}</p>
                <p class="mt-3 text-xs font-medium text-violet-700">Peminjaman selesai diproses</p>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-800">Stok tersedia</p>
                <p class="mt-4 text-3xl font-bold text-emerald-950">{{ $ringkasan['stok_tersedia'] }}</p>
                <p class="mt-3 text-xs font-medium text-emerald-700">Total unit siap dipinjam</p>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Aktivitas peminjam</p>
                        <h2 class="mt-1 text-lg font-bold text-gray-900">Aktivitas peminjam terbaru</h2>
                    </div>
                    <a href="{{ route('petugas.peminjaman.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Lihat semua</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($aktivitasPeminjamTerbaru as $item)
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $item->user->name ?? 'User Dihapus' }}</p>
                                <p class="mt-1 text-xs text-gray-500">Aktivitas {{ $item->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{
                                $item->status === 'telat' ? 'bg-rose-100 text-rose-800' :
                                ($item->status === 'diajukan' ? 'bg-amber-100 text-amber-800' :
                                ($item->status === 'dipinjam' ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                {{ $item->status === 'diajukan' ? 'Menunggu' : ($item->status === 'dipinjam' ? 'Dipinjam' : ($item->status === 'telat' ? 'Terlambat' : 'Sudah kembali')) }}
                            </span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-gray-500">Belum ada aktivitas peminjam.</p>
                    @endforelse
                </div>
            </div>

        </section>
    </div>
@endsection
