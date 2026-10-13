@props(['penjualan'])

@php
    $cabang = $penjualan->cabang;
    $customerName = $penjualan->customer?->nama ?? 'Pelanggan Umum';
    $customerPhone = $penjualan->customer?->no_hp ?? '-';
    $cashierName = $penjualan->kasir?->name ?? 'Kasir';
    $address = $cabang?->alamat ?? 'Jl. Masjid Jamik, Kel. Masjid Jamik, Kec Rangkui, Kota Pangkalpinang';
    $phone = format_whatsapp($cabang?->no_wa ?? $cabang?->no_telp ?? '081995466255');
    $metodeBayar = strtoupper($penjualan->metode_bayar ?? 'CASH');
@endphp

<div id="salesInvoicePrintArea" class="sales-invoice-container max-w-4xl mx-auto bg-white p-5 sm:p-7 rounded-2xl border border-gray-200 shadow-md space-y-1.5 text-black" style="font-family: Arial, Helvetica, sans-serif;">

    <!-- Header: Store Identity & Sales Invoice Info -->
    <div class="grid grid-cols-12 gap-3 items-start ">

        <!-- Left: Logo & Toko -->
        <div class="col-span-7 flex items-start space-x-3.5">
            <img src="{{ asset('images/logo_bulat.png') }}" alt="Logo" class="w-16 h-16 rounded-2xl object-cover shrink-0 shadow-xs">
            <div>
                <h1 class="text-lg  text-black tracking-tight leading-none " style="font-family: Arial, Helvetica, sans-serif;">Soulmate Komputer</h1>
                <p class="text-lg font-medium text-black mt-1.5 leading-snug">
                    {{ $address }}
                </p>
                <p class="text-lg  text-black mt-1">
                    WhatsApp: {{ $phone }}
                </p>
            </div>
        </div>

        <!-- Right: Meta Info (Tanggal, Pelanggan, Pembayaran, No. Penjualan) -->
        <div class="col-span-5 text-xs">
            <table class="w-full">
                <tr>
                    <td class="text-xs  text-black uppercase py-0.5 whitespace-nowrap" style="width: 35%;">Tanggal</td>
                    <td class="px-1.5 text-black " style="width: 5%;">:</td>
                    <td class=" text-black font-mono text-xs py-0.5">{{ $penjualan->tanggal->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td class="text-xs  text-black uppercase py-0.5 whitespace-nowrap">Pelanggan</td>
                    <td class="px-1.5 text-black ">:</td>
                    <td class=" text-black text-xs py-0.5">
                        <span class="truncate block max-w-[200px]" title="{{ $customerName }}">
                            {{ $customerName }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="text-xs  text-black uppercase py-0.5 whitespace-nowrap">Pembayaran</td>
                    <td class="px-1.5 text-black ">:</td>
                    <td class=" uppercase text-xs py-0.5 {{ $penjualan->status === 'bon' ? 'text-amber-700 ' : 'text-black' }}">
                        {{ $penjualan->status === 'bon' ? 'BON (BELUM LUNAS)' : $metodeBayar }}
                    </td>
                </tr>
                <tr>
                    <td class="text-xs  text-black uppercase py-0.5 whitespace-nowrap">NO. PENJUALAN</td>
                    <td class="px-1.5 text-black ">:</td>
                    <td class=" text-[#5E17EB] font-mono text-sm py-0.5">
                        {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}
                    </td>
                </tr>
            </table>
        </div>

    </div>

    <!-- Divider Strip -->
    <div class="border-b-2 border-dashed border-gray-400 text-center tracking-widest text-xs uppercase text-black select-none">
        &middot;&middot;&middot;&middot;&middot; S A L E S &nbsp;&nbsp; I N V O I C E &middot;&middot;&middot;&middot;&middot;
    </div>

    <!-- Items Table -->
    <div class="">
        <table class="w-full text-left border-collapse text-xs table-fixed">
            <colgroup>
                <col style="width: 16%;">
                <col style="width: 38%;">
                <col style="width: 10%;">
                <col style="width: 18%;">
                <col style="width: 18%;">
            </colgroup>
            <thead>
                <tr class="border-b-2 border-black text-xs  uppercase text-black">
                    <th class="pb-1 text-left">Kode Barang</th>
                    <th class="pb-1 text-left">Nama Barang</th>
                    <th class="pb-1 text-center">Jumlah</th>
                    <th class="pb-1 text-right">Harga (Rp)</th>
                    <th class="pb-1 text-right">Total (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dashed divide-gray-200">
                @foreach($penjualan->details as $d)
                    <tr>
                        <td class="py-1 font-mono text-black text-xs align-top truncate pr-2">
                            {{ $d->produk?->barcode ?: 'BRG-' . str_pad((string)$d->produk_id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-1 align-top pr-2">
                            <span class=" text-black block leading-snug">
                                {{ $d->nama_item }}
                            </span>
                        </td>
                        <td class="py-1 text-center font-mono  align-top whitespace-nowrap text-black text-xs">{{ $d->qty }} UNIT</td>
                        <td class="py-1 text-right font-mono align-top whitespace-nowrap text-xs">Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                        <td class="py-1 text-right font-mono  align-top whitespace-nowrap text-black text-xs">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @if($penjualan->details->count() > 1)
                    <tr class="border-t border-gray-400 font-medium text-xs text-black">
                        <td colspan="4" class="py-1 text-right  pr-2">Subtotal :</td>
                        <td class="py-1 text-right font-mono  text-black">Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($penjualan->diskon > 0)
                    <tr class="{{ $penjualan->details->count() <= 1 ? 'border-t border-gray-400 ' : '' }}font-medium text-xs text-black">
                        <td colspan="4" class="py-0.5 text-right  pr-2">Potongan Diskon :</td>
                        <td class="py-0.5 text-right font-mono  text-rose-600">- Rp {{ number_format($penjualan->diskon, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($penjualan->ppn_nominal > 0)
                    <tr class="{{ ($penjualan->details->count() <= 1 && $penjualan->diskon <= 0) ? 'border-t border-gray-400 ' : '' }}font-medium text-xs text-black">
                        <td colspan="4" class="py-0.5 text-right  pr-2">PPN {{ (int)$penjualan->ppn_persen }}% :</td>
                        <td class="py-0.5 text-right font-mono  text-black">Rp {{ number_format($penjualan->ppn_nominal, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="{{ ($penjualan->details->count() <= 1 && $penjualan->diskon <= 0 && $penjualan->ppn_nominal <= 0) ? 'border-t-2 border-black ' : 'border-t-2 border-gray-400 ' }}">
                    <td colspan="4" class="py-2 text-right text-xs sm:text-sm  text-black pr-2">Grandtotal :</td>
                    <td class="py-2 text-right font-mono text-xs sm:text-sm text-[#5E17EB] whitespace-nowrap">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Tanda Tangan Section -->
    <div class="grid grid-cols-2 gap-8 pt-3 text-center text-xs">
        <div>
            <p class="font-medium text-black">Pelanggan,</p>
            <div class="h-11"></div>
            <p class=" text-black border-t border-gray-400 pt-1 inline-block min-w-[140px]">
                {{ $customerName }}
            </p>
        </div>
        <div>
            <p class="font-medium text-black">Kasir Toko,</p>
            <div class="h-11"></div>
            <p class=" text-black border-t border-gray-400 pt-1 inline-block min-w-[140px]">
                {{ $cashierName }}
            </p>
        </div>
    </div>

    <!-- Footer Notes (non aktif) -->
    {{-- <div class="text-center pt-2 pb-0.5 text-xs text-black border-t border-dashed border-gray-300 mt-2">
        <p class="font-medium leading-normal">Terimakasih Telah Berbelanja di Soulmate Komputer &bull; Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa perjanjian sebelumnya</p>
    </div> --}}

</div>
