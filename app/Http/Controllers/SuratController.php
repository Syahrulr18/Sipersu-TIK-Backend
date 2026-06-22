<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use App\Helpers\NomorSuratGenerator;
use App\Http\Requests\StoreSuratRequest;
use App\Http\Requests\UpdateKontenRequest;
use App\Http\Requests\VerifikasiRequest;
use App\Http\Resources\SuratDetailResource;
use App\Http\Resources\SuratListResource;
use App\Models\KodeHal;
use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Models\SuratLog;
use App\Services\NotifikasiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
{
    /**
     * GET /surat — Daftar surat berdasarkan role
     */
    public function index(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $query = Surat::with(['kodeHal', 'pembuatOleh'])
            ->withCount('lampiran');

        // Filter berdasarkan role
        match ($user->role) {
            'verifikator' => $query->where('verifikator_id', $user->id),
            'kajur'       => $query->where('penanda_tangan_id', $user->id),
            'dosen'       => $query->whereHas('penerima', fn($q) => $q->where('user_id', $user->id))
                                   ->where('status', Surat::STATUS_TERBIT),
            default       => null, // admin lihat semua
        };

        // Filter tambahan via query param
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('hal', 'like', "%{$request->search}%")
                  ->orWhere('nomor_surat', 'like', "%{$request->search}%")
                  ->orWhere('ringkasan', 'like', "%{$request->search}%");
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'nomor_surat') {
            $query->orderByRaw('CASE WHEN nomor_surat IS NULL THEN 1 ELSE 0 END')
                  ->orderBy('nomor_surat', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $surat = $query->paginate(15);


        return response()->json([
            'data' => SuratListResource::collection($surat),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'total'        => $surat->total(),
            ],
        ]);
    }

    /**
     * POST /surat — Buat surat baru (administrator)
     */
    public function store(StoreSuratRequest $request): JsonResponse
    {
        $kodeHal = KodeHal::where('kode', $request->kode_hal)->firstOrFail();

        $surat = Surat::create([
            'penanda_tangan_id' => $request->penanda_tangan_id,
            'verifikator_id'    => $request->verifikator_id,
            'kode_hal_id'       => $kodeHal->id,
            'hal'               => $request->hal,
            'ringkasan'         => $request->ringkasan,
            'status'            => Surat::STATUS_DRAFT,
            'dibuat_oleh'       => auth()->id(),
        ]);

        // Attach penerima (support tujuan_dosen_id single atau array)
        $penerimaIds = is_array($request->tujuan_dosen_id)
            ? $request->tujuan_dosen_id
            : [$request->tujuan_dosen_id];
        $surat->penerima()->attach($penerimaIds);

        // Upload lampiran jika ada
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $data = FileHelper::uploadLampiran($file, $surat->id);
                SuratLampiran::create($data);
            }
        }

        // Log pembuatan
        SuratLog::create([
            'surat_id'       => $surat->id,
            'user_id'        => auth()->id(),
            'status_sebelum' => null,
            'status_sesudah' => Surat::STATUS_DRAFT,
            'catatan'        => 'Surat dibuat',
            'created_at'     => now(),
        ]);

        return response()->json([
            'message' => 'Surat berhasil dibuat',
            'id'      => $surat->id,
        ], 201);
    }

    /**
     * GET /surat/{id} — Detail surat
     */
    public function show(int $id): JsonResponse
    {
        $user  = auth()->user();
        $surat = Surat::with([
            'kodeHal', 'penandaTangan', 'verifikator',
            'pembuatOleh', 'penerima', 'lampiran',
            'log.user', 'kontenPenerima'
        ])->findOrFail($id);

        // Akses control: dosen hanya bisa lihat surat yang ditujukan kepadanya
        if ($user && strtolower($user->role) === 'dosen') {
            $isPenerima = $surat->penerima->contains('id', $user->id);
            if (!$isPenerima) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke surat ini.'], 403);
            }
        }

        return response()->json(['data' => new SuratDetailResource($surat)]);
    }

    /**
     * PATCH /surat/{id}/konten — Update konten HTML (draft only)
     */
    public function updateKonten(UpdateKontenRequest $request, int $id): JsonResponse
    {
        $surat = Surat::findOrFail($id);

        if ($surat->status !== Surat::STATUS_DRAFT && $surat->status !== Surat::STATUS_DITOLAK) {
            return response()->json(['message' => 'Konten hanya dapat diubah pada status draft atau ditolak.'], 422);
        }

        // Update global konten_html jika dikirim
        if ($request->has('konten_html')) {
            $surat->update(['konten_html' => $request->konten_html]);
        }

        // Update konten spesifik per penerima jika dikirim
        if ($request->has('konten_penerima') && is_array($request->konten_penerima)) {
            // Hapus konten penerima lama agar bisa diganti yang baru (jika ada)
            $surat->kontenPenerima()->delete();

            $kontenPenerimaData = array_map(function ($kp) use ($surat) {
                return [
                    'surat_id' => $surat->id,
                    'penerima_user_id' => $kp['penerima_id'],
                    'konten_html' => $kp['konten_html'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $request->konten_penerima);

            \App\Models\SuratKontenPenerima::insert($kontenPenerimaData);
        }

        return response()->json(['message' => 'Konten surat berhasil diperbarui']);
    }

    /**
     * DELETE /surat/{id} — Hapus surat dan sesuaikan nomor surat setelahnya
     */
    public function destroy(int $id): JsonResponse
    {
        $surat = Surat::with(['lampiran', 'kodeHal'])->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($surat) {
            $nomorUrutDihapus = $surat->nomor_urut;
            
            // Hapus file lampiran
            foreach ($surat->lampiran as $lamp) {
                FileHelper::deleteLampiran($lamp->path);
            }

            $surat->delete();
        });

        return response()->json(['message' => 'Surat berhasil dihapus']);
    }

    /**
     * POST /surat/{id}/submit — Submit ke verifikator
     */
    public function submit(int $id): JsonResponse
    {
        $surat = Surat::findOrFail($id);

        if (!in_array($surat->status, [Surat::STATUS_DRAFT, Surat::STATUS_DITOLAK])) {
            return response()->json(['message' => 'Hanya surat draft atau ditolak yang dapat disubmit.'], 422);
        }

        if (empty($surat->konten_html)) {
            return response()->json(['message' => 'Konten surat belum diisi. Silakan isi konten terlebih dahulu.'], 422);
        }

        $statusLama = $surat->status;
        $surat->update([
            'status'            => Surat::STATUS_MENUNGGU_VERIFIKASI,
            'catatan_penolakan' => null,
        ]);

        SuratLog::create([
            'surat_id'       => $surat->id,
            'user_id'        => auth()->id(),
            'status_sebelum' => $statusLama,
            'status_sesudah' => Surat::STATUS_MENUNGGU_VERIFIKASI,
            'catatan'        => 'Surat dikirim ke verifikator',
            'created_at'     => now(),
        ]);

        NotifikasiService::kirimKeVerifikator($surat->load('pembuatOleh'));

        return response()->json(['message' => 'Surat berhasil dikirim ke verifikator']);
    }

    /**
     * POST /surat/{id}/verifikasi — Verifikasi (approve/reject)
     */
    public function verifikasi(VerifikasiRequest $request, int $id): JsonResponse
    {
        $user  = auth()->user();
        $surat = Surat::findOrFail($id);

        if ($surat->verifikator_id !== $user->id) {
            return response()->json(['message' => 'Anda bukan verifikator surat ini.'], 403);
        }

        if ($surat->status !== Surat::STATUS_MENUNGGU_VERIFIKASI) {
            return response()->json(['message' => 'Surat tidak dalam status menunggu verifikasi.'], 422);
        }

        $statusBaru   = $request->aksi === 'setuju' ? Surat::STATUS_DIVERIFIKASI : Surat::STATUS_DITOLAK;
        $statusLama   = $surat->status;

        $surat->update([
            'status'            => $statusBaru,
            'catatan_penolakan' => $request->aksi === 'tolak' ? $request->catatan : null,
        ]);

        SuratLog::create([
            'surat_id'       => $surat->id,
            'user_id'        => $user->id,
            'status_sebelum' => $statusLama,
            'status_sesudah' => $statusBaru,
            'catatan'        => $request->catatan,
            'created_at'     => now(),
        ]);

        if ($request->aksi === 'setuju') {
            NotifikasiService::kirimKeKajur($surat->load('kodeHal'));
            NotifikasiService::kirimKePembuat(
                $surat, 'Surat Diverifikasi',
                "Surat \"{$surat->hal}\" telah diverifikasi dan menunggu tanda tangan Kajur.", 'verifikasi'
            );
        } else {
            NotifikasiService::kirimKePembuat(
                $surat, 'Surat Ditolak',
                "Surat \"{$surat->hal}\" ditolak. Alasan: {$request->catatan}", 'tolak'
            );
        }

        $msg = $request->aksi === 'setuju' ? 'Surat berhasil diverifikasi' : 'Surat ditolak';
        return response()->json(['message' => $msg]);
    }

    /**
     * POST /surat/{id}/tandatangan — TTD & terbitkan
     */
    public function tandatangan(Request $request, int $id): JsonResponse
    {
        $user  = auth()->user();
        $surat = Surat::with(['kodeHal', 'pembuatOleh', 'penerima'])->findOrFail($id);

        if ($surat->penanda_tangan_id !== $user->id) {
            return response()->json(['message' => 'Anda bukan penanda tangan surat ini.'], 403);
        }

        if ($surat->status !== Surat::STATUS_DIVERIFIKASI) {
            return response()->json(['message' => 'Surat belum diverifikasi.'], 422);
        }

        // Jika Kajur memilih aksi tolak
        if ($request->aksi === 'tolak') {
            $request->validate(['catatan' => 'required|string']);
            
            $surat->update([
                'status' => Surat::STATUS_DITOLAK,
                'catatan_penolakan' => $request->catatan,
            ]);

            SuratLog::create([
                'surat_id'       => $surat->id,
                'user_id'        => $user->id,
                'status_sebelum' => Surat::STATUS_DIVERIFIKASI,
                'status_sesudah' => Surat::STATUS_DITOLAK,
                'catatan'        => $request->catatan,
                'created_at'     => now(),
            ]);

            NotifikasiService::kirimKePembuat(
                $surat, 'Surat Ditolak Kajur',
                "Surat \"{$surat->hal}\" ditolak oleh Kajur. Alasan: {$request->catatan}", 'tolak'
            );

            return response()->json(['message' => 'Surat ditolak oleh Kajur']);
        }

        // Generate nomor surat resmi (atomic dengan lockForUpdate)
        $nomor = NomorSuratGenerator::generate($surat);

        $surat->update(['status' => Surat::STATUS_TERBIT]);

        SuratLog::create([
            'surat_id'       => $surat->id,
            'user_id'        => $user->id,
            'status_sebelum' => Surat::STATUS_DIVERIFIKASI,
            'status_sesudah' => Surat::STATUS_TERBIT,
            'catatan'        => "Surat diterbitkan dengan nomor {$nomor}",
            'created_at'     => now(),
        ]);

        NotifikasiService::kirimKePembuat(
            $surat, 'Surat Diterbitkan',
            "Surat \"{$surat->hal}\" telah diterbitkan dengan nomor {$nomor}.", 'terbit'
        );
        NotifikasiService::kirimKePenerima($surat);

        return response()->json([
            'message'     => 'Surat berhasil ditandatangani dan diterbitkan',
            'nomor_surat' => $nomor,
        ]);
    }

    /**
     * GET /surat/{id}/pdf — Generate & download PDF
     */
    public function pdf(int $id)
    {
        $surat = Surat::with([
            'kodeHal', 'penandaTangan', 'verifikator',
            'pembuatOleh', 'penerima', 'lampiran', 'kontenPenerima'
        ])->findOrFail($id);

        $user = auth()->user();
        $penerimaId = request()->query('penerima_id');
        
        // Akses control: dosen hanya bisa download surat yang ditujukan kepadanya
        if ($user && strtolower($user->role) === 'dosen') {
            $penerimaId = $user->id;
            
            $isPenerima = $surat->penerima->contains('id', $penerimaId);
            if (!$isPenerima) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke surat ini.'], 403);
            }
        }

        // Filter penerima agar hanya nama dosen tertentu yang muncul di PDF
        if ($penerimaId && $surat->penerima->contains('id', $penerimaId)) {
            $surat->setRelation('penerima', $surat->penerima->filter(fn($p) => $p->id == $penerimaId));
            
            // Override konten_html with specific content if it exists
            $specificKonten = $surat->kontenPenerima->firstWhere('penerima_user_id', $penerimaId);
            if ($specificKonten && $specificKonten->konten_html) {
                $surat->konten_html = $specificKonten->konten_html;
            }
        }

        $pdf = Pdf::loadView('pdf.surat', ['surat' => $surat])
            ->setPaper('A4', 'portrait');

        $filename = $surat->nomor_surat
            ? str_replace('/', '-', $surat->nomor_surat) . '.pdf'
            : "surat-draft-{$surat->id}.pdf";

        // Cek apakah ada lampiran berjenis PDF
        $hasPdfLampiran = $surat->lampiran->where('mime_type', 'application/pdf')->count() > 0;

        if (!$hasPdfLampiran) {
            return $pdf->download($filename);
        }

        // Jika ada lampiran PDF, gunakan FPDI untuk menggabungkan
        $basePdfContent = $pdf->output();
        $tempBasePdf = tempnam(sys_get_temp_dir(), 'surat_base_');
        file_put_contents($tempBasePdf, $basePdfContent);
        
        $fpdi = new \setasign\Fpdi\Fpdi();
        
        try {
            // Import halaman-halaman surat utama
            $pageCount = $fpdi->setSourceFile($tempBasePdf);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplIdx = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tplIdx);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tplIdx);
            }

            // Import halaman-halaman dari setiap lampiran PDF
            foreach ($surat->lampiran as $lamp) {
                if ($lamp->mime_type === 'application/pdf') {
                    $lampPath = storage_path('app/public/' . $lamp->path);
                    if (file_exists($lampPath)) {
                        $pageCountLamp = 0;
                        try {
                            $pageCountLamp = $fpdi->setSourceFile($lampPath);
                        } catch (\Exception $e) {
                            // Abaikan sementara error versi, kita akan mencoba downgrade menggunakan Ghostscript
                            $tempDowngraded = tempnam(sys_get_temp_dir(), 'downgraded_lampiran_') . '.pdf';
                            $gsPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'gswin64c' : 'gs';
                            
                            $isGsAvailable = false;
                            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                exec("where gswin64c 2>nul", $output, $returnVar);
                                if ($returnVar === 0) {
                                    $isGsAvailable = true;
                                } else {
                                    exec("where gswin32c 2>nul", $output, $returnVar);
                                    if ($returnVar === 0) {
                                        $gsPath = 'gswin32c';
                                        $isGsAvailable = true;
                                    }
                                }
                            } else {
                                exec("which gs 2>/dev/null", $output, $returnVar);
                                if ($returnVar === 0) $isGsAvailable = true;
                            }
                            
                            if ($isGsAvailable) {
                                $command = escapeshellcmd($gsPath) . " -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($tempDowngraded) . " " . escapeshellarg($lampPath);
                                exec($command, $output, $returnVar);
                                
                                if ($returnVar === 0 && file_exists($tempDowngraded)) {
                                    try {
                                        $pageCountLamp = $fpdi->setSourceFile($tempDowngraded);
                                    } catch (\Exception $e2) {
                                        // Tetap gagal setelah didowngrade
                                    }
                                }
                            }
                        }

                        if ($pageCountLamp > 0) {
                            for ($i = 1; $i <= $pageCountLamp; $i++) {
                                $tplIdx = $fpdi->importPage($i);
                                $size = $fpdi->getTemplateSize($tplIdx);
                                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                                $fpdi->useTemplate($tplIdx);
                            }
                        }
                    }
                }
            }

            $mergedPdfContent = $fpdi->Output('S');
            
            return response($mergedPdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } finally {
            if (file_exists($tempBasePdf)) {
                unlink($tempBasePdf);
            }
        }
    }

    /**
     * GET /surat/{id}/lampiran/{lid}/download
     */
    public function downloadLampiran(int $id, int $lid)
    {
        $lamp = SuratLampiran::where('surat_id', $id)->findOrFail($lid);

        if (!Storage::disk('public')->exists($lamp->path)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        return Storage::disk('public')->download($lamp->path, $lamp->nama_file_asli);
    }

    /**
     * DELETE /surat/{id}/lampiran/{lid}
     */
    public function deleteLampiran(int $id, int $lid): JsonResponse
    {
        $surat = Surat::findOrFail($id);

        if ($surat->status !== Surat::STATUS_DRAFT) {
            return response()->json(['message' => 'Lampiran hanya dapat dihapus pada status draft.'], 422);
        }

        $lamp = SuratLampiran::where('surat_id', $id)->findOrFail($lid);
        FileHelper::deleteLampiran($lamp->path);
        $lamp->delete();

        return response()->json(['message' => 'Lampiran berhasil dihapus']);
    }

    /**
     * GET /surat/verifikasi — Antrian verifikasi (verifikator)
     */
    public function antrianVerifikasi(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $surat = Surat::with(['kodeHal', 'pembuatOleh'])
            ->withCount('lampiran')
            ->where('verifikator_id', $user->id)
            ->where('status', Surat::STATUS_MENUNGGU_VERIFIKASI)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => SuratListResource::collection($surat),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'total'        => $surat->total(),
            ],
        ]);
    }

    /**
     * GET /surat/tandatangan — Antrian tanda tangan (kajur)
     */
    public function antrianTandaTangan(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $surat = Surat::with(['kodeHal', 'pembuatOleh'])
            ->withCount('lampiran')
            ->where('penanda_tangan_id', $user->id)
            ->where('status', Surat::STATUS_DIVERIFIKASI)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'data' => SuratListResource::collection($surat),
            'meta' => [
                'current_page' => $surat->currentPage(),
                'last_page'    => $surat->lastPage(),
                'total'        => $surat->total(),
            ],
        ]);
    }
}
