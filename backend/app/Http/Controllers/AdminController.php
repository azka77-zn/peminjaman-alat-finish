<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\DetailPinjam;
use App\Models\LogAktivitas;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function index()
    {
        $logs = LogAktivitas::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('logs'));
    }

   // CRUD ALAT: Menampilkan daftar alat
    public function indexAlat(Request $request)
    {
        $search = $request->input('search');

        $alats = Alat::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_alat', 'like', "%{$search}%")
                    ->orWhere('satuan_kondisi', 'like', "%{$search}%")
                    ->orWhereHas('kategori', function ($q) use ($search) {
                    $q->where('nama_kategori', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.alat.index', compact('alats', 'search'));
    }

    // 2. Menampilkan form tambah alat
    public function createAlat()
    {
        $kategoris = Kategori::all();
        return view('admin.alat.create', compact('kategoris'));
    }

    // 3. Menyimpan alat baru
    public function storeAlat(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Upload Gambar jika ada
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        Alat::create($data);

        return redirect()->route('admin.alat.index')
            ->with('success', 'Data alat berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit alat
    public function editAlat($id)
    {
        $alat = Alat::findOrFail($id);
        $kategoris = Kategori::all();

        return view('admin.alat.edit', compact('alat', 'kategoris'));
    }

    // 5. Memperbarui data alat
    public function updateAlat(Request $request, $id)
    {
        $alat = Alat::findOrFail($id);

        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'status_kondisi' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        // Handle Update Gambar jika ada file baru
        if ($request->hasFile('gambar')) {

            // Hapus gambar lama jika ada
            if ($alat->gambar && file_exists(public_path($alat->gambar))) {
            unlink(public_path($alat->gambar));
            }

            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/alat'), $filename);
            $data['gambar'] = 'storage/alat/' . $filename;
        }

        $alat->update($data);

        return redirect()->route('admin.alat.index')
            ->with('success', 'Data alat berhasil diperbarui.');
    }

    // 6. Menghapus data alat
   public function destroyAlat($id)
{
    $alat = Alat::findOrFail($id);

    // Cek apakah alat masih sedang dipinjam
    $sedangDipinjam = Peminjaman::whereIn('status', ['dipinjam', 'telat'])
        ->whereHas('detailPinjam', function ($query) use ($alat) {
            $query->where('alat_id', $alat->id);
        })
        ->exists();

    if ($sedangDipinjam) {
        return redirect()->route('admin.alat.index')
            ->with('error', 'Alat tidak dapat dihapus karena masih sedang dipinjam oleh pengguna.');
    }

    // Hapus file gambar fisik jika ada
    if ($alat->gambar && file_exists(public_path($alat->gambar))) {
        unlink(public_path($alat->gambar));
    }

    $alat->delete();

    return redirect()->route('admin.alat.index')
        ->with('success', 'Alat berhasil dihapus.');
}

    // CRUD User (Manajemen User Admin, Petugas, Peminjam)
    public function indexUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.user.index', compact('users', 'search'));
    }

    public function createUser()
    {
        return view('admin.user.create');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,petugas,peminjam'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);
        $data['password'] = bcrypt($data['password']);

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        User::create($data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        return view('admin.user.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,petugas,peminjam'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.user.index')->with('success', 'User berhasil diperbarui.');
    }


        public function destroyUser($id)
        {
            $user = User::findOrFail($id);
            
             if ($user->role === 'admin') {
                return back()->with(
                    'error',
                    'Admin tidak bisa dihapus.'
                );
            }

            // Cek apakah user masih memiliki alat yang sedang dipinjam
            $peminjamanAktif = $user->peminjaman()
                ->whereIn('status', ['dipinjam', 'telat'])
                ->exists();

            if ($peminjamanAktif) {
                return redirect()->route('admin.user.index')
                    ->with('error', 'User tidak dapat dihapus karena masih memiliki alat yang sedang dipinjam.');
            }

            $user->delete();

            return redirect()->route('admin.user.index')
                ->with('success', 'User berhasil dihapus.');
        }

    // 1. Menampilkan form indeks kategori dengan pencarian
    public function indexKategori(Request $request)
    {
        $search = $request->input('search');

        $kategoris = Kategori::when($search, function ($query, $search) {
        return $query->where('nama_kategori', 'like', "%{$search}%");
        })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('admin.kategori.index', compact('kategoris', 'search'));
    }

    // 2. Menampilkan form tambah kategori
    public function createKategori()
    {
        return view('admin.kategori.create');
    }

    // 3. Menyimpan kategori baru
    public function storeKategori(Request $request)
    {
        $request->validate([
        'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori',
        ]);

        Kategori::create([
        'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // 4. Menampilkan form edit kategori
    public function editKategori($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    // 5. Memperbarui kategori
    public function updateKategori(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
        'nama_kategori' => 'required|string|max:255|unique:kategori,nama_kategori,' . $id,
        ]);

        $kategori->update([
        'nama_kategori' => $request->nama_kategori,
        ]);

        return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    // 6. Menghapus kategori
    public function destroyKategori($id)
    {
        $kategori = Kategori::findOrFail($id);

    // Opsional: Cek apakah kategori masih dipakai oleh alat
    if ($kategori->alat()->count() > 0) {
        return redirect()->route('admin.kategori.index')
        ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data alat.');
    }

    $kategori->delete();

    return redirect()->route('admin.kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    // 1. Menampilkan daftar peminjaman
    public function indexPeminjaman(Request $request)
    {
        $search = $request->input('search');

        $peminjamans = Peminjaman::with(['user', 'detailPinjam.alat'])
            ->when($search, function ($query, $search) {
                return $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.peminjaman.index', compact('peminjamans', 'search'));
    }

    // 2. Menampilkan form tambah peminjaman
    public function createPeminjaman()
    {
        $users = User::where('role', 'peminjam')->get(); // Atau ambil semua user jika bebas
        $alats = Alat::where('stok', '>', 0)->get();
        return view('admin.peminjaman.create', compact('users', 'alats'));
    }

   // 3. Menyimpan data peminjaman baru
    public function storePeminjaman(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tgl_pinjam' => 'required|date',
            'tgl_kembali_plan' => 'required|date|after_or_equal:tgl_pinjam',
            'alat_id' => 'required|array',
            'alat_id.*' => 'exists:alat,id',
            'jumlah' => 'required|array',
            'jumlah.*' => 'integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Buat transaksi utama peminjaman
            $peminjaman = Peminjaman::create([
                'user_id' => $request->user_id,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_kembali_plan' => $request->tgl_kembali_plan,
                'status' => 'diajukan', // Status awal
            ]);

            // Simpan detail alat yang dipinjam
            foreach ($request->alat_id as $index => $alatId) {
                $jumlahPinjam = $request->jumlah[$index];

                $alat = Alat::findOrFail($alatId);

              

                // Validasi stok
                if ($alat->stok < $jumlahPinjam) {
                throw new \Exception("Stok alat '{$alat->nama_alat}' tidak mencukupi.");
                }

                DetailPinjam::create([
                    'peminjaman_id' => $peminjaman->id,
                    'alat_id' => $alatId,
                    'jumlah' => $jumlahPinjam,
                ]);
            }

            // Kurangi stok alat jika status langsung disetujui/dipinjam (opsional, atau dikurangi saat status berubah jadi 'dipinjam')

            DB::commit();

            return redirect()
                ->route('admin.peminjaman.index')
                ->with('success', 'Data peminjaman berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // 4. Memperbarui status peminjaman (Misal: dari diajukan -> dipinjam )
   
        
    public function updateStatusPeminjaman(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:diajukan,dipinjam,dikembalikan,selesai,telat',
        ]);

        $peminjaman = Peminjaman::with('detailPinjam.alat')->findOrFail($id);

        // Simpan status sebelum diubah
        //$statusLama = $peminjaman->status;

        // Ambil status baru dari request
       // $statusBaru = $request->status;

        // Jika barang dikembalikan, stok ditambahkan kembali
        //if (
           // in_array($statusLama, ['dipinjam', 'telat']) &&
            //in_array($statusBaru, ['dikembalikan', 'selesai'])
        // {
           // foreach ($peminjaman->detailPinjam as $detail) {
              //  $detail->alat->increment('stok', $detail->jumlah);
          //  }
//}

        $aturan = [
            'diajukan' => ['dipinjam'],
            'dipinjam' => ['selesai', 'telat'],
            'selesai' => ['telat'],
            'telat' => []
        ];

        $lama = $peminjaman->status;
        $baru = $request->status;
    
        if (!in_array($baru, $aturan[$lama] ?? [])) {
            return back()->with('error', 'Perubahan status tidak diizinkan!');
        }

          

        DB::beginTransaction();

        try {
            if ($lama == 'diajukan' && $baru == 'dipinjam') {
                foreach ($peminjaman->detailPinjam as $detail) {
                    $alat = $detail->alat;

                    if ($alat->stok < $detail->jumlah) {
                        throw new \Exception("Stok {$alat->nama_alat} tidak mencukupi!");
                    }

                    $alat->decrement('stok', $detail->jumlah);
                }
            }

            $peminjaman->update(['status' => $baru]);

            DB::commit();

            return back()->with('success', 'Status berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }




    // 5. Menghapus data peminjaman
    public function destroyPeminjaman($id)
    {
        $peminjaman = Peminjaman::with('detailPinjam')->findOrFail($id);

            if (in_array($peminjaman->status, ['dipinjam', 'telat'])) {
            return back()->with(
                'error','Peminjaman yang belum selesai tidak bisa dihapus.')
            ;
        }
        // Jika statusnya sedang dipinjam, kembalikan stok terlebih dahulu sebelum dihapus
        if ($peminjaman->status == 'dipinjam') {
            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->increment('stok', $detail->jumlah);
            }   
        }

        $peminjaman->delete();

        return redirect()
            ->route('admin.peminjaman.index')
            ->with('success', 'Data peminjaman berhasil dihapus.');
    }


   // 1. Menampilkan daftar pengembalian
// 1. Menampilkan daftar pengembalian
public function indexPengembalian(Request $request)
{
    $search = $request->input('search');

    // Data riwayat pengembalian
    $pengembalians = Pengembalian::with(['peminjaman.user', 'petugas'])
        ->when($search, function ($query, $search) {
            return $query->where('kondisi_kembali', 'like', "%{$search}%")
                ->orWhereHas('peminjaman.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    // Data peminjaman yang belum memiliki data pengembalian
    $peminjamans = Peminjaman::with('user')
        ->whereHas('user')
        ->whereNotIn('status', ['diajukan'])
        ->whereDoesntHave('pengembalian')
        ->get();

    return view('admin.pengembalian.index', compact(
        'pengembalians',
        'peminjamans',
        'search'
    ));
}

// 2. Menampilkan form tambah pengembalian
public function createPengembalian()
{
    // Ambil peminjaman yang sudah diproses dan belum memiliki data pengembalian
    $peminjamans = Peminjaman::with('user')
        ->whereHas('user')
        ->whereNotIn('status', ['diajukan'])
        ->whereDoesntHave('pengembalian')
        ->get();

    return view('admin.pengembalian.create', compact('peminjamans'));
}

// 3. Menyimpan data pengembalian baru dengan denda otomatis
public function storePengembalian(Request $request)
{
    $request->validate([
        'peminjaman_id' => 'required|exists:peminjaman,id',
        'tgl_kembali' => 'required|date',
        'kondisi_kembali' => 'required|string|max:255',
        'denda_tambahan' => 'nullable|integer|min:0', // Denda opsional jika ada kerusakan fisik
    ]);

    $peminjaman = Peminjaman::whereHas('user')
        ->whereNotIn('status', ['diajukan'])
        ->whereDoesntHave('pengembalian')
        ->findOrFail($request->peminjaman_id);

    DB::beginTransaction();

    try {
        $peminjaman->load('detailPinjam.alat');

        // Hitung keterlambatan otomatis (dalam hari)
        $tglPlan = \Carbon\Carbon::parse($peminjaman->tgl_kembali_plan);
        $tglAktual = \Carbon\Carbon::parse($request->tgl_kembali);

        $dendaOtomatis = 0;
        $tarifDendaPerHari = 5000; // Contoh tarif denda: Rp 5.000 / hari keterlambatan

        if ($tglAktual->greaterThan($tglPlan)) {
            $selisihHari = $tglPlan->diffInDays($tglAktual);
            $dendaOtomatis = $selisihHari * $tarifDendaPerHari;
        }

        // Total denda = denda keterlambatan + denda tambahan (misal karena rusak)
        $dendaTambahan = $request->denda_tambahan ?? 0;
        $totalDenda = $dendaOtomatis + $dendaTambahan;

        // Simpan data pengembalian
        Pengembalian::create([
            'peminjaman_id' => $request->peminjaman_id,
            'tgl_kembali' => $request->tgl_kembali,
            'kondisi_kembali' => $request->kondisi_kembali,
            'denda' => $totalDenda,
            'petugas_id' => auth()->id(),
        ]);

        // Ubah status peminjaman menjadi selesai
        $peminjaman->update(['status' => 'selesai']);

        // Kembalikan stok alat ke inventaris
        foreach ($peminjaman->detailPinjam as $detail) {
            $detail->alat->increment('stok', $detail->jumlah);
        }

        DB::commit();

        return redirect()->route('admin.pengembalian.index')
            ->with('success', 'Pengembalian berhasil diproses. Denda otomatis terhitung: Rp '
                . number_format($totalDenda, 0, ',', '.'));
    } catch (\Exception $e) {
        DB::rollBack();

        return back()->withInput()->with('error', $e->getMessage());
    }
}

// 4. Menghapus data pengembalian
public function destroyPengembalian($id)
{
    $pengembalian = Pengembalian::with('peminjaman.detailPinjam.alat')
        ->findOrFail($id);

    DB::beginTransaction();

    try {
        $peminjaman = $pengembalian->peminjaman;

        // Jika data pengembalian dihapus, kembalikan status peminjaman jadi 'dipinjam' dan kurangi kembali stoknya
        if ($peminjaman) {
            $peminjaman->update(['status' => 'dipinjam']);

            foreach ($peminjaman->detailPinjam as $detail) {
                $detail->alat->decrement('stok', $detail->jumlah);
            }
        }

        $pengembalian->delete();

        DB::commit();

        return redirect()->route('admin.pengembalian.index')
            ->with('success', 'Data pengembalian berhasil dihapus.');
    } catch (\Exception $e) {
        DB::rollBack();

        return back()->with('error', $e->getMessage());
    }
}
}