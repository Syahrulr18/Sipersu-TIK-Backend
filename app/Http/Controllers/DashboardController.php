<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private string $role;

    public function __construct()
    {
        $this->role = auth()->user()?->role ?? 'dosen';
    }

    /**
     * GET /dashboard/statistik
     */
    public function statistik(): JsonResponse
    {
        $user = auth()->user();
        $data = match ($user->role) {
            'administrator' => $this->statAdmin(),
            'verifikator'   => $this->statVerifikator($user),
            'kajur'         => $this->statKajur($user),
            default         => $this->statDosen($user),
        };

        return response()->json(['data' => $data]);
    }

    /**
     * GET /dashboard/chart — data bulanan 6 bulan terakhir
     */
    public function chart(): JsonResponse
    {
        $data = Surat::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->where('status', Surat::STATUS_TERBIT)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(fn($item) => [
                'bulan' => $item->bulan,
                'total' => $item->total,
            ]);

        return response()->json(['data' => $data]);
    }

    /**
     * GET /dashboard/recent — 5 surat terbaru sesuai role
     */
    public function recent(): JsonResponse
    {
        $user  = auth()->user();
        $query = Surat::with(['kodeHal', 'pembuatOleh'])
            ->orderByDesc('created_at')
            ->limit(5);

        match ($user->role) {
            'verifikator' => $query->where('verifikator_id', $user->id)
                                   ->whereIn('status', [Surat::STATUS_MENUNGGU_VERIFIKASI, Surat::STATUS_DIVERIFIKASI]),
            'kajur'       => $query->where('penanda_tangan_id', $user->id)
                                   ->whereIn('status', [Surat::STATUS_DIVERIFIKASI, Surat::STATUS_TERBIT]),
            'dosen'       => $query->whereHas('penerima', fn($q) => $q->where('user_id', $user->id))
                                   ->where('status', Surat::STATUS_TERBIT),
            default       => null,
        };

        $surat = $query->get()->map(fn($s) => [
            'id'          => $s->id,
            'nomor_surat' => $s->nomor_surat,
            'hal'         => $s->hal,
            'status'      => $s->status,
            'created_at'  => $s->created_at?->format('d M Y'),
            'kode_hal'    => $s->kodeHal?->kode,
        ]);

        return response()->json(['data' => $surat]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function statAdmin(): array
    {
        return [
            ['label' => 'Total Draf Aktif', 'value' => Surat::where('status', Surat::STATUS_DRAFT)->count()],
            ['label' => 'Menunggu Verifikasi', 'value' => Surat::where('status', Surat::STATUS_MENUNGGU_VERIFIKASI)->count()],
            ['label' => 'Perlu Revisi', 'value' => Surat::where('status', Surat::STATUS_DITOLAK)->count()],
            ['label' => 'Terbit', 'value' => Surat::where('status', Surat::STATUS_TERBIT)->count()],
        ];
    }

    private function statVerifikator(User $user): array
    {
        return [
            ['label' => 'Menunggu Verifikasi', 'value' => Surat::where('verifikator_id', $user->id)->where('status', Surat::STATUS_MENUNGGU_VERIFIKASI)->count()],
            ['label' => 'Telah Diverifikasi', 'value' => Surat::where('verifikator_id', $user->id)->where('status', Surat::STATUS_DIVERIFIKASI)->count()],
            ['label' => 'Perlu Perbaikan', 'value' => Surat::where('verifikator_id', $user->id)->where('status', Surat::STATUS_DITOLAK)->count()],
        ];
    }

    private function statKajur(User $user): array
    {
        return [
            ['label' => 'Menunggu TTD', 'value' => Surat::where('penanda_tangan_id', $user->id)->where('status', Surat::STATUS_DIVERIFIKASI)->count()],
            ['label' => 'Telah Ditandatangani', 'value' => Surat::where('penanda_tangan_id', $user->id)->where('status', Surat::STATUS_TERBIT)->count()],
        ];
    }

    private function statDosen(User $user): array
    {
        return [
            ['label' => 'Total Surat Diterima', 'value' => $user->suratDiterima()->where('status', Surat::STATUS_TERBIT)->count()],
        ];
    }
}
