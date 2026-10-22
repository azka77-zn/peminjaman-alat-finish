<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PetugasController extends Controller
{
    /**
     * Menampilkan ringkasan operasional petugas
     */
    public function dashboard()
    {
        $ringkasan = [
            'menunggu' => Peminjaman::where('status', 'diajukan')->count(),
            'dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
            'terlambat' => Peminjaman::where('status', 'telat')->count(),
            'sudah_kembali' => Peminjaman::whereIn('status', ['dikembalikan', 'selesai'])->count(),
            'stok_tersedia' => Alat::where('stok', '>', 0)->sum('stok'),
        ];

        $aktivitasPeminjamTerbaru = Peminjaman::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('petugas.dashboard', compact('ringkasan', 'aktivitasPeminjamTerbaru'));
    }

    /**
     * Menampilkan daftar pengajuan peminjaman
     */
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->where('status', 'diajukan')
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.peminjaman.index', compact('peminjamans', 'search'));
    }

    /**
     * Menyetujui peminjaman
     */
   public function setujuiPeminjaman($id)
{
    DB::beginTransaction();

    try {
        $peminjaman = Peminjaman::with('detailPinjam')
            ->lockForUpdate()
            ->findOrFail($id);

        // Pastikan peminjaman masih menunggu persetujuan
        if ($peminjaman->status !== 'diajukan') {
            throw new \Exception('Peminjaman ini sudah diproses.');
        }

        // Cek stok terlebih dahulu
        foreach ($peminjaman->detailPinjam as $detail) {
            $alat = Alat::lockForUpdate()
                ->findOrFail($detail->alat_id);

            if ($alat->stok < $detail->jumlah) {
                throw new \Exception(
                    "Stok alat '{$alat->nama_alat}' tidak mencukupi. Sisa stok: {$alat->stok}."
                );
            }
        }

        // Kalau semua stok cukup, baru ubah status
        $peminjaman->update([
            'status' => 'dipinjam'
        ]);

        // Kurangi stok
        foreach ($peminjaman->detailPinjam as $detail) {
            $alat = Alat::lockForUpdate()
                ->findOrFail($detail->alat_id);

            $alat->decrement('stok', $detail->jumlah);
        }

        DB::commit();

        return redirect()
            ->back()
            ->with('success', 'Peminjaman disetujui dan stok alat dikurangi.');

    } catch (\Exception $e) {
        DB::rollBack();

        return redirect()
            ->back()
            ->with('error', $e->getMessage());
    }
}

    /**
     * Menampilkan daftar pengembalian
     */
    public function indexPengembalian(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
            ->where('status', ['dipinjam', 'telat'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('petugas.pengembalian.index', compact('peminjamans', 'search'));
    }

    /**
     * Memproses pengembalian alat
     */
    public function prosesPengembalian(Request $request, $peminjamanId)
    {
        $request->validate([
            'kondisi_kembali' => 'required|string',
            'denda' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($peminjamanId);

            // Simpan data pengembalian
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tgl_kembali' => now(),
                'kondisi_kembali' => $request->kondisi_kembali,
                'denda' => $request->denda ?? 0,
                'petugas_id' => auth()->id(),
            ]);

            // Update status peminjaman
            $peminjaman->update(['status' => 'dikembalikan']);

            // Kembalikan stok alat
            foreach ($peminjaman->detailPinjam as $detail) {
                $alat = Alat::findOrFail($detail->alat_id);
                $alat->stok += $detail->jumlah;
                $alat->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengembalian berhasil dicatat dan stok dipulihkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan laporan sederhana (opsional)
     */
    public function indexLaporan(Request $request)
{
    $status = $request->input('status');
    $dari_tanggal = $request->input('dari_tanggal');
    $sampai_tanggal = $request->input('sampai_tanggal');

    $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
        ->when($status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($dari_tanggal && $sampai_tanggal, function ($query) use ($dari_tanggal, $sampai_tanggal) {
            return $query->whereBetween('tgl_pinjam', [$dari_tanggal, $sampai_tanggal]);
        })
        ->latest()
        ->get();

    return view('petugas.laporan.index', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal'));
}

// Menampilkan halaman khusus cetak (print preview)
public function cetakLaporan(Request $request)
{
    $status = $request->input('status');
    $dari_tanggal = $request->input('dari_tanggal');
    $sampai_tanggal = $request->input('sampai_tanggal');

    $laporans = Peminjaman::with(['user', 'detailPinjam.alat', 'pengembalian'])
        ->when($status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($dari_tanggal && $sampai_tanggal, function ($query) use ($dari_tanggal, $sampai_tanggal) {
            return $query->whereBetween('tgl_pinjam', [$dari_tanggal, $sampai_tanggal]);
        })
        ->latest()
        ->get();

    return view('petugas.laporan.cetak', compact('laporans', 'status', 'dari_tanggal', 'sampai_tanggal'));
}

}