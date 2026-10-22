@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')
@section('header-title', 'Riwayat Peminjaman')

@section('content')

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-5 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">
            Riwayat Peminjaman
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Daftar seluruh peminjaman alat yang pernah Anda ajukan.
        </p>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">

            <thead class="bg-gray-50 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Alat</th>
                    <th class="px-6 py-4">Jumlah</th>
                    <th class="px-6 py-4">Tanggal Pinjam</th>
                    <th class="px-6 py-4">Rencana Kembali</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                             @php
                                 $no = 1;
                            @endphp

                @forelse($peminjamans as $peminjaman)

                    @foreach($peminjaman->detailPinjam as $detail)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-6 py-4">
                                {{ $no++ }}
                            </td>

                            
                            {{-- Nama Alat --}}
                            <td class="px-6 py-4 font-medium text-gray-800">
                                {{ $detail->alat->nama_alat ?? '-' }}
                            </td>

                            {{-- Jumlah --}}
                            <td class="px-6 py-4">
                                {{ $detail->jumlah }}
                            </td>

                            {{-- Tanggal Pinjam --}}
                            <td class="px-6 py-4">
                                {{ $peminjaman->tgl_pinjam?->format('d-m-Y') ?? '-' }}
                            </td>

                            {{-- Rencana Kembali --}}
                            <td class="px-6 py-4">
                                {{ $peminjaman->tgl_kembali_plan?->format('d-m-Y') ?? '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @if($peminjaman->status === 'diajukan')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">
                                        Diajukan
                                    </span>

                                @elseif($peminjaman->status === 'dipinjam')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                        Dipinjam
                                    </span>

                                @elseif($peminjaman->status === 'dikembalikan')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                        Dikembalikan
                                    </span>

                                @elseif($peminjaman->status === 'ditolak')
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                        Ditolak
                                    </span>

                                @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                        {{ ucfirst($peminjaman->status) }}
                                    </span>
                                @endif

                            </td>

                        </tr>

                    @endforeach

                @empty

                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">

                            <div class="text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.586l4.414 4.414A2 2 0 0119 9v10a2 2 0 01-2 2z"/>
                                </svg>

                                <p class="text-gray-500 font-medium">
                                    Belum ada riwayat peminjaman.
                                </p>

                                <p class="text-sm mt-1">
                                    Silakan ajukan peminjaman alat melalui katalog.
                                </p>
                            </div>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

</div>

@endsection