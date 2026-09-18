<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sales Invoice - {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}</title>
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
            margin: 5px 0 6px 0;
            text-align: center;
            font-weight: bold;
            letter-spacing: 3px;
            font-size: 8.5pt;
            color: #111;
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
            min-width: 130px;
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
    </style>
</head>
<body>
@php
    $cabang = $penjualan->cabang;
    $customerName = $penjualan->customer?->nama ?? 'Pelanggan Umum';
    $cashierName = $penjualan->kasir?->name ?? 'Kasir';
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = $cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255';
    $metodeBayar = strtoupper($penjualan->metode_bayar ?? 'CASH');

    $logoPath = public_path('images/logo_bulat.png');
    $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp

    <!-- System Header -->
    <table class="w-full text-muted" style="margin-bottom: 2px; font-size: 7.5pt;">
        <tr>
            <td class="text-left font-mono">{{ $penjualan->tanggal->format('d/m/Y H:i:s') }}</td>
            <td class="text-right font-bold" style="letter-spacing: 1px;">SOULMATE KASIR</td>
        </tr>
    </table>

    <!-- Header Section (3 Columns) -->
    <table class="w-full header-table table-layout" style="margin-bottom: 2px;">
        <tr>
            <!-- Col 1: Logo & Store Info (48%) -->
            <td style="width: 48%;">
                <table>
                    <tr>
                        @if($logoData)
                            <td style="width: 42px; padding-right: 8px; vertical-align: top;">
                                <img src="{{ $logoData }}" style="width: 36px; height: 36px; object-fit: contain;">
                            </td>
                        @endif
                        <td style="vertical-align: top;">
                            <div style="font-size: 11pt; font-weight: bold; color: #111; line-height: 1.1;">Soulmate Komputer</div>
                            <div class="text-muted" style="font-size: 7.5pt; margin-top: 2px;">{{ $address }}</div>
                            <div style="font-size: 7.5pt; font-weight: bold; margin-top: 1px;">Hp/Telp. {{ $phone }}</div>
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Col 2: Date & Invoice No (26%) -->
            <td style="width: 26%; padding-left: 8px; font-size: 8pt;">
                <table class="w-full">
                    <tr>
                        <td class="text-muted" style="width: 48%;">Tanggal</td>
                        <td style="width: 6%;">:</td>
                        <td class="font-bold">{{ $penjualan->tanggal->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jam</td>
                        <td>:</td>
                        <td class="font-mono text-muted" style="font-size: 7.5pt;">{{ $penjualan->tanggal->format('H:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="padding-top: 2px;">No.Trans</td>
                        <td style="padding-top: 2px;">:</td>
                        <td class="font-mono font-bold" style="padding-top: 2px; font-size: 8.5pt;">{{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}</td>
                    </tr>
                </table>
            </td>

            <!-- Col 3: Customer & Payment (26%) -->
            <td style="width: 26%; padding-left: 8px; font-size: 8pt;">
                <table class="w-full">
                    <tr>
                        <td class="text-muted" style="width: 45%;">Pelanggan</td>
                        <td style="width: 6%;">:</td>
                        <td class="font-bold truncate">{{ $customerName }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted" style="padding-top: 2px;">Bayar</td>
                        <td style="padding-top: 2px;">:</td>
                        <td class="font-bold uppercase" style="padding-top: 2px;">{{ $metodeBayar }}</td>
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
                <th class="text-left" style="width: 18%;">Kode barang</th>
                <th class="text-left" style="width: 42%;">Nama barang</th>
                <th class="text-center" style="width: 12%;">Jumlah</th>
                <th class="text-right" style="width: 14%;">Harga (Rp.)</th>
                <th class="text-right" style="width: 14%;">Total (Rp.)</th>
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
                        @if($d->produk?->deskripsi)
                            <div class="text-muted" style="font-size: 7pt;">{{ Str::limit($d->produk->deskripsi, 45) }}</div>
                        @endif
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
                    <tr>
                        <td class="text-left text-muted">Subtotal</td>
                        <td class="text-right font-mono font-bold">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($penjualan->diskon > 0)
                        <tr>
                            <td class="text-left text-muted">Potongan</td>
                            <td class="text-right font-mono font-bold" style="color: #b91c1c;">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($penjualan->ppn_nominal > 0)
                        <tr>
                            <td class="text-left text-muted">PPN ({{ (int)$penjualan->ppn_persen }}%)</td>
                            <td class="text-right font-mono font-bold">Rp {{ number_format($penjualan->ppn_nominal, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="grandtotal-row">
                        <td class="text-left font-bold uppercase">Grandtotal</td>
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
