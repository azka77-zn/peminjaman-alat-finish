<?php

namespace App\Observers;

use App\Models\Alat;
use App\Models\LogAktivitas;

class AlatObserver
{
   
    public function created(Alat $alat): void
    {
        $userId = auth()->id() ?? $alat->user_id;

        // Lewati pencatatan jika tidak ada user, misalnya saat seeding.
        if ($userId === null) {
            return;
        }

        LogAktivitas::create([
            'user_id' => $userId,
            'aktivitas' => "Menambahkan data alat baru: '{$alat->nama_alat}' (Stok: {$alat->stok}, Kondisi: {$alat->status_kondisi}).",
        ]);
    }

    /**
     * Mencatat aktivitas ketika alat diperbarui.
     */
    public function updated(Alat $alat): void
    {
        $userId = auth()->id() ?? $alat->user_id;

        if ($userId === null) {
            return;
        }

        $changes = [];

        foreach ($alat->getChanges() as $key => $newValue) {
            if ($key !== 'updated_at') {
                $oldValue = $alat->getOriginal($key);

                $changes[] = "Kolom '{$key}' berubah dari '{$oldValue}' menjadi '{$newValue}'";
            }
        }

        $detailPerubahan = empty($changes)
            ? 'memperbarui data alat'
            : implode(', ', $changes);

        LogAktivitas::create([
            'user_id' => $userId,
            'aktivitas' => "Memperbarui alat '{$alat->nama_alat}': {$detailPerubahan}.",
        ]);
    }

    /**
     * Mencatat aktivitas ketika alat dihapus.
     */
    public function deleted(Alat $alat): void
    {
        $userId = auth()->id() ?? $alat->user_id;

        if ($userId === null) {
            return;
        }

        LogAktivitas::create([
            'user_id' => $userId,
            'aktivitas' => "Menghapus data alat: '{$alat->nama_alat}'.",
        ]);
    }
}