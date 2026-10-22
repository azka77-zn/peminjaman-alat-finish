<?php

namespace App\Observers;

use App\Models\Peminjaman;
use App\Models\LogAktivitas;

class PeminjamanObserver
{
    public function created(Peminjaman $peminjaman)
    {
        LogAktivitas::create([
            'user_id' => auth()->id() ?? $peminjaman->user_id,
            'aktivitas' => "Menambahkan data peminjaman baru (ID: {$peminjaman->id}) dengan status: {$peminjaman->status}.",
        ]);
    }

    public function updated(Peminjaman $peminjaman)
    {
        $changes = [];
        foreach ($peminjaman->getChanges() as $key => $newValue) {
            if ($key != 'updated_at') {
                $oldValue = $peminjaman->getOriginal($key);
                $changes[] = "kolom '{$key}' berubah dari '{$oldValue}' menjadi '{$newValue}'";
            }
        }

        $detailPerubahan = empty($changes) 
            ? 'memperbarui data' 
            : implode(', ', $changes);

        LogAktivitas::create([
            'user_id' => auth()->id() ?? $peminjaman->user_id,
            'aktivitas' => "Memperbarui peminjaman ID {$peminjaman->id}: {$detailPerubahan}.",
        ]);
    }

    public function deleted(Peminjaman $peminjaman)
    {
        LogAktivitas::create([
            'user_id' => auth()->id() ?? $peminjaman->user_id,
            'aktivitas' => "Menghapus data peminjaman (ID: {$peminjaman->id}).",
        ]);
    }
}
