<?php

namespace App\Observers;

use App\Models\Pengembalian;
use App\Models\LogAktivitas;

class PengembalianObserver
{
    public function created(Pengembalian $pengembalian)
    {
        LogAktivitas::create([
            'user_id' => auth()->id() ?? $pengembalian->user_id,
            'aktivitas' => "Menambahkan data pengembalian (ID: {$pengembalian->id}) untuk peminjaman ID {$pengembalian->peminjaman_id}, kondisi: {$pengembalian->kondisi}, denda Rp {$pengembalian->denda}.",
        ]);
    }

    public function updated(Pengembalian $pengembalian)
    {
        $changes = [];
        foreach ($pengembalian->getChanges() as $key => $newValue) {
            if ($key != 'updated_at') {
                $oldValue = $pengembalian->getOriginal($key);
                $changes[] = "kolom '{$key}' berubah dari '{$oldValue}' menjadi '{$newValue}'";
            }
        }

        $detailPerubahan = empty($changes) 
            ? 'memperbarui data' 
            : implode(', ', $changes);

        LogAktivitas::create([
            'user_id' => auth()->id() ?? $pengembalian->user_id,
            'aktivitas' => "Memperbarui pengembalian ID {$pengembalian->id}: {$detailPerubahan}.",
        ]);
    }

    public function deleted(Pengembalian $pengembalian)
    {
        LogAktivitas::create([
            'user_id' => auth()->id() ?? $pengembalian->user_id,
            'aktivitas' => "Menghapus data pengembalian (ID: {$pengembalian->id}).",
        ]);
    }
}
