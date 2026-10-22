<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamController extends Controller
{
        
    // Dashboard
    public function dashboard()
    {
        $userId = auth()->id();

        $stats = [
            'total' => Peminjaman::where('user_id', $userId)->count(),

            'dipinjam' => Peminjaman::where('user_id', $userId)
                ->where('status', 'dipinjam')
                ->count(),

            'menunggu' => Peminjaman::where('user_id', $userId)
                ->where('status', 'dipinjam')
                ->count(),
        ];

        return view('peminjam.dashboard', compact('stats'));
    }

// Melihat daftar/katalog alat yang tersedia
    public function katalogAlat()
    {
        $alats = Alat::with('kategori')->where('stok', '>', 0)->get();
        return view('peminjam.katalog', compact('alats'));
    }

  
    
public function ajukanPeminjaman(Request $request)
{
    $request->validate([
        'tgl_kembali_plan' => 'required|date|after:today',
        'alat_id' => 'required|array|min:1',
        'alat_id.*' => 'required|exists:alat,id',
        'jumlah' => 'required|array|min:1',
        'jumlah.*' => 'required|integer|min:1',
    ]);

    try {
        DB::transaction(function () use ($request) {

            $peminjaman = Peminjaman::create([
                'user_id' => auth()->id(),
                'tgl_pinjam' => now(),
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan',
            ]);

            foreach ($request->alat_id as $index => $alatId) {

                $jumlah = $request->jumlah[$index] ?? 0;

                $alat = Alat::where('id', $alatId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($alat->stok < $jumlah) {
                    throw new \Exception(
                        "Stok alat {$alat->nama_alat} tidak mencukupi."
                    );
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlah,
                ]);
            }
        });

        return redirect()
            ->route('peminjam.riwayat')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        throw $e;

    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}


    // Melihat riwayat peminjaman user yang sedang login
    public function riwayatPeminjaman()
    {
        $peminjamans = Peminjaman::with('detailPinjam.alat')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('peminjam.riwayat', compact('peminjamans'));
    }
    
    public function pengembalian()
{
    $peminjamans = peminjaman::with('detailPinjam.alat')
        ->where('user_id', auth()->id())
        ->where('status', 'dikembalikan')
        ->latest()
        ->get();

    return view('peminjam.pengembalian', compact('peminjamans'));
}
    
}