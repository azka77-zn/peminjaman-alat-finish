@extends('layouts.app')

@section('title', 'Kelola Pengembalian - Panel Admin')
@section('header-title', 'Riwayat Pengembalian Alat')

@section('content')
@if(session('success'))
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm">
        <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">!</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50/60 px-5 py-6 md:px-7">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="mb-1 text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Administrasi alat</p>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat pengembalian</h2>
                <p class="mt-1 text-sm text-slate-500">Pantau pengembalian alat dan denda yang tercatat.</p>
            </div>
            <a href="{{ route('admin.pengembalian.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 hover:shadow-md">
                <span class="text-lg leading-none">+</span>
                Proses Pengembalian
            </a>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="rounded-lg border border-white/80 bg-white/70 px-3 py-2 text-sm text-slate-600 shadow-sm">
                <span class="font-bold text-slate-900">{{ $pengembalians->total() }}</span> pengembalian tercatat
            </div>
            <form method="GET" action="{{ route('admin.pengembalian.index') }}" class="flex w-full sm:w-auto">
                <label for="search-pengembalian" class="sr-only">Cari riwayat pengembalian</label>
                <input id="search-pengembalian" type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Cari peminjam atau kondisi..."
                    class="min-w-0 flex-1 rounded-l-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 sm:w-72">
                <button type="submit" class="rounded-r-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-900">
                    Cari
                </button>
                @if($search ?? false)
                    <a href="{{ route('admin.pengembalian.index') }}" class="ml-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3.5 font-bold">Peminjam</th>
                    <th class="px-5 py-3.5 font-bold">Tanggal kembali</th>
                    <th class="px-5 py-3.5 font-bold">Kondisi alat</th>
                    <th class="px-5 py-3.5 font-bold">Denda</th>
                    <th class="px-5 py-3.5 font-bold">Petugas</th>
                    <th class="px-5 py-3.5 text-right font-bold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                @forelse($pengembalians as $pg)
                    <tr class="transition hover:bg-blue-50/40">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                                    {{ strtoupper(substr($pg->peminjaman->user->name ?? '?', 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $pg->peminjaman->user->name ?? 'User Dihapus' }}</p>
                                    <p class="text-xs text-slate-400">Transaksi #{{ $pg->peminjaman_id }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-700">{{ $pg->tgl_kembali }}</td>
                        <td class="max-w-xs px-5 py-4">
                            <span class="inline-block rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $pg->kondisi_kembali }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">
                            @if($pg->denda > 0)
                                <span class="font-bold text-amber-700">Rp {{ number_format($pg->denda, 0, ',', '.') }}</span>
                            @else
                                <span class="font-medium text-emerald-600">Tidak ada denda</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">{{ $pg->petugas->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <form action="{{ route('admin.pengembalian.destroy', $pg->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data pengembalian ini?')">
                                @csrf
                            
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-400">—</div>
                            <p class="mt-3 font-semibold text-slate-700">Belum ada riwayat pengembalian</p>
                            <p class="mt-1 text-sm text-slate-500">Data pengembalian yang diproses akan tampil di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pengembalians->hasPages())
        <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
            {{ $pengembalians->links() }}
        </div>
    @endif
</div>
@endsection
