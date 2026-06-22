<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /users — List semua user (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('nama_lengkap')->paginate(15);

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'total'        => $users->total(),
            ],
        ]);
    }

    /**
     * GET /users/penanda-tangan — List kajur aktif sebagai penanda tangan
     */
    public function penandaTangan(): JsonResponse
    {
        $users = User::active()->byRole('kajur')->orderBy('nama_lengkap')->get();
        return response()->json(['data' => UserResource::collection($users)]);
    }

    /**
     * GET /users/verifikator — List verifikator aktif
     */
    public function verifikator(): JsonResponse
    {
        $users = User::active()->byRole('verifikator')->orderBy('nama_lengkap')->get();
        return response()->json(['data' => UserResource::collection($users)]);
    }

    /**
     * GET /users/dosen — Search dosen by nama
     */
    public function searchDosen(Request $request): JsonResponse
    {
        $users = User::active()
            ->byRole('dosen')
            ->when($request->search, fn($q) =>
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
            )
            ->orderBy('nama_lengkap')
            ->limit(20)
            ->get();

        return response()->json(['data' => UserResource::collection($users)]);
    }

    /**
     * POST /users — Create user (admin)
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            ...$request->validated(),
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat',
            'data'    => new UserResource($user),
        ], 201);
    }

    /**
     * PUT /users/{id} — Update user (admin)
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * PATCH /users/{id}/toggle — Toggle is_active (admin)
     */
    public function toggleAktif(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Jangan nonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat menonaktifkan akun sendiri.'], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return response()->json(['message' => "User berhasil {$status}"]);
    }

    /**
     * POST /users/{id}/reset-password — Reset password (admin)
     */
    public function resetPassword(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $defaultPassword = 'password';
        $user->update(['password' => Hash::make($defaultPassword)]);

        return response()->json([
            'message'          => 'Password berhasil direset',
            'password_default' => $defaultPassword,
        ]);
    }
    /**
     * DELETE /users/{id} — Delete user (admin)
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
