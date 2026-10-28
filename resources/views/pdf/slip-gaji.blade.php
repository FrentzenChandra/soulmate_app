<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $slip->nama_staf_snapshot ?: ($slip->user->name ?? 'Karyawan') }} - {{ sprintf('%04d-%02d', $slip->periode_tahun, $slip->periode_bulan) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 12mm 6mm 12mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000000;
            line-height: 1.25;
            background: #ffffff;
        }
        .w-full { width: 100%; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-fixed {
            table-layout: fixed;
        }

        /* 1. Header */
        .header-table {
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1.5px solid #E2E8F0;
        }
        .company-title-brand {
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #5E17EB;
            display: inline;
        }
        .company-title-dark {
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #111827;
            display: inline;
        }
        .company-subtitle {
            font-size: 6.8pt;
            font-weight: bold;
            color: #64748B;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }
        .banner-box {
            background: #5E17EB;
            border-radius: 8px;
            padding: 6px 10px;
            color: #ffffff;
        }
        .banner-title {
            font-size: 11pt;
            font-weight: 900;
            letter-spacing: 0.8px;
            color: #ffffff;
            line-height: 1.1;
        }
        .banner-sub {
            font-size: 6.5pt;
            font-weight: bold;
            letter-spacing: 1px;
            color: #E9D5FF;
        }
        .banner-pill {
            background: #ffffff;
            color: #5E17EB;
            font-weight: 900;
            font-size: 7.5pt;
            padding: 3px 8px;
            border-radius: 12px;
            text-align: center;
            display: inline-block;
            text-transform: uppercase;
        }

        /* 2. Profile Box */
        .profile-box {
            margin-bottom: 8px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            background: #F8FAFC;
            padding: 5px 8px;
            page-break-inside: avoid;
        }
        .profile-table td {
            padding: 2px 3px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .profile-label {
            width: 85px;
            font-weight: bold;
            color: #475569;
        }
        .profile-separator {
            width: 8px;
            text-align: center;
            color: #94A3B8;
        }
        .profile-value {
            font-weight: bold;
            color: #000000;
        }
        .profile-badge {
            color: #475569;
            font-weight: normal;
            font-size: 7pt;
        }

        /* 3. Section Tables */
        .section-table {
            margin-bottom: 6px;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .header-penerimaan {
            background: #5E17EB;
            color: #ffffff;
        }
        .header-penerimaan th {
            color: #ffffff;
            padding: 4px 6px;
            font-size: 7.5pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            border: none;
        }
        .header-pengurangan {
            background: #E11D48;
            color: #ffffff;
        }
        .header-pengurangan th {
            color: #ffffff;
            padding: 4px 6px;
            font-size: 7.5pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            border: none;
        }

        .item-row td {
            padding: 2.5px 6px;
            font-size: 7.5pt;
            border-bottom: 1px solid #F1F5F9;
        }
        .item-bullet {
            color: #5E17EB;
            font-size: 8pt;
            padding-right: 3px;
        }
        .item-bullet-red {
            color: #E11D48;
            font-size: 8pt;
            padding-right: 3px;
        }

        /* Highlight Strips */
        .strip-bruto td {
            background: #FEF3C7;
            border-top: 1px solid #FDE68A !important;
            padding: 4px 6px;
            font-weight: 900;
            font-size: 7.5pt;
            color: #000000;
        }
        .gold-badge {
            background: #FACC15;
            padding: 1.5px 6px;
            border-radius: 4px;
            display: inline-block;
            font-weight: 900;
            color: #000000;
        }

        .strip-pengurangan td {
            background: #FEE2E2;
            border-top: 1px solid #FECACA !important;
            padding: 4px 6px;
            font-weight: 900;
            font-size: 7.5pt;
            color: #000000;
        }
        .amount-red {
            color: #E11D48 !important;
        }

        /* 4. Grandtotal Box */
        .grandtotal-box {
            background: #059669;
            border-radius: 8px;
            padding: 6px 10px;
            margin-top: 4px;
            margin-bottom: 8px;
            color: #ffffff;
            page-break-inside: avoid;
        }
        .grandtotal-box td {
            color: #ffffff;
            vertical-align: middle;
        }
        .grandtotal-label {
            font-size: 9.5pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #ffffff;
        }
        .grandtotal-amount {
            font-size: 13pt;
            font-weight: 900;
            text-align: right;
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* 5. Footer Box */
        .footer-table {
            padding-top: 6px;
            border-top: 1px solid #E2E8F0;
            page-break-inside: avoid;
        }
        .footer-table td {
            vertical-align: top;
        }
        .terima-kasih-title {
            font-family: "Times New Roman", Times, Georgia, serif;
            font-style: italic;
            font-size: 14pt;
            font-weight: 900;
            color: #5E17EB;
            margin-bottom: 2px;
        }
        .terima-kasih-text {
            font-size: 7pt;
            color: #475569;
            font-weight: bold;
            max-width: 240px;
            line-height: 1.25;
        }
        .watermark-slogan {
            font-size: 7.5pt;
            font-weight: 900;
            color: #CBD5E1;
            letter-spacing: 2px;
            text-align: center;
            text-transform: uppercase;
            font-style: italic;
            padding-top: 8px;
        }
        .signature-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #000000;
            text-align: center;
        }
        .signature-line {
            display: inline-block;
            width: 130px;
            margin-top: 28px;
            margin-bottom: 2px;
        }
        .signature-name {
            font-size: 7.5pt;
            font-weight: 900;
            color: #000000;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
@php
    $cabang = $slip->cabang;
    $namaStaf = $slip->nama_staf_snapshot ?: ($slip->user->name ?? 'Karyawan');
    $jabatan = $slip->bagian_snapshot ?: ($slip->user->bagian ?? '-');
    $tanggalLahir = $slip->tanggal_lahir_snapshot ? $slip->tanggal_lahir_snapshot->translatedFormat('d F Y') : '-';
    $usia = $slip->usia_snapshot ?: '-';
    $tanggalMulaiBekerja = $slip->tanggal_mulai_bekerja_snapshot ? $slip->tanggal_mulai_bekerja_snapshot->translatedFormat('F Y') : '-';
    $masaKerja = $slip->masa_kerja_snapshot ?: '-';
    $periodeNama = \Carbon\Carbon::createFromDate($slip->periode_tahun, $slip->periode_bulan, 1)->translatedFormat('F Y');
    $periodeUpper = strtoupper($periodeNama);

    // Evaluasi apakah ada item yang menggunakan kuantiti Hari
    $hasHari = ((float)$slip->uang_makan_total > 0 || (float)$slip->uang_transport_total > 0);

    // Filter daftar item pengurangan dengan nominal > 0
    $potonganItems = $slip->items ? $slip->items->filter(fn($it) => (float)$it->nominal > 0) : collect();

    // Logo Base64
    $logoPath = public_path('images/logo_icon.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
@endphp

    <!-- 1. Header (Logo Kiri + Banner Ungu Kanan) -->
    <table class="w-full header-table">
        <tr>
            <td style="width: 58%; vertical-align: middle;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 44px; vertical-align: middle; padding-right: 8px;">
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" style="width: 40px; height: 40px;" alt="Logo">
                            @else
                                <div style="width: 40px; height: 40px; background: #5E17EB; border-radius: 8px; text-align: center; line-height: 40px; color: #ffffff; font-weight: 900; font-size: 16pt;">
                                    S
                                </div>
                            @endif
                        </td>
                        <td style="vertical-align: middle;">
                            <div>
                                <span class="company-title-brand">SOULMATE</span>
                                <span class="company-title-dark"> KOMPUTER</span>
                            </div>
                            <div class="company-subtitle">
                                LAPTOP &bull; KOMPUTER &bull; PRINTER &bull; CCTV &bull; SERVICE
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 42%; vertical-align: middle; text-align: right;">
                <table class="banner-box" style="width: 100%;">
                    <tr>
                        <td style="vertical-align: middle; text-align: left;">
                            <div class="banner-title">SLIP GAJI</div>
                            <div class="banner-sub">KARYAWAN</div>
                        </td>
                        <td style="vertical-align: middle; text-align: right;">
                            <span class="banner-pill">{{ $periodeUpper }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 2. Profil Karyawan Box -->
    <div class="profile-box">
        <table class="w-full table-fixed profile-table">
            <tr>
                <td style="width: 50%;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="profile-label">Nama Karyawan</td>
                            <td class="profile-separator">:</td>
                            <td class="profile-value">{{ $namaStaf }}</td>
                        </tr>
                        <tr>
                            <td class="profile-label">Jabatan</td>
                            <td class="profile-separator">:</td>
                            <td class="profile-value">{{ $jabatan }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="profile-label">Tanggal Lahir</td>
                            <td class="profile-separator">:</td>
                            <td class="profile-value">
                                {{ $tanggalLahir }}
                                @if($usia !== '-')
                                    <span class="profile-badge">({{ $usia }})</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="profile-label">Periode Gaji</td>
                            <td class="profile-separator">:</td>
                            <td class="profile-value">{{ $periodeNama }}</td>
                        </tr>
                        <tr>
                            <td class="profile-label">Masa Kerja</td>
                            <td class="profile-separator">:</td>
                            <td class="profile-value">
                                {{ $tanggalMulaiBekerja }}
                                @if($masaKerja !== '-')
                                    <span class="profile-badge">({{ $masaKerja }})</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- 3. TABEL PENERIMAAN -->
    <table class="w-full table-fixed section-table">
        <thead>
            <tr class="header-penerimaan">
                <th class="text-left" style="width: {{ $hasHari ? '50%' : '75%' }};">PENERIMAAN</th>
                @if($hasHari)
                    <th class="text-center" style="width: 14%;">Hari</th>
                    <th class="text-right" style="width: 18%;">Per Hari</th>
                @endif
                <th class="text-right" style="width: {{ $hasHari ? '18%' : '25%' }};">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            {{-- Gaji Pokok --}}
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> Gaji Pokok
                </td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
            </tr>

            {{-- Uang Makan --}}
            @if((float)$slip->uang_makan_total > 0)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> Uang Makan
                </td>
                @if($hasHari)
                    <td class="text-center font-bold">{{ $slip->uang_makan_hari }} Hari</td>
                    <td class="text-right">Rp {{ number_format($slip->uang_makan_per_hari, 0, ',', '.') }}</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->uang_makan_total, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Uang Transportasi --}}
            @if((float)$slip->uang_transport_total > 0)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> Uang Transportasi
                </td>
                @if($hasHari)
                    <td class="text-center font-bold">{{ $slip->uang_transport_hari }} Hari</td>
                    <td class="text-right">Rp {{ number_format($slip->uang_transport_per_hari, 0, ',', '.') }}</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->uang_transport_total, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Tunjangan --}}
            @if((float)$slip->tunjangan_nominal > 0)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> {{ $slip->tunjangan_nama ?: 'Tunjangan' }}
                </td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->tunjangan_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Bonus --}}
            @if((float)$slip->bonus_nominal > 0)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> Bonus
                </td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->bonus_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Lembur/Performa --}}
            @if((float)$slip->lembur_performa_nominal > 0)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> Lembur/Performa
                </td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->lembur_performa_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Servisan Selesai --}}
            @if((float)$slip->servisan_selesai_nominal > 0)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet">&bull;</span> Servisan Selesai
                </td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->servisan_selesai_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="strip-bruto">
                <td colspan="{{ $hasHari ? 3 : 1 }}" class="uppercase">TOTAL PENGHASILAN BRUTO</td>
                <td class="text-right">
                    <span class="gold-badge">Rp {{ number_format($slip->total_bruto, 0, ',', '.') }}</span>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- 4. TABEL PENGURANGAN (Hanya Tampil Jika Ada Potongan) -->
    @if($potonganItems->isNotEmpty())
    <table class="w-full table-fixed section-table">
        <thead>
            <tr class="header-pengurangan">
                <th class="text-left" style="width: {{ $hasHari ? '50%' : '75%' }};">PENGURANGAN</th>
                @if($hasHari)
                    <th class="text-center" style="width: 14%;">Hari</th>
                    <th class="text-right" style="width: 18%;">Per Hari</th>
                @endif
                <th class="text-right" style="width: {{ $hasHari ? '18%' : '25%' }};">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($potonganItems as $potongan)
            <tr class="item-row">
                <td class="font-bold">
                    <span class="item-bullet-red">&bull;</span> {{ $potongan->nama_item ?: ($potongan->kategori ?: 'Potongan') }}
                </td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold amount-red">Rp {{ number_format($potongan->nominal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="strip-pengurangan">
                <td colspan="{{ $hasHari ? 3 : 1 }}" class="uppercase">TOTAL PENGURANGAN</td>
                <td class="text-right amount-red">Rp {{ number_format($slip->total_pengurangan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- 5. GRAND TOTAL BOX (TOTAL DITERIMA KARYAWAN) -->
    <table class="w-full grandtotal-box">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div class="grandtotal-label uppercase">TOTAL DITERIMA KARYAWAN</div>
            </td>
            <td style="width: 40%; vertical-align: middle; text-align: right;">
                <div class="grandtotal-amount">Rp {{ number_format($slip->total_diterima, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- 6. FOOTER (UCAPAN TERIMA KASIH, WATERMARK, TANDA TANGAN) -->
    <table class="w-full footer-table">
        <tr>
            <!-- Sisi Kiri: Ucapan Terima Kasih -->
            <td style="width: 42%;">
                <div class="terima-kasih-title">Terima Kasih</div>
                <div class="terima-kasih-text">
                    Atas dedikasi dari kerja keras Anda untuk kemajuan Soulmate Komputer.
                </div>
            </td>

            <!-- Tengah: Slogan Watermark -->
            <td style="width: 28%; text-align: center;">
                <div class="watermark-slogan">
                    BERSAMA LEBIH MAJU
                </div>
            </td>

            <!-- Sisi Kanan: Tanda Tangan -->
            <td style="width: 30%; text-align: center;">
                <div class="signature-title">
                    Pangkalpinang, {{ $periodeNama }}
                </div>
                <div>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-name">
                    Manajemen<br>Soulmate Komputer
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
