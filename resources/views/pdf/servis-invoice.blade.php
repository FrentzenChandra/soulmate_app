<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }}</title>
    <style>
        @page {
            margin: 6mm 8mm 6mm 8mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 8.5pt;
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
        .text-muted { color: #555; }

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
            border-top: 1.5px dashed #333;
            border-bottom: 1.5px dashed #333;
            padding: 4px 0;
            margin: 6px 0 6px 0;
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 8.5pt;
            color: #111;
        }

        .spec-bar {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            padding: 4px 8px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 8pt;
        }

        .items-table {
            margin-bottom: 6px;
        }
        .items-table th {
            padding: 4px 6px;
            font-weight: bold;
            font-size: 8pt;
            border-top: 1.5px solid #222;
            border-bottom: 1.5px solid #222;
            background: #fafafa;
        }
        .items-table td {
            padding: 4px 6px;
            font-size: 8pt;
            vertical-align: middle;
            border-bottom: 1px dotted #ccc;
        }

        .sign-title {
            color: #555;
            font-size: 7.5pt;
            margin-bottom: 28px;
        }
        .sign-name {
            border-top: 1px solid #333;
            padding-top: 3px;
            display: inline-block;
            min-width: 140px;
            font-weight: bold;
            font-size: 8pt;
        }

        .totals-table td {
            padding: 2px 4px;
            font-size: 8pt;
        }
        .totals-table .grandtotal-row td {
            border-top: 1.5px solid #222;
            padding-top: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .warranty-box {
            border: 1px solid #10b981;
            background: #ecfdf5;
            padding: 5px 8px;
            border-radius: 4px;
            margin-top: 6px;
            font-size: 7.5pt;
            line-height: 1.3;
        }
    </style>
</head>
<body>
@php
    $cabang = $servis->cabang;
    $customerName = $servis->customer?->nama ?? 'Pelanggan Umum';
    $customerPhone = $servis->customer?->no_hp ?? '-';
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = $cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255';
    $kasirTeknisi = $servis->kasir?->name ?? ($servis->teknisi?->name ?? 'Soulmate Komputer');

    $logoPath = public_path('images/logo_bulat.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

    $tglMasuk = $servis->tanggal_masuk ? $servis->tanggal_masuk->format('d/m/Y') : '-';
    $tglAmbil = $servis->tanggal_ambil ? $servis->tanggal_ambil->format('d/m/Y') : '-';
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
                            <div style="font-size: 7.5pt; font-weight: bold; margin-top: 1px;">Hp/WA: {{ $phone }}</div>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Col 2: Meta Info (48%) -->
            <td style="width: 48%; padding-left: 10px;">
                <table class="w-full" style="font-size: 8pt;">
                    <tr>
                        <td class="text-muted" style="width: 34%;">Tgl Masuk / Ambil</td>
                        <td style="width: 5%;">:</td>
                        <td class="font-bold font-mono">{{ $tglMasuk }} &rarr; {{ $tglAmbil }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pelanggan</td>
                        <td>:</td>
                        <td class="font-bold">{{ $customerName }} <span class="text-muted font-mono" style="font-size: 7.5pt;">({{ $customerPhone }})</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Faktur Servis</td>
                        <td>:</td>
                        <td class="font-bold font-mono" style="font-size: 9pt; color: #5E17EB;">
                            {{ $servis->no_servis_frontend ?? $servis->no_servis }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Title Bar -->
    <div class="divider-title">
        FAKTUR PELUNASAN SERVIS &amp; PENGAMBILAN UNIT
    </div>

    <!-- Unit & PIC Info -->
    <table class="w-full spec-bar table-layout">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <span class="text-muted font-bold uppercase">Spesifikasi Perangkat :</span>
                <strong style="color: #000;">{{ $servis->tipe_unit ?: $servis->kategori_perangkat_label }}</strong>
                @if($servis->serial_number)
                    <span class="text-muted font-mono"> &bull; S/N: {{ $servis->serial_number }}</span>
                @endif
            </td>
            <td style="width: 40%; vertical-align: middle; text-align: right;">
                <span class="text-muted font-bold uppercase">Teknisi / Kasir :</span>
                <strong>{{ $servis->teknisi?->name ?? '-' }} / {{ $servis->kasir?->name ?? 'Kasir Toko' }}</strong>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="w-full items-table table-layout">
        <thead>
            <tr>
                <th class="text-center" style="width: 6%;">No</th>
                <th class="text-left" style="width: 46%;">Rincian Jasa / Sparepart</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 19%;">Harga</th>
                <th class="text-right" style="width: 19%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servis->details as $idx => $det)
                <tr>
                    <td class="text-center font-mono text-muted">{{ $idx + 1 }}</td>
                    <td>
                        <span class="font-bold">{{ $det->nama_item }}</span>
                        <span class="text-muted" style="font-size: 7pt;">({{ ($det->jenis === 'sparepart' || $det->masterServis?->jenis === 'sparepart' || $det->produk_id) ? 'Sparepart' : 'Jasa' }})</span>
                    </td>
                    <td class="text-center font-mono font-bold">{{ $det->qty }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 6px;">Biaya Servis Standar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures & Totals Section -->
    <table class="w-full table-layout" style="margin-top: 4px;">
        <tr>
            <!-- Customer Sign (28%) -->
            <td style="width: 28%; text-align: center; vertical-align: top;">
                <div class="sign-title">Penerima / Pelanggan,</div>
                <div>
                    <span class="sign-name">{{ $customerName }}</span>
                </div>
            </td>

            <!-- Cashier Sign (28%) -->
            <td style="width: 28%; text-align: center; vertical-align: top;">
                <div class="sign-title">Kasir / Teknisi,</div>
                <div>
                    <span class="sign-name">{{ $kasirTeknisi }}</span>
                </div>
            </td>

            <!-- Totals & Payment Summary (44%) -->
            <td style="width: 44%; vertical-align: top; padding-left: 15px;">
                <table class="w-full totals-table">
                    <tr class="grandtotal-row">
                        <td class="text-left font-bold uppercase">TOTAL TAGIHAN</td>
                        <td class="text-right font-mono font-bold" style="color: #5E17EB;">
                            Rp {{ number_format($servis->total_biaya, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($servis->bayar > 0)
                        <tr>
                            <td class="text-left text-muted">Bayar ({{ strtoupper($servis->metode_pembayaran ?: 'Tunai') }})</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($servis->bayar, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-left text-muted">Kembalian</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($servis->kembalian, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Warranty Box -->
    <div class="warranty-box">
        @if($servis->garansi_hari > 0 && $servis->garansi_sampai)
            <div style="font-weight: bold; color: #065f46; text-transform: uppercase;">
                GARANSI TOKO RESMI ({{ $servis->garansi_hari }} HARI)
            </div>
            <div style="margin-top: 1px;">
                Unit ini mendapatkan garansi perbaikan hingga: <strong class="font-mono" style="background: #fff; padding: 1px 4px; border-radius: 2px; border: 1px solid #a7f3d0;">{{ $servis->garansi_sampai->format('d F Y') }}</strong>.
            </div>
            <div class="text-muted" style="margin-top: 1px; font-size: 7pt;">
                Garansi tidak berlaku jika segel rusak, terkena cairan, atau kesalahan pemakaian/kelistrikan pengguna.
            </div>
        @else
            <div style="font-weight: bold; color: #333;">KETENTUAN LAYANAN</div>
            <div class="text-muted" style="margin-top: 1px; font-size: 7pt;">
                Barang yang telah diambil telah dicek dan berfungsi normal di depan pelanggan.
            </div>
        @endif
    </div>

</body>
</html>
