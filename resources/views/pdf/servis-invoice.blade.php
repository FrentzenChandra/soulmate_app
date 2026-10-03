<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Faktur Servis - {{ $servis->no_servis_frontend ?? $servis->no_servis }}</title>
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
            margin: 6px 0;
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 9pt;
            color: #111;
        }

        .spec-bar {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 8.5pt;
        }

        .items-table {
            margin-bottom: 6px;
        }
        .items-table th {
            padding: 5px 6px;
            font-weight: bold;
            font-size: 8.5pt;
            border-top: 1.5px solid #222;
            border-bottom: 1.5px solid #222;
            background: #fafafa;
        }
        .items-table td {
            padding: 2.5px 5px;
            font-size: 8.5pt;
            vertical-align: middle;
            border-bottom: 1px dotted #ccc;
        }

        .sign-title {
            color: #555;
            font-size: 8pt;
            margin-bottom: 30px;
        }
        .sign-name {
            border-top: 1px solid #333;
            padding-top: 3px;
            display: inline-block;
            min-width: 140px;
            font-weight: bold;
            font-size: 8.5pt;
        }

        .totals-table td {
            padding: 2px 4px;
            font-size: 8.5pt;
        }
        .totals-table .grandtotal-row td {
            border-top: 1.5px solid #222;
            padding-top: 4px;
            font-size: 10pt;
            font-weight: bold;
        }

        .warranty-box {
            border: 1px solid #10b981;
            background: #ecfdf5;
            padding: 6px 8px;
            border-radius: 4px;
            margin-top: 6px;
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
    $kasirTeknisi = $servis->kasir?->name ?? ($servis->teknisi?->name ?? 'Soulmate Komputer');

    $logoPath = public_path('images/logo_bulat.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

    $tglSelesai = $servis->tanggal_selesai ?? ($servis->tanggal_ambil ?? $servis->updated_at);
    $tglSelesaiFormatted = $tglSelesai ? $tglSelesai->format('d/m/Y H:i:s') : '-';
@endphp

    <!-- Header Section -->
    <table class="w-full header-table table-layout" style="margin-bottom: 4px;">
        <tr>
            <!-- Col 1: Logo & Store Info (52%) -->
            <td style="width: 50%;">
                <table>
                    <tr>
                        @if($logoData)
                            <td style="width: 58px; padding-right: 8px; vertical-align: top;">
                                <img src="{{ $logoData }}" style="width: 50px; height: 50px; object-fit: contain;">
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

            <!-- Col 2: Meta Info (50%) -->
            <td style="width: 50%; padding-left: 10px;">
                <table class="w-full" style="font-size: 8pt;">
                    <tr>
                        <td class="text-muted font-bold uppercase" style="width: 40%; font-size: 7.5pt;">Tanggal Selesai</td>
                        <td style="width: 4%;">:</td>
                        <td class="font-bold font-mono">{{ $tglSelesaiFormatted }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-bold uppercase" style="font-size: 7.5pt;">Pelanggan</td>
                        <td>:</td>
                        <td class="font-bold">{{ $customerName }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-bold uppercase" style="font-size: 7.5pt;">NO. FAKTUR SERVIS</td>
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
        FAKTUR SERVIS
    </div>

    <!-- Unit & PIC Info -->
    <table class="w-full spec-bar table-layout">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <span class="text-muted font-bold uppercase">PERANGKAT :</span>
                <strong style="color: #000;">{{ $servis->tipe_unit ?: $servis->kategori_perangkat_label }}</strong>
            </td>
            <td style="width: 40%; vertical-align: middle; text-align: right;">
                <span class="text-muted font-bold uppercase">Petugas Kasir :</span>
                <strong>{{ $servis->kasir?->name ?? ($servis->teknisi?->name ?? 'Soulmate Komputer') }}</strong>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="w-full items-table table-layout">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th class="text-left" style="width: 35%;">Nama Barang / Jasa</th>
                <th class="text-center" style="width: 14%;">Garansi</th>
                <th class="text-center" style="width: 8%;">Qty</th>
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
                    </td>
                    <td class="text-center font-mono" style="font-size: 7.5pt;">
                        {{ $det->garansi_label }}
                    </td>
                    <td class="text-center font-mono font-bold">{{ $det->qty }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($det->harga, 0, ',', '.') }}</td>
                    <td class="text-right font-mono font-bold">Rp {{ number_format($det->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 6px;">Biaya Servis Standar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures & Totals Section -->
    <table class="w-full table-layout" style="margin-top: 4px;">
        <tr>
            <!-- Customer Sign (26%) -->
            <td style="width: 26%; text-align: center; vertical-align: top;">
                <div class="sign-title">Pelanggan,</div>
                <div>
                    <span class="sign-name">{{ $customerName }}</span>
                </div>
            </td>

            <!-- Cashier Sign (26%) -->
            <td style="width: 26%; text-align: center; vertical-align: top;">
                <div class="sign-title">Kasir Toko,</div>
                <div>
                    <span class="sign-name">{{ $kasirTeknisi }}</span>
                </div>
            </td>

            <!-- Totals & Payment Summary (48%) -->
            <td style="width: 48%; vertical-align: top; padding-left: 8px;">
                <table class="w-full totals-table">
                    <tr class="grandtotal-row">
                        <td class="text-left font-bold">Grandtotal</td>
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
        @php
            $hasGaransi = $servis->details->where('garansi_hari', '>', 0)->count() > 0;
        @endphp
        @if($hasGaransi)
            <div style="font-weight: bold; color: #065f46; text-transform: uppercase;">
                KETENTUAN GARANSI PER ITEM
            </div>
            <div style="margin-top: 1px;">
                Garansi berlaku per item sesuai rincian pada tabel di atas terhitung sejak tanggal konfirmasi selesai.
            </div>
            <div class="text-muted" style="margin-top: 1px; font-size: 7pt;">
                Garansi tidak berlaku jika nota hilang, segel rusak, terkena cairan, atau akibat kelalaian pemakaian/kelistrikan.
            </div>
        @else
            <div style="font-weight: bold; color: #333;">KETENTUAN LAYANAN</div>
            <div class="text-muted" style="margin-top: 1px; font-size: 7pt;">
                Barang yang telah diambil telah dicek dan berfungsi normal di depan pelanggan (Non-Garansi).
            </div>
        @endif
    </div>

</body>
</html>
