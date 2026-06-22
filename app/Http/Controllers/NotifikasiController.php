<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotifikasiResource;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;

class NotifikasiController extends Controller
{
    /**
     * GET /notifikasi
     */
    public function index(): JsonResponse
    {
        $notifikasi = Notifikasi::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => NotifikasiResource::collection($notifikasi),
        ]);
    }

    /**
     * GET /notifikasi/unread-count
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * PATCH /notifikasi/{id}/baca
     */
    public function baca(int $id): JsonResponse
    {
        $notif = Notifikasi::where('user_id', auth()->id())->findOrFail($id);
        $notif->update(['is_read' => true]);

        return response()->json(['message' => 'Notifikasi ditandai telah dibaca']);
    }

    /**
     * PATCH /notifikasi/baca-semua
     */
    public function bacaSemua(): JsonResponse
    {
        Notifikasi::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai telah dibaca']);
    }

    /**
     * DELETE /notifikasi/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $notif = Notifikasi::where('user_id', auth()->id())->findOrFail($id);
        $notif->delete();

        return response()->json(['message' => 'Notifikasi berhasil dihapus']);
    }

    /**
     * DELETE /notifikasi/hapus-semua
     */
    public function hapusSemua(): JsonResponse
    {
        Notifikasi::where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Semua notifikasi berhasil dihapus']);
    }
}
