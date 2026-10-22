<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::latest()->get();
        return response()->json([
            'message' => 'Daftar pengguna berhasil diambil.',
            'data' => UserResource::collection($users)
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($request, $data) {
            $data['password'] = Hash::make($data['password']);

            if ($request->hasFile('foto_profile')) {
                $data['foto_profile'] = $request->file('foto_profile')->store('profiles', 'public');
            }

            return User::create($data);
        });

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

  public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        'role' => 'required|in:admin,petugas,peminjam,pelanggan',
        'no_hp' => 'nullable|string|max:20',
        'password' => 'nullable|string|min:6',
        'foto_profile' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $data = $request->only(['name', 'email', 'role', 'no_hp']);

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    if ($request->hasFile('foto_profile')) {
        $path = $request->file('foto_profile')->store('profiles', 'public');
        $data['foto_profile'] = $path;
    }

    $user->update($data);

    return redirect()->route('admin.user.index')
        ->with('success', 'Data pengguna berhasil diperbarui.');
}

    public function destroy(User $user): JsonResponse
    {
        try {
            DB::transaction(function () use ($user) {

                // Cek apakah user masih memiliki peminjaman aktif
                $peminjamanAktif = $user->peminjaman()
                    ->whereIn('status', ['dipinjam', 'telat'])
                    ->exists();

                if ($peminjamanAktif) {
                    throw new Exception(
                        'User tidak dapat dihapus karena masih memiliki peminjaman alat yang belum dikembalikan.'
                    );
                }

                // Hapus foto profile jika ada
                if ($user->foto_profile) {
                    Storage::disk('public')->delete($user->foto_profile);
                }

                // Hapus user
                $user->delete();
            });

            return response()->json([
                'message' => 'User berhasil dihapus.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}