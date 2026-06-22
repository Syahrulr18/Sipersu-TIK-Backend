<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    /**
     * POST /auth/login
     * Support login dengan NIP atau email.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Tentukan field login: NIP atau email
        $field = $request->nip ? 'nip' : 'email';
        $value = $request->nip ?? $request->email;

        $user = User::where($field, $value)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'NIP/Email atau password salah.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun Anda telah dinonaktifkan.'], 403);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login berhasil',
            'token'   => $token,
            'user'    => new UserResource($user),
        ]);
    }

    /**
     * POST /auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
            'role' => 'dosen', // Default role
            'is_active' => true,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'token'   => $token,
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * POST /auth/logout
     */
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logout berhasil']);
    }

    /**
     * GET /auth/me
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'user' => new UserResource(auth()->user()),
        ]);
    }

    /**
     * PUT /auth/profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = auth()->user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * POST /auth/change-password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json(['message' => 'Password lama tidak cocok.'], 422);
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return response()->json(['message' => 'Password berhasil diubah']);
    }

    /**
     * POST /auth/profile/photo
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        // Hapus foto lama
        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
        }

        $path = $request->file('photo')->store("foto-profil", 'public');
        $user->update(['foto' => $path]);

        return response()->json([
            'message'  => 'Foto profil berhasil diupload',
            'foto_url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * POST /auth/profile/ttd
     */
    public function uploadTtd(Request $request): JsonResponse
    {
        $request->validate([
            'ttd' => 'required|image|mimes:png|max:2048',
        ]);

        $user = auth()->user();

        // Hanya role tertentu yang mungkin butuh ttd (misal Kajur), tapi kita biarkan terbuka untuk fleksibilitas
        
        // Hapus ttd lama
        if ($user->ttd) {
            Storage::disk('public')->delete($user->ttd);
        }

        $path = $request->file('ttd')->store("tanda-tangan", 'public');
        $user->update(['ttd' => $path]);

        return response()->json([
            'message'  => 'Tanda tangan berhasil diupload',
            'ttd_url'  => Storage::disk('public')->url($path),
        ]);
    }
}
