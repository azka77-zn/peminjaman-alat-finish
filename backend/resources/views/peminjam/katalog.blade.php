@extends('layouts.app')

@section('title', 'Katalog Alat')

@section('header-title', 'Katalog Alat')

@section('content')

<div class="space-y-6">

    {{-- Pesan sukses --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100">
                ✓
            </div>

            <div>
                <p class="text-sm font-semibold">Berhasil</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif


    {{-- Pesan error --}}
    @if(session('error'))
        <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-100">
                !
            </div>

            <div>
                <p class="text-sm font-semibold">Terjadi Kesalahan</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif


    {{-- Header halaman --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

        <div>
            <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-blue-600">
                📚 Koleksi Alat
            </div>

            <h2 class="text-2xl font-bold tracking-tight text-slate-800">
                Katalog Alat Tersedia
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Pilih alat yang ingin kamu pinjam.
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <p class="text-xs text-slate-400">
                Alat tersedia
            </p>

            <p class="mt-1 text-lg font-bold text-blue-600">
                {{ $alats->count() }}
            </p>
        </div>

    </div>


    {{-- Form Peminjaman --}}
    <form action="{{ route('peminjam.peminjaman.ajukan') }}" method="POST">
        @csrf

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Bagian tanggal --}}
            <div class="border-b border-slate-200 bg-slate-50/70 p-6">

                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            Rencana Peminjaman
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Tentukan tanggal kapan alat akan dikembalikan.
                        </p>
                    </div>

                    <div class="w-full md:w-72">

                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Rencana Tanggal Kembali
                        </label>

                        <input
                            type="date"
                            name="tgl_kembali_plan"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >

                    </div>

                </div>

            </div>


            {{-- Info --}}
            <div class="flex items-center gap-3 border-b border-blue-100 bg-blue-50 px-6 py-4">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    💡
                </div>

                <p class="text-sm text-blue-700">
                    Centang alat yang ingin dipinjam, lalu tentukan jumlahnya.
                </p>

            </div>


            {{-- Tabel alat --}}
            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="border-b border-slate-200 bg-slate-50">

                        <tr>

                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Pilih
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Gambar
                            </th>

                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Nama Alat
                            </th>

                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                                Kategori
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Stok
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                Jumlah
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse($alats as $index => $alat)

                            <tr class="group transition hover:bg-blue-50/40">

                                {{-- Checkbox --}}
                                <td class="px-5 py-4 text-center">

                                    <input
                                        type="checkbox"
                                        name="alat_id[]"
                                        value="{{ $alat->id }}"
                                        class="h-5 w-5 cursor-pointer rounded-md border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500"
                                    >

                                </td>


                                {{-- Gambar --}}
                                <td class="px-5 py-4 text-center">

                                    @if($alat->gambar)

                                        <div class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm transition group-hover:scale-105">

                                            <img
                                                src="{{ asset($alat->gambar) }}"
                                                alt="{{ $alat->nama_alat }}"
                                                class="h-full w-full object-cover"
                                            >

                                        </div>

                                    @else

                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-xl border border-slate-200 bg-slate-100">

                                            <span class="text-xs font-medium text-slate-400">
                                                Tidak ada
                                            </span>

                                        </div>

                                    @endif

                                </td>


                                {{-- Nama alat --}}
                                <td class="px-5 py-4">

                                    <p class="font-semibold text-slate-800">
                                        {{ $alat->nama_alat }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        ID Alat #{{ $alat->id }}
                                    </p>

                                </td>


                                {{-- Kategori --}}
                                <td class="px-5 py-4">

                                    <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600">
                                        {{ $alat->kategori->nama_kategori ?? '-' }}
                                    </span>

                                </td>


                                {{-- Stok --}}
                                <td class="px-5 py-4 text-center">

                                    <span class="inline-flex min-w-[40px] justify-center rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        {{ $alat->stok }}
                                    </span>

                                </td>


                                {{-- Jumlah --}}
                                <td class="px-5 py-4">

                                    <input
                                        type="number"
                                        name="jumlah[]"
                                        value="1"
                                        min="1"
                                        max="{{ $alat->stok }}"
                                        class="mx-auto block w-24 rounded-xl border border-slate-300 bg-white px-3 py-2 text-center text-sm font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    >

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-6 py-16 text-center">

                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                                        📦
                                    </div>

                                    <p class="mt-4 font-semibold text-slate-700">
                                        Tidak ada alat tersedia
                                    </p>

                                    <p class="mt-1 text-sm text-slate-400">
                                        Saat ini belum ada alat yang dapat dipinjam.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Footer --}}
            @if($alats->count() > 0)

                <div class="flex flex-col gap-4 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">

                    <div class="text-sm text-slate-500">

                        <span class="font-semibold text-slate-700">
                            Tips:
                        </span>

                        Pilih alat terlebih dahulu sebelum mengajukan peminjaman.

                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-xl"
                    >

                        <span>📦</span>
                        Ajukan Peminjaman

                    </button>

                </div>

            @endif

        </div>

    </form>

</div>

@endsection