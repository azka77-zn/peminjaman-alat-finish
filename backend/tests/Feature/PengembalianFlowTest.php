<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\DetailPinjam;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengembalianFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_process_pengembalian_and_store_history(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $peminjam = User::factory()->create([
            'role' => 'peminjam',
        ]);

        $kategori = Kategori::create([
            'nama_kategori' => 'Jaringan',
        ]);

        $alat = Alat::create([
            'kategori_id' => $kategori->id,
            'nama_alat' => 'Router',
            'stok' => 2,
            'status_kondisi' => 'Baik',
            'deskripsi' => 'Router untuk ujian',
        ]);

        $peminjaman = Peminjaman::create([
            'user_id' => $peminjam->id,
            'tgl_pinjam' => '2026-08-01',
            'tgl_kembali_plan' => '2026-08-05',
            'status' => 'dipinjam',
        ]);

        DetailPinjam::create([
            'peminjaman_id' => $peminjaman->id,
            'alat_id' => $alat->id,
            'jumlah' => 1,
        ]);

        $response = $this->actingAs($admin)
            ->post('/admin/pengembalian', [
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => '2026-08-04',
                'kondisi_kembali' => 'Baik',
                'denda' => 0,
            ]);

        $response->assertRedirect('/admin/pengembalian/riwayat');

        $this->assertDatabaseHas('pengembalian', [
            'peminjaman_id' => $peminjaman->id,
            'kondisi_kembali' => 'Baik',
            'denda' => 0,
            'petugas_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('peminjaman', [
            'id' => $peminjaman->id,
            'status' => 'selesai',
        ]);
    }
}
