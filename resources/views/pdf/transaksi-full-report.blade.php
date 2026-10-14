<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi FULL - {{ $cabangNama }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 10mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
        }
        body {
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 8pt;
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

        .header-box {
            border-bottom: 2px solid #5E17EB;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .title-main {
            font-size: 14pt;
            font-weight: bold;
            color: #5E17EB;
            letter-spacing: 0.5px;
        }
        .meta-table {
            width: 100%;
            margin-top: 4px;
            font-size: 7.5pt;
            color: #4b5563;
        }
        .meta-table td {
            padding: 1px 0;
        }

        .summary-cards {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }
        .summary-card {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 4px 6px;
            text-align: center;
        }
        .summary-label {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
        }
        .summary-val {
            font-size: 9.5pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #111827;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #d1d5db;
            text-transform: uppercase;
            font-size: 7pt;
        }
        table.data-table td {
            padding: 4px 4px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        .item-row {
            padding: 1px 0;
            border-bottom: 1px dotted #e5e7eb;
        }
        .item-row:last-child {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-lunas { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-bon { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }
        .badge-penjualan { background: #ede9fe; color: #5b21b6; }
        .badge-servis { background: #fef3c7; color: #92400e; }
        .badge-ppn { background: #dbeafe; color: #1e40af; }
        .badge-nonppn { background: #fce7f3; color: #9d174d; }

        .footer-note {
            margin-top: 8px;
            font-size: 7pt;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <!-- Header Laporan -->
    <div class="header-box">
        <table class="w-full">
            <tr>
                <td style="width: 60%; vertical-align: bottom;">
                    <div class="title-main">LAPORAN TRANSAKSI FULL</div>
                    <div style="font-size: 8.5pt; font-weight: bold; color: #1f2937; margin-top: 2px;">
                        {{ $cabangNama }}
                    </div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: bottom;">
                    <table class="meta-table" style="text-align: right;">
                        <tr>
                            <td><strong>Periode:</strong> {{ $periodeText }}</td>
                        </tr>
                        <tr>
                            <td><strong>Dicetak Oleh:</strong> {{ $printedBy }} ({{ $printedAt }})</td>
                        </tr>
                        <tr>
                            <td><strong>Total Baris:</strong> {{ count($data) }} Transaksi</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- 4 Kotak Ringkasan Finansial Sesuai Data Filter -->
    <table class="summary-cards">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total Omset (Biaya Jual)</div>
                <div class="summary-val" style="color: #5E17EB;">Rp {{ number_format($totalBiayaAll, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total Modal (HPP)</div>
                <div class="summary-val" style="color: #d97706;">Rp {{ number_format($totalModalAll, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total Laba Bersih</div>
                <div class="summary-val" style="color: #059669;">Rp {{ number_format($totalLabaAll, 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Grandtotal Tagihan</div>
                <div class="summary-val" style="color: #0052FF;">Rp {{ number_format($totalGrandTotalAll, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 75px;" class="text-left">No Transaksi</th>
                <th style="width: 70px;" class="text-center">Waktu</th>
                <th style="width: 55px;" class="text-center">Tipe</th>
                <th style="width: 80px;" class="text-left">Pelanggan</th>
                <th style="width: 75px;" class="text-left">Petugas</th>
                <th class="text-left">Rincian Item</th>
                <th style="width: 65px;" class="text-right">Biaya (Rp)</th>
                <th style="width: 65px;" class="text-right">Modal (Rp)</th>
                <th style="width: 65px;" class="text-right">Laba (Rp)</th>
                <th style="width: 45px;" class="text-center">Pajak</th>
                <th style="width: 45px;" class="text-center">Metode</th>
                <th style="width: 45px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-mono font-bold">{{ $row['no_transaksi'] }}</td>
                    <td class="text-center" style="font-size: 7pt;">{{ $row['waktu'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $row['tipe_transaksi'] === 'servis' ? 'badge-servis' : 'badge-penjualan' }}">
                            {{ $row['tipe_transaksi'] }}
                        </span>
                    </td>
                    <td>
                        <div class="font-bold">{{ $row['pelanggan'] }}</div>
                        @if($row['no_hp'] && $row['no_hp'] !== '-')
                            <div style="font-size: 6.5pt; color: #6b7280;">{{ $row['no_hp'] }}</div>
                        @endif
                    </td>
                    <td>
                        <div>{{ $row['petugas'] }}</div>
                        <div style="font-size: 6.5pt; color: #6b7280;">Input: {{ $row['user_input'] }}</div>
                    </td>
                    <td>
                        @if(empty($row['items_list']))
                            <span style="color: #9ca3af; font-style: italic;">Tanpa item</span>
                        @else
                            @foreach($row['items_list'] as $it)
                                <div class="item-row">
                                    <strong>{{ $it['qty'] }}x</strong> {{ $it['nama'] }}
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold" style="color: #5E17EB;">
                        @if(empty($row['items_list']))
                            {{ number_format($row['total_jual'], 0, ',', '.') }}
                        @else
                            @foreach($row['items_list'] as $it)
                                <div class="item-row">{{ number_format($it['subtotal_jual'], 0, ',', '.') }}</div>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-right font-mono" style="color: #d97706;">
                        @if(empty($row['items_list']))
                            {{ number_format($row['total_modal'], 0, ',', '.') }}
                        @else
                            @foreach($row['items_list'] as $it)
                                <div class="item-row">{{ number_format($it['subtotal_modal'], 0, ',', '.') }}</div>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-right font-mono font-bold" style="color: {{ $row['total_laba'] >= 0 ? '#059669' : '#dc2626' }};">
                        @if(empty($row['items_list']))
                            {{ number_format($row['total_laba'], 0, ',', '.') }}
                        @else
                            @foreach($row['items_list'] as $it)
                                <div class="item-row" style="color: {{ $it['laba'] >= 0 ? '#059669' : '#dc2626' }};">
                                    {{ number_format($it['laba'], 0, ',', '.') }}
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-center">
                        @if(empty($row['items_list']))
                            <span class="badge {{ $row['has_ppn'] ? 'badge-ppn' : 'badge-nonppn' }}">{{ $row['pajak_label'] }}</span>
                        @else
                            @foreach($row['items_list'] as $it)
                                <div class="item-row">
                                    <span class="badge {{ $it['has_ppn'] ? 'badge-ppn' : 'badge-nonppn' }}">{{ $it['pajak_label'] }}</span>
                                </div>
                            @endforeach
                        @endif
                    </td>
                    <td class="text-center font-bold" style="font-size: 7pt;">
                        {{ $row['metode_pembayaran'] }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ strtolower($row['status']) === 'lunas' ? 'badge-lunas' : 'badge-bon' }}">
                            {{ $row['status'] }}
                        </span>
                        @if(!empty($row['pelunas_nama']))
                            <div style="font-size: 6pt; color: #047857;">{{ $row['pelunas_nama'] }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 15px; color: #6b7280;">
                        Tidak ada transaksi yang sesuai dengan filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6; font-weight: bold; border-top: 2px solid #9ca3af;">
                <td colspan="7" class="text-right uppercase" style="padding: 6px 4px; font-size: 8pt;">Grandtotal:</td>
                <td class="text-right font-mono" style="color: #5E17EB; font-size: 8pt;">
                    Rp {{ number_format($totalBiayaAll, 0, ',', '.') }}
                </td>
                <td class="text-right font-mono" style="color: #d97706; font-size: 8pt;">
                    Rp {{ number_format($totalModalAll, 0, ',', '.') }}
                </td>
                <td class="text-right font-mono" style="color: #059669; font-size: 8pt;">
                    Rp {{ number_format($totalLabaAll, 0, ',', '.') }}
                </td>
                <td colspan="3" class="text-center font-mono" style="color: #0052FF; font-size: 8pt;">
                    Grandtotal: Rp {{ number_format($totalGrandTotalAll, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <table class="w-full" style="margin-top: 10px; font-size: 7pt; color: #6b7280;">
        <tr>
            <td class="text-left">Soulmate Komputer - Sistem Kasir &amp; Manajemen Operasional</td>
            <td class="text-right">Dokumen Sah Dicetak Otomatis oleh Sistem</td>
        </tr>
    </table>

</body>
</html>
