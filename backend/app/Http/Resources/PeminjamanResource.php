<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeminjamanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Info peminjam
            'peminjam' => $this->whenLoaded('user', fn() =>
                $this->user?->name
            ),

            // Tanggal pinjam & rencana kembali
            'tgl_pinjam' => $this->tgl_pinjam?->format('Y-m-d'),
            'tgl_kembali_plan' => $this->tgl_kembali_plan?->format('Y-m-d'),

            // Status peminjaman
            'status' => $this->status,

            // Detail item yang dipinjam
            'item_dipinjam' => $this->whenLoaded('detailPinjam', function () {
                return $this->detailPinjam->map(function ($detail) {
                    return [
                        'nama_alat' => $detail->alat?->nama_alat 
                            ?? 'Alat Dihapus/Tidak Ditemukan',
                        'jumlah' => (int) $detail->jumlah,
                    ];
                });
            }),

            // Info pengembalian
            'info_pengembalian' => $this->whenLoaded('pengembalian', function () {
                if (!$this->pengembalian) return null;

                return [
                    'tgl_kembali' => $this->pengembalian->tgl_kembali?->format('Y-m-d'),
                    'kondisi' => $this->pengembalian->kondisi_kembali,
                    'denda' => (int) $this->pengembalian->denda,
                    'petugas_penerima' => $this->pengembalian->petugas?->name ?? 'Sistem',
                ];
            }),
        ];
    }
}
