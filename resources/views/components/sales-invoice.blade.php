@props(['penjualan'])

@php
    $cabang = $penjualan->cabang;
    $customerName = $penjualan->customer?->nama ?? 'Pelanggan Umum';
    $customerPhone = $penjualan->customer?->no_hp ?? '-';
    $cashierName = $penjualan->kasir?->name ?? 'Kasir';
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = $cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255';
    $metodeBayar = strtoupper($penjualan->metode_bayar ?? 'CASH');
@endphp

<div id="salesInvoicePrintArea" class="sales-invoice-container max-w-3xl mx-auto bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-md space-y-2.5 text-gray-900 text-xs">

    <!-- Header: Store Identity & Sales Invoice Info -->
    <div class="grid grid-cols-12 gap-3 items-start pb-1">

        <!-- Left: Logo & Toko -->
        <div class="col-span-7 flex items-start space-x-3">
            <img src="{{ asset('images/logo_bulat.png') }}" alt="Logo" class="w-14 h-14 rounded-2xl object-cover shrink-0 border border-gray-100 shadow-xs">
            <div>
                <h1 class="text-base font-black text-gray-900 tracking-tight leading-none">Soulmate Komputer</h1>
                <p class="text-[10px] font-medium text-gray-600 mt-1 leading-tight">
                    {{ $address }}
                </p>
                <p class="text-[10px] font-semibold text-gray-700 mt-0.5">
                    WhatsApp: {{ $phone }}
                </p>
            </div>
        </div>

        <!-- Right: Meta Info (Tanggal, Pelanggan, Pembayaran, No. Penjualan) -->
        <div class="col-span-5 text-[11px]">
            <table class="w-full">
                <tr>
                    <td class="text-[9px] font-bold text-gray-500 uppercase py-0.5 whitespace-nowrap">Tanggal</td>
                    <td class="px-1 text-gray-400 font-bold">:</td>
                    <td class="font-bold text-gray-800 font-mono text-[10px] py-0.5">{{ $penjualan->tanggal->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td class="text-[9px] font-bold text-gray-500 uppercase py-0.5 whitespace-nowrap">Pelanggan</td>
                    <td class="px-1 text-gray-400 font-bold">:</td>
                    <td class="font-bold text-gray-900 text-[10px] py-0.5">
                        <span class="truncate block max-w-[170px]" title="{{ $customerName }}">
                            {{ $customerName }}
                        </span>
                        @if($customerPhone && $customerPhone !== '-')
                            <span class="text-[10px] font-mono text-gray-600 block">{{ $customerPhone }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-[9px] font-bold text-gray-500 uppercase py-0.5 whitespace-nowrap">Pembayaran</td>
                    <td class="px-1 text-gray-400 font-bold">:</td>
                    <td class="font-bold uppercase text-[10px] py-0.5 {{ $penjualan->status === 'bon' ? 'text-amber-700 font-black' : 'text-gray-900' }}">
                        {{ $penjualan->status === 'bon' ? 'BON (BELUM LUNAS)' : $metodeBayar }}
                    </td>
                </tr>
                <tr>
                    <td class="text-[9px] font-bold text-gray-500 uppercase py-0.5 whitespace-nowrap">NO. PENJUALAN</td>
                    <td class="px-1 text-gray-400 font-bold">:</td>
                    <td class="font-black text-[#5E17EB] font-mono text-xs py-0.5">
                        {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}
                    </td>
                </tr>
            </table>
        </div>

    </div>

    <!-- Divider Strip -->
    <div class="border-t border-b border-dashed border-gray-400 py-0.5 text-center font-bold tracking-widest text-[10px] uppercase text-gray-700 select-none">
        &middot;&middot;&middot;&middot;&middot; S A L E S &nbsp; I N V O I C E &middot;&middot;&middot;&middot;&middot;
    </div>

    <!-- Items Table -->
    <div>
        <table class="w-full text-left border-collapse text-[11px]">
            <thead>
                <tr class="border-b border-gray-800 text-[10px] font-bold uppercase text-gray-700">
                    <th class="py-1 text-left w-24">Kode Barang</th>
                    <th class="py-1 text-left">Nama Barang</th>
                    <th class="py-1 text-center w-16">Jumlah</th>
                    <th class="py-1 text-right w-24">Harga (Rp)</th>
                    <th class="py-1 text-right w-28">Total (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($penjualan->details as $d)
                    <tr>
                        <td class="py-1 font-mono text-gray-600 text-[10px] align-top">
                            {{ $d->produk?->barcode ?: 'BRG-' . str_pad((string)$d->produk_id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-1 align-top">
                            <span class="font-semibold text-gray-900 block leading-snug">
                                {{ $d->nama_item }}
                            </span>
                        </td>
                        <td class="py-1 text-center font-mono font-bold align-top whitespace-nowrap text-gray-900">{{ $d->qty }} UNIT</td>
                        <td class="py-1 text-right font-mono align-top whitespace-nowrap">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                        <td class="py-1 text-right font-mono font-bold align-top whitespace-nowrap text-gray-900">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-800 font-bold text-xs">
                    <td colspan="4" class="py-1 text-right uppercase">GRANDTOTAL :</td>
                    <td class="py-1 text-right font-mono text-[#5E17EB]">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                </tr>
                @if($penjualan->diskon > 0)
                    <tr class="font-medium text-[10px] text-gray-600">
                        <td colspan="4" class="py-0.5 text-right">Potongan Diskon :</td>
                        <td class="py-0.5 text-right font-mono text-rose-600">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($penjualan->ppn_nominal > 0)
                    <tr class="font-medium text-[10px] text-gray-600">
                        <td colspan="4" class="py-0.5 text-right">PPN ({{ (int)$penjualan->ppn_persen }}%) :</td>
                        <td class="py-0.5 text-right font-mono">Rp {{ number_format($penjualan->ppn_nominal, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>

    <!-- Tanda Tangan Section -->
    <div class="grid grid-cols-2 gap-6 pt-2 text-center text-[10px]">
        <div>
            <p class="font-medium text-gray-500">Pelanggan,</p>
            <div class="h-6"></div>
            <p class="font-bold text-gray-900 border-t border-gray-300 pt-0.5 inline-block min-w-[120px]">
                {{ $customerName }}
            </p>
        </div>
        <div>
            <p class="font-medium text-gray-500">Kasir Toko,</p>
            <div class="h-6"></div>
            <p class="font-bold text-gray-900 border-t border-gray-300 pt-0.5 inline-block min-w-[120px]">
                {{ $cashierName }}
            </p>
        </div>
    </div>

    <!-- Footer Notes -->
    <div class="text-center pt-2 text-[10px] text-gray-600 border-t border-dashed border-gray-200 mt-2">
        <p class="font-medium">Terimakasih Telah Berbelanja di Soulmate Komputer &bull; Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa perjanjian sebelumnya</p>
    </div>

</div>
