<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Upload lampiran file, return array data untuk insert DB.
     */
    public static function uploadLampiran(UploadedFile $file, int $suratId): array
    {
        $originalName = $file->getClientOriginalName();
        $extension    = $file->getClientOriginalExtension();
        $safeFilename = self::generateSafeFilename($extension);
        $path         = "lampiran/surat-{$suratId}/{$safeFilename}";

        Storage::disk('public')->put($path, file_get_contents($file));

        $jumlahHalaman = 1;
        if (strtolower($extension) === 'pdf') {
            try {
                $content = file_get_contents($file->getPathname());
                $jumlahHalaman = preg_match_all("/\/Page\W/", $content, $dummy);
                if ($jumlahHalaman === 0) $jumlahHalaman = 1;
            } catch (\Exception $e) {
                $jumlahHalaman = 1;
            }
        }

        return [
            'surat_id'         => $suratId,
            'nama_file_asli'   => $originalName,
            'nama_file_sistem' => $safeFilename,
            'path'             => $path,
            'ukuran_bytes'     => $file->getSize(),
            'jumlah_halaman'   => $jumlahHalaman,
            'mime_type'        => $file->getMimeType(),
            'created_at'       => now(),
        ];
    }

    /**
     * Delete lampiran file from storage.
     */
    public static function deleteLampiran(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    /**
     * Generate safe unique filename with timestamp + random suffix.
     */
    public static function generateSafeFilename(string $extension): string
    {
        return date('YmdHis') . '_' . Str::random(8) . '.' . $extension;
    }

    /**
     * Format file size human readable.
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
