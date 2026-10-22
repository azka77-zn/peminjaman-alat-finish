@extends('layouts.app')

@section('title', 'Pengembalian Alat')
@section('header-title', 'Pengembalian Alat')

@section('content')

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">
        Pengembalian Alat
    </h2>
    <p class="text-sm text-gray-500 mt-1">
        Daftar alat yang sedang kamu pinjam.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-lg font-bold text-gray-800">
            Daftar Peminjaman Aktif
        </h3>
        <p class="text-sm text-gray-500 mt-1">
            Pilih peminjaman yang ingin dikembalikan.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">

            <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Nama Alat</th>
                    <th class="px-6 py-4">Jumlah</th>
                    <th class="px-6 py-4">Tanggal Pinjam</th>
                    <th class="px-6 py-4">Rencana Kembali</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($peminjamans as $peminjaman)

                    @foreach($peminjaman->detailPinjam as $detail)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-6 py-4">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $detail->alat->nama_alat ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $detail->jumlah }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') ?? '-' }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                    Dipinjam
                                </span>
                            </td>

                    

                    @endforeach

                @empty

                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">

                            <p class="text-gray-500 font-medium">
                                Tidak ada alat yang sedang dipinjam.
                            </p>

                            <p class="text-sm text-gray-400 mt-1">
                                Semua alat sudah dikembalikan atau belum ada peminjaman aktif.
                            </p>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

</div>

@endsection