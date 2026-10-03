<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sales Invoice - {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}</title>
    <style>
        @page {
            size: portrait;
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
            letter-spacing: 3px;
            font-size: 9pt;
            color: #111;
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
            padding: 5px 6px;
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
    </style>
</head>
<body>
@php
    $cabang = $penjualan->cabang;
    $customerName = $penjualan->customer?->nama ?? 'Pelanggan Umum';
    $customerPhone = format_phone($penjualan->customer?->no_hp);
    $cashierName = $penjualan->kasir?->name ?? 'Kasir';
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = format_whatsapp($cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255');
    $metodeBayar = strtoupper($penjualan->metode_bayar ?? 'CASH');

    $logoPath = public_path('images/logo_bulat.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp

    <!-- Header Section -->
    <table class="w-full header-table table-layout" style="margin-bottom: 4px;">
        <tr>
            <!-- Col 1: Logo & Store Info (50%) -->
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
                        <td class="text-muted font-bold uppercase" style="width: 38%; font-size: 7.5pt;">Tanggal</td>
                        <td style="width: 4%;">:</td>
                        <td class="font-bold font-mono">{{ $penjualan->tanggal->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-bold uppercase" style="font-size: 7.5pt;">Pelanggan</td>
                        <td>:</td>
                        <td class="font-bold">{{ $customerName }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-bold uppercase" style="font-size: 7.5pt;">Pembayaran</td>
                        <td>:</td>
                        <td class="font-bold uppercase" style="{{ $penjualan->status === 'bon' ? 'color: #b45309;' : '' }}">{{ $penjualan->status === 'bon' ? 'BON (BELUM LUNAS)' : $metodeBayar }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-bold uppercase" style="font-size: 7.5pt;">NO. PENJUALAN</td>
                        <td>:</td>
                        <td class="font-bold font-mono" style="font-size: 9pt; color: #5E17EB;">
                            {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Title Bar -->
    <div class="divider-title">
        S A L E S &nbsp;&nbsp; I N V O I C E
    </div>

    <!-- Items Table -->
    <table class="w-full items-table table-layout">
        <thead>
            <tr>
                <th class="text-left" style="width: 20%;">Kode Barang</th>
                <th class="text-left" style="width: 44%;">Nama Barang</th>
                <th class="text-center" style="width: 10%;">Jumlah</th>
                <th class="text-right" style="width: 13%;">Harga (Rp)</th>
                <th class="text-right" style="width: 13%;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->details as $d)
                <tr>
                    <td class="font-mono text-muted">
                        {{ $d->produk?->barcode ?: 'BRG-' . str_pad((string)$d->produk_id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <span class="font-bold">{{ $d->nama_item }}</span>
                    </td>
                    <td class="text-center font-bold font-mono">
                        {{ $d->qty }} UNIT
                    </td>
                    <td class="text-right font-mono">
                        {{ number_format($d->harga, 0, ',', '.') }}
                    </td>
                    <td class="text-right font-bold font-mono">
                        {{ number_format($d->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Bottom Section: Customer Sign, Cashier Sign, Totals -->
    <table class="w-full table-layout" style="margin-top: 4px;">
        <tr>
            <!-- Customer Sign (28%) -->
            <td style="width: 28%; text-align: center; vertical-align: top;">
                <div class="sign-title">Customer,</div>
                <div>
                    <span class="sign-name">{{ $customerName }}</span>
                </div>
            </td>

            <!-- Cashier Sign (28%) -->
            <td style="width: 28%; text-align: center; vertical-align: top;">
                <div class="sign-title">Cashier,</div>
                <div>
                    <span class="sign-name">{{ $cashierName }}</span>
                </div>
            </td>

            <!-- Totals Summary (44%) -->
            <td style="width: 44%; vertical-align: top; padding-left: 15px;">
                <table class="w-full totals-table">
                    @if($penjualan->details->count() > 1)
                        <tr>
                            <td class="text-left text-muted">Subtotal</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($penjualan->diskon > 0)
                        <tr>
                            <td class="text-left text-muted">Potongan</td>
                            <td class="text-right font-mono font-bold" style="color: #b91c1c;">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($penjualan->ppn_nominal > 0)
                        <tr>
                            <td class="text-left text-muted">PPN {{ (int)$penjualan->ppn_persen }}%</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($penjualan->ppn_nominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="grandtotal-row">
                        <td class="text-left font-bold">Grandtotal</td>
                        <td class="text-right font-mono font-bold" style="color: #000;">
                            Rp {{ number_format($penjualan->total, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="text-center text-muted" style="margin-top: 6px; border-top: 1px dotted #ccc; padding-top: 3px; font-size: 7pt;">
        Terimakasih Telah Berbelanja di Soulmate Komputer &bull; Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa perjanjian sebelumnya
    </div>

</body>
</html>
