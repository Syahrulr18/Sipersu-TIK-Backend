<?php

namespace App\Http\Controllers;

use App\Models\KodeHal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KodeHalController extends Controller
{
    /**
     * GET /kode-hal — List semua kode hal aktif
     */
    public function index(Request $request): JsonResponse
    {
        $query = KodeHal::active();

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kode', 'like', "%{$request->search}%")
                  ->orWhere('nama', 'like', "%{$request->search}%");
            });
        }

        $data = $query->orderBy('kategori')->orderBy('kode')->get();

        // Group by kategori
        $grouped = $data->groupBy('kategori')->map(fn($items, $kategori) => [
            'kategori' => $kategori,
            'items'    => $items->map(fn($k) => [
                'id'       => $k->id,
                'kode'     => $k->kode,
                'nama'     => $k->nama,
                'kategori' => $k->kategori,
            ])->values(),
        ])->values();

        return response()->json(['data' => $grouped]);
    }

    /**
     * POST /kode-hal — Tambah kode hal (admin)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'kode'      => 'required|string|unique:kode_hal,kode',
            'nama'      => 'required|string',
            'kategori'  => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $kodeHal = KodeHal::create([
            ...$request->only(['kode', 'nama', 'kategori', 'deskripsi']),
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Kode hal berhasil ditambahkan', 'data' => $kodeHal], 201);
    }

    /**
     * PUT /kode-hal/{id} — Update kode hal (admin)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $kodeHal = KodeHal::findOrFail($id);
        $request->validate([
            'kode'      => "required|string|unique:kode_hal,kode,{$id}",
            'nama'      => 'required|string',
            'kategori'  => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $kodeHal->update($request->only(['kode', 'nama', 'kategori', 'deskripsi']));

        return response()->json(['message' => 'Kode hal berhasil diperbarui', 'data' => $kodeHal->fresh()]);
    }

    /**
     * DELETE /kode-hal/{id} — Nonaktifkan kode hal (soft delete via is_active)
     */
    public function destroy(int $id): JsonResponse
    {
        $kodeHal = KodeHal::findOrFail($id);
        $kodeHal->update(['is_active' => false]);

        return response()->json(['message' => 'Kode hal berhasil dinonaktifkan']);
    }
}
