<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }}</title>
    <style>
        @page {
            margin: 4mm 15mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
        }
        body {
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            line-height: 1.25;
            background: #fff;
        }
        .w-full { width: 100%; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .uppercase { text-transform: uppercase; }
        .text-muted { color: #1a1a1a; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-layout {
            table-layout: fixed;
        }

        .header-table td {
            vertical-align: top;
        }

        .divider-title {
            border-top: 2.5px dashed #0a0a0a;
            border-bottom: 2.5px dashed #0a0a0a;
            padding: 4px 0;
            margin: 6px 0 8px 0;
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 9pt;
            color: #111;
        }

        .specs-table {
            margin-bottom: 8px;
        }
        .spec-cell {
            padding: 4px 8px;
            vertical-align: top;
        }
        .spec-box {
            border: 1px solid #e5e7eb;
            background: #fafafa;
            border-radius: 4px;
            padding: 6px 8px;
            min-height: 52px;
        }

        .sign-title {
            color: #1a1a1a;
            font-size: 8pt;
            margin-bottom: 30px;
        }
        .sign-name {
            border-top: 1px solid #1a1a1a;
            padding-top: 3px;
            display: inline-block;
            min-width: 150px;
            font-weight: bold;
            font-size: 8.5pt;
        }

        .info-box {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            padding: 6px 10px;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 8pt;
            line-height: 1.3;
        }
    </style>
</head>
<body>
@php
    $cabang = $servis->cabang;
    $customerName = $servis->customer?->nama ?? 'Pelanggan Umum';
    $customerPhone = format_phone($servis->customer?->no_hp);
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = format_whatsapp($cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255');
    $pembuatName = $servis->pembuat?->name ?? 'Soulmate Komputer';

    $logoPath = public_path('images/logo_bulat.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

    $tglMasuk = $servis->tanggal_masuk ? $servis->tanggal_masuk->format('d-m-Y H:i') : '-';
@endphp

    <!-- Header Section -->
    <table class="w-full header-table table-layout" style="margin-bottom: 4px;">
        <tr>
            <!-- Col 1: Logo & Store Info (52%) -->
            <td style="width: 52%;">
                <table>
                    <tr>
                        @if($logoData)
                            <td style="width: 44px; padding-right: 8px; vertical-align: top;">
                                <img src="{{ $logoData }}" style="width: 38px; height: 38px; object-fit: contain;">
                            </td>
                        @endif
                        <td style="vertical-align: top;">
                            <div style="font-size: 11pt; font-weight: bold; color: #111; line-height: 1.1;">Soulmate Komputer</div>
                            <div class="text-muted" style="font-size: 7.5pt; margin-top: 2px;">{{ $address }}</div>
                            <div style="font-size: 7.5pt; font-weight: bold; margin-top: 1px;">WhatsApp: {{ $phone }}</div>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Col 2: Meta Info (48%) -->
            <td style="width: 48%; padding-left: 10px;">
                <table class="w-full" style="font-size: 8pt;">
                    <tr>
                        <td  style="width: 32%;">Tanggal</td>
                        <td style="width: 5%;">:</td>
                        <td class="font-bold font-mono">{{ $tglMasuk }}</td>
                    </tr>
                    <tr>
                        <td >Pelanggan</td>
                        <td>:</td>
                        <td class="font-bold">{{ $customerName }}</td>
                    </tr>
                    <tr>
                        <td >Kode Servis</td>
                        <td>:</td>
                        <td class="font-bold font-mono" style="font-size: 9.5pt; color: #5E17EB;">
                            {{ $servis->no_servis_frontend ?? $servis->no_servis }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Title Bar -->
    <div class="divider-title">
        TANDA TERIMA BARANG SERVIS
    </div>

    <!-- Specs 2-Column Grid -->
    <table class="w-full specs-table table-layout">
        <tr>
            <!-- Col 1: Unit & Keluhan -->
            <td class="spec-cell" style="width: 50%; padding-left: 0;">
                <div class="spec-box" style="margin-bottom: 6px;">
                    <div class="font-bold uppercase" style="font-size: 7pt;">Unit Perangkat :</div>
                    <div style="font-size: 8.5pt; margin-top: 1px;">
                        {{ $servis->tipe_unit ?: ($servis->kategori_perangkat_label . ' (Tanpa Tipe)') }}
                    </div>
                    <div style="font-size: 7pt; margin-top: 1px;">
                        Kategori: {{ $servis->kategori_perangkat_label }}
                    </div>
                </div>

                <div class="spec-box">
                    <div class="font-bold uppercase" style="font-size: 7pt;">Keluhan Kerusakan :</div>
                    <div style="margin-top: 2px; font-size: 7.5pt; line-height: 1.25;  font-style: normal;">
                        {{ $servis->keluhan ?: '-' }}
                    </div>
                </div>
            </td>

            <!-- Col 2: Kelengkapan & Catatan -->
            <td class="spec-cell" style="width: 50%; padding-right: 0;">
                <div class="spec-box" style="margin-bottom: 6px;">
                    <div class="font-bold uppercase" style="font-size: 7pt;">Kelengkapan Unit :</div>
                    <div style="margin-top: 2px; font-size: 7.5pt; line-height: 1.25;">
                        {{ $servis->kelengkapan ?: 'Hanya Unit' }}
                    </div>
                </div>

                <div class="spec-box">
                    <div class="font-bold uppercase" style="font-size: 7pt;">Kondisi Fisik :</div>
                    <div style="margin-top: 2px; font-size: 7.5pt; line-height: 1.25;">
                        {{ $servis->kondisi_barang ?: ($servis->catatan ?: '-') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Signatures Section -->
    <table class="w-full table-layout" style="margin-top: 4px;">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="sign-title">Pelanggan,</div>
                <div>
                    <span class="sign-name">{{ $customerName }}</span>
                </div>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                <div class="sign-title">Hormat kami,</div>
                <div>
                    <span class="sign-name">{{ $pembuatName }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Web Tracking Box Note (Exact User Specification) -->
    <div class="info-box">
        Pengecekan status servis dapat dilakukan di <strong style="color: #0052FF;">www.soulmatekomputer.com</strong> dengan memasukkan kode servis: <strong class="font-mono" style="background: #fff; padding: 1px 4px; border-radius: 2px; border: 1px solid #d1d5db;">{{ $servis->no_servis_frontend ?? $servis->no_servis }}</strong>
    </div>

</body>
</html>
