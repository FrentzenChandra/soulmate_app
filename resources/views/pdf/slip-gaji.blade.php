<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $slip->nama_staf_snapshot ?: ($slip->user->name ?? 'Karyawan') }} - {{ sprintf('%04d-%02d', $slip->periode_tahun, $slip->periode_bulan) }}</title>
    <style>
        @page {
            size: portrait;
            margin: 8mm 14mm 10mm 14mm;
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
            font-size: 9pt;
            color: #000000;
            line-height: 1.3;
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

        /* Header */
        .header-box {
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo-img {
            max-height: 48px;
            width: auto;
        }
        .company-title {
            font-size: 14pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .company-subtitle {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 2px;
        }
        .doc-title {
            font-size: 14pt;
            font-weight: 900;
            letter-spacing: 1px;
            text-align: right;
        }
        .doc-period {
            font-size: 9pt;
            font-weight: bold;
            text-align: right;
            margin-top: 2px;
        }

        /* Profile Box */
        .profile-table {
            margin-bottom: 14px;
            border: 1px solid #000000;
            background: #ffffff;
        }
        .profile-table td {
            padding: 4px 8px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .profile-label {
            width: 110px;
            font-weight: bold;
        }
        .profile-separator {
            width: 12px;
            text-align: center;
        }
        .profile-value {
            font-weight: normal;
        }

        /* Section Headings */
        .section-header {
            background: #f0f0f0;
            border-top: 1.5px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 4px 8px;
            font-size: 8.5pt;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        /* Items Tables */
        .data-table {
            margin-bottom: 12px;
            border-left: 1px solid #000000;
            border-right: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }
        .data-table th {
            background: #f8f8f8;
            border-bottom: 1px solid #000000;
            padding: 4px 8px;
            font-size: 8pt;
            font-weight: bold;
        }
        .data-table td {
            padding: 3.5px 8px;
            font-size: 8.5pt;
            border-bottom: 1px dotted #cccccc;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .subtotal-row td {
            background: #f4f4f4;
            border-top: 1.5px solid #000000 !important;
            border-bottom: none !important;
            padding: 5px 8px;
            font-weight: 900;
            font-size: 9pt;
        }

        /* Grandtotal Box */
        .grandtotal-table {
            margin-top: 4px;
            margin-bottom: 16px;
            border: 2px solid #000000;
            background: #ffffff;
        }
        .grandtotal-table td {
            padding: 8px 10px;
        }
        .grandtotal-label {
            font-size: 11pt;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .grandtotal-amount {
            font-size: 13pt;
            font-weight: 900;
            text-align: right;
        }

        /* Signatures */
        .signature-table {
            margin-top: 20px;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            font-size: 8.5pt;
        }
        .sign-line {
            display: inline-block;
            width: 150px;
            border-bottom: 1px solid #000000;
            margin-top: 45px;
            margin-bottom: 4px;
        }
        .footer-note {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px dashed #000000;
            font-size: 8pt;
            text-align: center;
            font-style: italic;
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

    // Logo base64
    $logoPath = public_path('images/logo_bulat.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

    // Evaluasi apakah ada item yang menggunakan kuantiti Hari
    $hasHari = ((float)$slip->uang_makan_total > 0 || (float)$slip->uang_transport_total > 0);

    // Filter daftar item pengurangan dengan nominal > 0
    $potonganItems = $slip->items->filter(fn($it) => (float)$it->nominal > 0);
@endphp

    <!-- Header Box -->
    <table class="w-full table-fixed header-box">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <table style="width: auto;">
                    <tr>
                        @if($logoData)
                        <td style="vertical-align: middle; padding-right: 10px; width: 48px;">
                            <img src="{{ $logoData }}" class="logo-img" alt="Logo">
                        </td>
                        @endif
                        <td style="vertical-align: middle;">
                            <div class="company-title">SOULMATE KOMPUTER</div>
                            <div class="company-subtitle">{{ $cabang->nama_cabang ?? 'Pusat' }} &bull; {{ $cabang->alamat ?? 'Pangkalpinang' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: middle; text-align: right;">
                <div class="doc-title">SLIP GAJI KARYAWAN</div>
                <div class="doc-period">PERIODE: {{ strtoupper($periodeNama) }}</div>
                <div style="font-size: 7.5pt; margin-top: 2px;">No: {{ $slip->no_slip }}</div>
            </td>
        </tr>
    </table>

    <!-- Profil Karyawan Box -->
    <table class="w-full table-fixed profile-table">
        <tr>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="profile-label">Nama Karyawan</td>
                        <td class="profile-separator">:</td>
                        <td class="profile-value font-bold">{{ $namaStaf }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Jabatan</td>
                        <td class="profile-separator">:</td>
                        <td class="profile-value">{{ $jabatan }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Tanggal Lahir</td>
                        <td class="profile-separator">:</td>
                        <td class="profile-value">{{ $tanggalLahir }} ({{ $usia }})</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%;">
                <table>
                    <tr>
                        <td class="profile-label">Periode Gaji</td>
                        <td class="profile-separator">:</td>
                        <td class="profile-value font-bold">{{ $periodeNama }}</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Masa Kerja</td>
                        <td class="profile-separator">:</td>
                        <td class="profile-value">{{ $tanggalMulaiBekerja }} ({{ $masaKerja }})</td>
                    </tr>
                    <tr>
                        <td class="profile-label">Status Slip</td>
                        <td class="profile-separator">:</td>
                        <td class="profile-value font-bold uppercase">{{ $slip->status }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 1. TABEL PENERIMAAN -->
    <table class="w-full table-fixed data-table">
        <thead>
            <tr class="section-header">
                <th colspan="{{ $hasHari ? 4 : 2 }}" class="text-left uppercase">Penerimaan</th>
            </tr>
            @if($hasHari)
            <tr>
                <th class="text-left" style="width: 48%;">Keterangan</th>
                <th class="text-center" style="width: 14%;">Hari</th>
                <th class="text-right" style="width: 18%;">Per Hari</th>
                <th class="text-right" style="width: 20%;">Jumlah</th>
            </tr>
            @else
            <tr>
                <th class="text-left" style="width: 75%;">Keterangan</th>
                <th class="text-right" style="width: 25%;">Jumlah</th>
            </tr>
            @endif
        </thead>
        <tbody>
            <!-- Gaji Pokok (Selalu Tampil) -->
            <tr>
                <td class="font-bold">Gaji Pokok</td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right font-bold">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
            </tr>

            {{-- Uang Makan (Hanya tampil jika nominal > 0) --}}
            @if((float)$slip->uang_makan_total > 0)
            <tr>
                <td>Uang Makan</td>
                @if($hasHari)
                    <td class="text-center">{{ $slip->uang_makan_hari }} Hari</td>
                    <td class="text-right">Rp {{ number_format($slip->uang_makan_per_hari, 0, ',', '.') }}</td>
                @endif
                <td class="text-right">Rp {{ number_format($slip->uang_makan_total, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Uang Transportasi (Hanya tampil jika nominal > 0) --}}
            @if((float)$slip->uang_transport_total > 0)
            <tr>
                <td>Uang Transportasi</td>
                @if($hasHari)
                    <td class="text-center">{{ $slip->uang_transport_hari }} Hari</td>
                    <td class="text-right">Rp {{ number_format($slip->uang_transport_per_hari, 0, ',', '.') }}</td>
                @endif
                <td class="text-right">Rp {{ number_format($slip->uang_transport_total, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Tunjangan (Hanya tampil jika nominal > 0) --}}
            @if((float)$slip->tunjangan_nominal > 0)
            <tr>
                <td>{{ $slip->tunjangan_nama ?: 'Tunjangan' }}</td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right">Rp {{ number_format($slip->tunjangan_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Bonus (Hanya tampil jika nominal > 0) --}}
            @if((float)$slip->bonus_nominal > 0)
            <tr>
                <td>Bonus</td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right">Rp {{ number_format($slip->bonus_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Lembur/Performa (Hanya tampil jika nominal > 0) --}}
            @if((float)$slip->lembur_performa_nominal > 0)
            <tr>
                <td>Lembur/Performa</td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right">Rp {{ number_format($slip->lembur_performa_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif

            {{-- Servisan Selesai (Hanya tampil jika nominal > 0) --}}
            @if((float)$slip->servisan_selesai_nominal > 0)
            <tr>
                <td>Servisan Selesai</td>
                @if($hasHari)
                    <td class="text-center">-</td>
                    <td class="text-right">-</td>
                @endif
                <td class="text-right">Rp {{ number_format($slip->servisan_selesai_nominal, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="subtotal-row">
                <td colspan="{{ $hasHari ? 3 : 1 }}" class="uppercase">Total Penghasilan Bruto</td>
                <td class="text-right">Rp {{ number_format($slip->total_bruto, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- 2. TABEL PENGURANGAN (Hanya Tampil Jika Ada Potongan) --}}
    @if($potonganItems->isNotEmpty())
    <table class="w-full table-fixed data-table">
        <thead>
            <tr class="section-header">
                <th colspan="2" class="text-left uppercase">Pengurangan</th>
            </tr>
            <tr>
                <th class="text-left" style="width: 75%;">Keterangan</th>
                <th class="text-right" style="width: 25%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($potonganItems as $potongan)
            <tr>
                <td>{{ $potongan->nama_item ?: $potongan->kategori }}</td>
                <td class="text-right">Rp {{ number_format($potongan->nominal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="subtotal-row">
                <td class="uppercase">Total Pengurangan</td>
                <td class="text-right">Rp {{ number_format($slip->total_pengurangan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- 3. GRAND TOTAL BOX (Label Opsi A: Total Diterima) --}}
    <table class="w-full table-fixed grandtotal-table">
        <tr>
            <td class="grandtotal-label uppercase">TOTAL DITERIMA</td>
            <td class="grandtotal-amount">Rp {{ number_format($slip->total_diterima, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <table class="w-full table-fixed signature-table">
        <tr>
            <td style="width: 50%;">
                <div>Penerima,</div>
                <div class="sign-line"></div>
                <div class="font-bold">{{ $namaStaf }}</div>
                <div style="font-size: 7.5pt;">Karyawan</div>
            </td>
            <td style="width: 50%;">
                <div>{{ $cabang->kota ?? 'Pangkalpinang' }}, {{ $periodeNama }}</div>
                <div style="margin-top: 2px;">Manajemen Soulmate Komputer,</div>
                <div class="sign-line"></div>
                <div class="font-bold">{{ $slip->finalizedBy ? $slip->finalizedBy->name : 'Finance / HR' }}</div>
                <div style="font-size: 7.5pt;">Pengelola</div>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer-note">
        Terima Kasih atas dedikasi dan kerja keras Anda untuk kemajuan Soulmate Komputer.
    </div>
</body>
</html>

