<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $surat->nomor_surat ?? 'Draft Surat' }}</title>
    <style>
        @page {
            margin: 2.54cm;
            size: A4;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* ── Kop Surat ── */
        .header {
            border-bottom: 4px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
            margin-top: -1cm;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 95px;
            vertical-align: middle;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            line-height: 1;
        }
        .header-text .instansi {
            font-size: 14pt;
            font-weight: normal;
            text-transform: uppercase;
        }
        .header-text .jurusan {
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text .alamat {
            font-size: 10pt;
        }

        /* ── Identitas Surat ── */
        .surat-info {
            margin: 5px 0;
        }
        .surat-info table {
            border-collapse: collapse;
        }
        .surat-info td {
            padding: 1px 0;
            vertical-align: top;
            line-height: 1.2;
        }
        .surat-info td:first-child {
            width: 100px;
        }
        .surat-info td:nth-child(2) {
            width: 10px;
            padding: 0 5px;
        }

        /* ── Body Surat ── */
        .surat-body {
            margin: 10px 0;
            text-align: justify;
        }
        .surat-body p {
            margin: 0;
        }
        .surat-body table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }
        .surat-body table th, .surat-body table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        /* ── Tanda Tangan ── */
        .ttd-section {
            margin-top: 40px;
        }
        .ttd-block {
            float: right;
            text-align: left;
            width: 320px;
        }
        .ttd-block .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            white-space: nowrap;
        }
        .ttd-block .ttd-jabatan {
            font-size: 11pt;
        }
        .ttd-block .ttd-nip {
            font-size: 10pt;
        }
        .ttd-space { height: 70px; }

        /* ── Lampiran list ── */
        .lampiran-list {
            margin-top: 20px;
            font-size: 10pt;
        }

        /* ── Nomor surat watermark jika draft ── */
        .draft-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0,0,0,0.05);
            font-weight: bold;
            z-index: -1;
        }

        .clearfix::after { content: ''; display: table; clear: both; }
    </style>
</head>
<body>

@if(!$surat->nomor_surat)
<div class="draft-watermark">DRAFT</div>
@endif

<!-- ── Kop Surat ─────────────────────────────────────────── -->
<div class="header">
    <div class="header-logo">
        <img src="{{ public_path('logo_PNUP.png') }}" alt="Logo PNUP" style="width: 95px; height: auto;">
    </div>
    <div class="header-text">
        <div class="instansi">KEMENTERIAN PENDIDIKAN TINGGI,<br>SAINS, DAN TEKNOLOGI</div>
        <div class="jurusan">POLITEKNIK NEGERI UJUNG PANDANG</div>
        <div class="alamat">
            Direktorat Kampus Tamalanrea, Jl. P. Kemerdekaan Km. 10, Makassar 90245<br>
            E-mail: pnup@poliupg.ac.id Laman www.poliupg.ac.id
        </div>
    </div>
</div>

<!-- ── Konten Surat ──────────────────────────────────────── -->
<div class="surat-info">
    <table style="width: 100%;">
        <tr>
            <td style="width: 60%">
                <table>
                    <tr>
                        <td>Nomor</td>
                        <td>:</td>
                        <td>{{ $surat->nomor_surat ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Lampiran</td>
                        <td>:</td>
                        <td>{{ $surat->lampiran->count() > 0 ? $surat->lampiran->sum(fn($l) => $l->jumlah_halaman ?: 1) . ' Lembar' : '-' }}</td>
                    </tr>
                    <tr>
                        <td>Perihal</td>
                        <td>:</td>
                        <td><strong>{{ $surat->hal }}</strong></td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                Makassar, {{ \Carbon\Carbon::parse($surat->tanggal_terbit ?? $surat->created_at)->translatedFormat('d F Y') }}
            </td>
        </tr>
    </table>
</div>

<div style="margin-bottom: 20px;">
    <div>Kepada Yth.</div>
    @foreach($surat->penerima as $p)
        <div style="padding-left: 16px;">{{ $p->nama_lengkap }}</div>
    @endforeach
    <div style="padding-left: 16px;">Di - Tempat</div>
</div>

<div class="surat-body">
    {!! $surat->konten_html ?? '<p>Konten surat belum diisi.</p>' !!}
</div>

<table style="width: 100%; margin-top: 20px; border-collapse: collapse; border: none;">
    <tr>
        <td style="width: 60%; border: none;"></td>
        <td style="text-align: left; vertical-align: top; border: none;">
            <strong>
                <div>Ketua Jurusan</div>
                <div>Teknik Informatika dan Komputer</div>
            </strong>
            <div style="height: 70px;">
                @if(isset($surat->penandaTangan->ttd) && $surat->penandaTangan->ttd)
                    <img src="{{ public_path('storage/' . $surat->penandaTangan->ttd) }}" style="max-height: 70px; width: auto; object-fit: contain; margin-top: 8px;">
                @endif
            </div>
            <strong>
                <u>{{ $surat->penandaTangan->nama_lengkap ?? 'Nama Penanda Tangan' }}</u><br>
                NIP. {{ $surat->penandaTangan->nip ?? '-' }}
            </strong>
        </td>
    </tr>
</table>

<!-- Daftar Lampiran Berkas dilebur langsung ke halaman selanjutnya -->
@if($surat->lampiran->count() > 0)
@foreach($surat->lampiran as $lamp)
    @if(str_starts_with($lamp->mime_type, 'image/'))
        <div style="page-break-before: always;">
            <img src="{{ public_path('storage/' . $lamp->path) }}" style="max-width: 100%; max-height: 950px; display: block; margin: 0 auto;">
        </div>
    @endif
@endforeach
@endif

</body>
</html>
