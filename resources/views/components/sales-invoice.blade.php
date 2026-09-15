@props(['penjualan'])

@php
    $cabang = $penjualan->cabang;
    $customerName = $penjualan->customer?->nama ?? 'Pelanggan Umum';
    $cashierName = $penjualan->kasir?->name ?? 'Kasir';
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = $cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255';
    $metodeBayar = strtolower($penjualan->metode_bayar ?? 'cash');
@endphp

<div id="salesInvoicePrintArea" class="sales-invoice-container bg-white text-gray-900 font-sans p-6 sm:p-8 max-w-[9.5in] w-full mx-auto rounded-xl border border-gray-200 shadow-sm print:shadow-none print:border-none print:p-0">

    <!-- Top System Header Label -->
    <div class="flex justify-between items-center text-[10px] text-gray-500 font-mono mb-2">
        <span>{{ $penjualan->tanggal->format('m/d/y, h:i A') }}</span>
        <span class="font-bold tracking-wider">SOULMATE KASIR</span>
    </div>

    <!-- Header Grid: Company Info, Meta Col 1, Meta Col 2 -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start pb-3">
        <!-- Left: Logo & Company Address -->
        <div class="md:col-span-6 flex items-start space-x-3">
            <div class="w-12 h-12 rounded-lg border-2 border-gray-900 flex items-center justify-center shrink-0 p-1">
                <img src="{{ asset('images/logo_bulat.jpg') }}" alt="Soulmate Logo" class="w-full h-full object-contain">
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900 tracking-tight leading-none">Soulmate Komputer</h2>
                <p class="text-[11px] text-gray-600 mt-1 leading-snug">{{ $address }}</p>
                <p class="text-[11px] text-gray-800 font-medium mt-0.5">Hp/Telp. {{ $phone }}</p>
            </div>
        </div>

        <!-- Center: Tanggal & No.Transaksi -->
        <div class="md:col-span-3 text-[11px] space-y-1">
            <div class="grid grid-cols-2 gap-1">
                <span class="text-gray-600">Tanggal :</span>
                <span class="font-bold text-gray-900">{{ $penjualan->tanggal->format('d-m-Y') }}</span>
            </div>
            <div class="grid grid-cols-2 gap-1">
                <span></span>
                <span class="font-mono text-gray-700 text-[10px]">{{ $penjualan->tanggal->format('H:i:s') }}</span>
            </div>
            <div class="pt-1">
                <span class="text-gray-600 block">No.Transaksi:</span>
                <span class="font-bold font-mono text-gray-900 text-xs block">{{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}</span>
            </div>
        </div>

        <!-- Right: Pelanggan & Pembayaran -->
        <div class="md:col-span-3 text-[11px] space-y-1">
            <div>
                <span class="text-gray-600 block">Pelanggan :</span>
                <span class="font-bold text-gray-900 block truncate">{{ $customerName }}</span>
            </div>
            <div class="pt-1">
                <span class="text-gray-600 block">Pembayaran :</span>
                <span class="font-bold text-gray-900 uppercase block">{{ $metodeBayar }}</span>
            </div>
        </div>
    </div>

    <!-- Title Bar -->
    <div class="border-t-2 border-b-2 border-dashed border-gray-800 py-1.5 my-3 text-center">
        <h3 class="text-xs sm:text-sm font-bold tracking-[0.25em] text-gray-900 uppercase">
            S A L E S &nbsp; I N V O I C E
        </h3>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-dashed border-gray-800 text-[11px]">
                    <th class="py-2 px-1 text-left font-bold text-gray-900 w-28">Kode barang</th>
                    <th class="py-2 px-2 text-left sm:text-center font-bold text-gray-900">Nama barang</th>
                    <th class="py-2 px-2 text-center font-bold text-gray-900 w-20">Jumlah</th>
                    <th class="py-2 px-2 text-right font-bold text-gray-900 w-28">Harga (Rp.)</th>
                    <th class="py-2 px-1 text-right font-bold text-gray-900 w-28">Total (Rp.)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dashed divide-gray-200">
                @foreach($penjualan->details as $d)
                    <tr class="text-[11px] text-gray-800">
                        <!-- Kode barang -->
                        <td class="py-2.5 px-1 font-mono align-top text-gray-700">
                            {{ $d->produk?->barcode ?: 'BRG-' . str_pad((string)$d->produk_id, 4, '0', STR_PAD_LEFT) }}
                        </td>

                        <!-- Nama barang -->
                        <td class="py-2.5 px-2 align-top text-left sm:text-center">
                            <span class="font-semibold text-gray-900 block leading-snug">{{ $d->nama_item }}</span>
                            @if($d->produk?->deskripsi)
                                <span class="text-[10px] text-gray-500 block leading-tight">{{ Str::limit($d->produk->deskripsi, 60) }}</span>
                            @endif
                        </td>

                        <!-- Jumlah -->
                        <td class="py-2.5 px-2 text-center align-top font-bold whitespace-nowrap text-gray-900">
                            {{ $d->qty }} UNIT
                        </td>

                        <!-- Harga -->
                        <td class="py-2.5 px-2 text-right align-top whitespace-nowrap text-gray-800">
                            {{ number_format($d->harga, 0, ',', '.') }}
                        </td>

                        <!-- Total -->
                        <td class="py-2.5 px-1 text-right align-top font-bold whitespace-nowrap text-gray-900">
                            {{ number_format($d->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Separator Line -->
    <div class="border-b-2 border-dashed border-gray-800 my-3"></div>

    <!-- Bottom Section: Customer Sign, Cashier Sign, Totals Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start pt-2 text-xs">
        <!-- Sign Customer -->
        <div class="sm:col-span-4 text-center flex flex-col justify-between h-28">
            <p class="font-medium text-gray-700 text-[11px]">Customer,</p>
            <p class="font-bold text-gray-900 text-xs border-b border-gray-400 pb-0.5 mx-4 truncate">
                {{ $customerName }}
            </p>
        </div>

        <!-- Sign Cashier -->
        <div class="sm:col-span-4 text-center flex flex-col justify-between h-28">
            <p class="font-medium text-gray-700 text-[11px]">Cashier,</p>
            <p class="font-bold text-gray-900 text-xs border-b border-gray-400 pb-0.5 mx-4 truncate">
                {{ $cashierName }}
            </p>
        </div>

        <!-- Totals Summary (Without Total Pembayaran, Grandtotal only) -->
        <div class="sm:col-span-4 space-y-1.5 text-xs">
            <div class="flex justify-between items-center text-gray-700">
                <span class="font-medium">Subtotal</span>
                <div class="flex items-center space-x-1 font-mono">
                    <span class="text-gray-500 text-[11px]">Rp.</span>
                    <span class="font-bold text-right min-w-[85px] text-gray-900">{{ number_format($penjualan->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="flex justify-between items-center text-gray-700">
                <span class="font-medium">Potongan</span>
                <div class="flex items-center space-x-1 font-mono">
                    <span class="text-gray-500 text-[11px]">{{ $penjualan->diskon > 0 ? 'Rp.' : '' }}</span>
                    <span class="font-bold text-right min-w-[85px] {{ $penjualan->diskon > 0 ? 'text-rose-600' : 'text-gray-900' }}">
                        {{ $penjualan->diskon > 0 ? number_format($penjualan->diskon, 0, ',', '.') : '0' }}
                    </span>
                </div>
            </div>

            <div class="flex justify-between items-center text-gray-700">
                <span class="font-medium">PPN ({{ (int)$penjualan->ppn_persen }}%)</span>
                <div class="flex items-center space-x-1 font-mono">
                    <span class="text-gray-500 text-[11px]">{{ $penjualan->ppn_nominal > 0 ? 'Rp.' : '' }}</span>
                    <span class="font-bold text-right min-w-[85px] text-gray-900">
                        {{ $penjualan->ppn_nominal > 0 ? number_format($penjualan->ppn_nominal, 0, ',', '.') : '0' }}
                    </span>
                </div>
            </div>

            <div class="flex justify-between items-center font-black pt-1.5 border-t border-gray-300 text-gray-900">
                <span class="text-xs uppercase">Grandtotal</span>
                <div class="flex items-center space-x-1 font-mono">
                    <span class="text-[11px]">Rp.</span>
                    <span class="text-sm font-black text-right min-w-[85px] text-gray-900">{{ number_format($penjualan->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Notes -->
    <div class="text-center pt-8 pb-2 space-y-1 border-t border-gray-100 mt-6 text-[11px] text-gray-700">
        <p class="font-medium">Terimakasih Telah Berbelanja di Soulmate Komputer</p>
        <p class="text-[10px] text-gray-500">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa perjanjian sebelumnya</p>
    </div>

</div>
