<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\TransaksiFull;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransaksiFullExportController extends Controller
{
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        if (! $user || (! $user->isOwner() && ! $user->can('transaksi.full.view'))) {
            abort(403, 'Akses modul Transaksi FULL dibatasi.');
        }

        $isOwner = $user->isOwner();
        $isHQ = $isOwner && (session('current_cabang_id') === 'all' || ! session()->has('current_cabang_id'));
        $activeBranchSession = session('current_cabang_id');

        $startDate = $request->query('startDate', '');
        $endDate = $request->query('endDate', '');
        $periodeMode = $request->query('periodeMode', 'range');
        $tipeFilter = $request->query('tipeFilter', 'semua');
        $statusFilter = $request->query('statusFilter', 'semua');
        $metodeFilter = $request->query('metodeFilter', 'semua');
        $ppnFilter = $request->query('ppnFilter', 'semua');
        $cabangFilter = $request->query('cabangFilter', 'semua');
        $search = $request->query('search', '');

        // Query dasar
        $query = TransaksiFull::query();

        // Isolasi Cabang
        if (! $isHQ) {
            $targetCabangId = $isOwner ? (int) $activeBranchSession : (int) $user->cabang_id;
            if ($targetCabangId) {
                $query->where('transaksi_fulls.cabang_id', $targetCabangId);
            }
        } elseif ($cabangFilter !== 'semua' && ! empty($cabangFilter)) {
            $query->where('transaksi_fulls.cabang_id', (int) $cabangFilter);
        }

        // Filter Periode Tanggal
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('transaksi_fulls.tanggal', [$start, $end]);
        }

        // Filter Tipe Transaksi
        if ($tipeFilter !== 'semua' && ! empty($tipeFilter)) {
            $query->where('transaksi_fulls.tipe_transaksi', $tipeFilter);
        }

        // Filter Status Pembayaran
        if ($statusFilter !== 'semua' && ! empty($statusFilter)) {
            $query->where('transaksi_fulls.status', $statusFilter);
        }

        // Filter Metode Pembayaran
        if ($metodeFilter !== 'semua' && ! empty($metodeFilter)) {
            $query->where('transaksi_fulls.metode_pembayaran', $metodeFilter);
        }

        // Filter Pajak PPN
        if ($ppnFilter === 'ppn') {
            $query->where(function ($q) {
                $q->whereHas('details', function ($dq) {
                    $dq->where('ppn_persen', '>', 0)
                        ->orWhere('ppn_nominal', '>', 0)
                        ->orWhereHas('ppn', fn ($pq) => $pq->where('persen', '>', 0));
                })->orWhere(function ($sq) {
                    $sq->whereDoesntHave('details')
                        ->where(function ($ssq) {
                            $ssq->where('transaksi_fulls.ppn_persen', '>', 0)
                                ->orWhere('transaksi_fulls.ppn_nominal', '>', 0);
                        });
                });
            });
        } elseif ($ppnFilter === 'non_ppn') {
            $query->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('details', function ($dq) {
                        $dq->where(function ($ddq) {
                            $ddq->whereNull('ppn_persen')->orWhere('ppn_persen', '<=', 0);
                        })->where(function ($ddq) {
                            $ddq->whereNull('ppn_nominal')->orWhere('ppn_nominal', '<=', 0);
                        })->where(function ($ddq) {
                            $ddq->whereDoesntHave('ppn')
                                ->orWhereHas('ppn', fn ($pq) => $pq->where('persen', '<=', 0));
                        });
                    });
                })->orWhere(function ($sq) {
                    $sq->whereDoesntHave('details')
                        ->where(function ($ssq) {
                            $ssq->whereNull('transaksi_fulls.ppn_persen')->orWhere('transaksi_fulls.ppn_persen', '<=', 0);
                        })->where(function ($ssq) {
                            $ssq->whereNull('transaksi_fulls.ppn_nominal')->orWhere('transaksi_fulls.ppn_nominal', '<=', 0);
                        });
                });
            });
        }

        // Multi-Word Loose Search
        if (! empty($search)) {
            $words = array_filter(explode(' ', trim($search)));
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $pattern = '%' . strtolower($word) . '%';
                    $q->where(function ($sq) use ($pattern) {
                        $sq->whereRaw('LOWER(transaksi_fulls.no_transaksi) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(transaksi_fulls.nama_pelanggan) LIKE ?', [$pattern])
                           ->orWhereRaw('LOWER(COALESCE(transaksi_fulls.no_hp, \'\')) LIKE ?', [$pattern])
                           ->orWhereHas('pegawai', fn ($pq) => $pq->whereRaw('LOWER(nama) LIKE ?', [$pattern]))
                           ->orWhereHas('details', fn ($dq) => $dq->whereRaw('LOWER(nama_item) LIKE ?', [$pattern]));
                    });
                }
            });
        }

        $allData = $query->toBase()
            ->leftJoin('users as u', 'u.id', '=', 'transaksi_fulls.user_id')
            ->leftJoin('users as p', 'p.id', '=', 'transaksi_fulls.pelunas_user_id')
            ->leftJoin('pegawais as pg', 'pg.id', '=', 'transaksi_fulls.pegawai_id')
            ->select([
                'transaksi_fulls.id',
                'transaksi_fulls.no_transaksi',
                'transaksi_fulls.cabang_id',
                'transaksi_fulls.tipe_transaksi',
                'transaksi_fulls.kategori_perangkat',
                'transaksi_fulls.tanggal',
                'transaksi_fulls.tanggal_lunas',
                'transaksi_fulls.nama_pelanggan',
                'transaksi_fulls.no_hp',
                'transaksi_fulls.status',
                'transaksi_fulls.metode_pembayaran_id',
                'transaksi_fulls.metode_pembayaran',
                'transaksi_fulls.catatan',
                'transaksi_fulls.total_modal',
                'transaksi_fulls.total_jual',
                'transaksi_fulls.grand_total',
                'transaksi_fulls.total_laba',
                'transaksi_fulls.ppn_id',
                'transaksi_fulls.ppn_persen',
                'transaksi_fulls.ppn_nominal',
                'transaksi_fulls.user_id',
                'transaksi_fulls.pelunas_user_id',
                'transaksi_fulls.pegawai_id',
                'u.name as user_input_name',
                'p.name as pelunas_name',
                'pg.nama as pegawai_nama',
            ])
            ->orderBy('transaksi_fulls.tanggal', 'desc')
            ->orderByDesc('transaksi_fulls.id')
            ->get();

        $trxIds = $allData->pluck('id')->all();
        $detailsByTrx = [];
        if (! empty($trxIds)) {
            $rawDetails = DB::table('transaksi_full_details as d')
                ->leftJoin('master_ppns as mp', 'mp.id', '=', 'd.ppn_id')
                ->whereIn('d.transaksi_full_id', $trxIds)
                ->select([
                    'd.id',
                    'd.transaksi_full_id',
                    'd.qty',
                    'd.nama_item',
                    'd.harga_modal',
                    'd.harga_jual',
                    'd.subtotal_modal',
                    'd.subtotal_jual',
                    'd.laba',
                    'd.ppn_id',
                    'd.ppn_persen',
                    'd.ppn_nominal',
                    'mp.persen as master_ppn_persen',
                ])
                ->get();

            foreach ($rawDetails as $d) {
                $detailsByTrx[$d->transaksi_full_id][] = $d;
            }
        }

        $processedData = $allData->map(function ($item) use ($detailsByTrx, $ppnFilter) {
            $tglStr = $item->tanggal ? (string) $item->tanggal : '';
            $tglLunasStr = $item->tanggal_lunas ? (string) $item->tanggal_lunas : '';

            $waktuEpoch = $tglStr ? strtotime($tglStr) : 0;
            $waktu = $waktuEpoch ? date('d/m/Y H:i', $waktuEpoch) : '-';

            $pelunasNama = ($item->status === 'Lunas' && ! empty($item->pelunas_user_id) && ! empty($item->pelunas_name)) ? $item->pelunas_name : null;

            $trxDetails = $detailsByTrx[$item->id] ?? [];
            $itemsList = [];
            $filteredSubtotalJual = 0;
            $filteredSubtotalModal = 0;
            $filteredLaba = 0;
            $filteredPpnNominal = 0;

            $parentHasPpn = ((float) $item->ppn_persen > 0 || (float) $item->ppn_nominal > 0);
            $trxHasExplicitPpnDetail = collect($trxDetails)->contains(function ($d) {
                return (float) $d->ppn_persen > 0 || (float) $d->ppn_nominal > 0 || (float) ($d->master_ppn_persen ?? 0) > 0;
            });

            foreach ($trxDetails as $d) {
                $qty = (int) $d->qty;
                $hargaModal = (float) $d->harga_modal;
                $hargaJual = (float) $d->harga_jual;
                $subModal = (float) ($d->subtotal_modal ?: ($hargaModal * $qty));
                $subJual = (float) ($d->subtotal_jual ?: ($hargaJual * $qty));
                $laba = (float) ($d->laba ?: ($subJual - $subModal));
                $ppnPersen = (float) $d->ppn_persen;
                $ppnNominal = (float) $d->ppn_nominal;
                $masterPpnPersen = (float) ($d->master_ppn_persen ?? 0);

                if ($trxHasExplicitPpnDetail) {
                    $itemHasPpn = ($ppnPersen > 0 || $ppnNominal > 0 || $masterPpnPersen > 0);
                } else {
                    $itemHasPpn = $parentHasPpn;
                    if ($itemHasPpn && $ppnPersen <= 0) {
                        $ppnPersen = (float) $item->ppn_persen;
                    }
                }

                if ($ppnFilter === 'ppn' && ! $itemHasPpn) {
                    continue;
                }
                if ($ppnFilter === 'non_ppn' && $itemHasPpn) {
                    continue;
                }

                $effectivePpnRate = $ppnPersen ?: $masterPpnPersen;
                $itemPpnNominal = $itemHasPpn ? ($ppnNominal ?: (($subJual * $effectivePpnRate) / 100)) : 0;

                $filteredSubtotalJual += $subJual;
                $filteredSubtotalModal += $subModal;
                $filteredLaba += $laba;
                $filteredPpnNominal += $itemPpnNominal;

                $itemsList[] = [
                    'qty' => $qty,
                    'nama' => $d->nama_item,
                    'subtotal_modal' => $subModal,
                    'subtotal_jual' => $subJual,
                    'laba' => $laba,
                    'has_ppn' => $itemHasPpn,
                    'pajak_label' => $itemHasPpn ? 'PPN' : 'Non PPN',
                ];
            }

            if ($ppnFilter !== 'semua' && empty($itemsList)) {
                return null;
            }

            $rowTotalJual = ($ppnFilter !== 'semua') ? $filteredSubtotalJual : (float) $item->total_jual;
            $rowTotalModal = ($ppnFilter !== 'semua') ? $filteredSubtotalModal : (float) $item->total_modal;
            $rowTotalLaba = ($ppnFilter !== 'semua') ? $filteredLaba : (float) $item->total_laba;
            $rowGrandTotal = ($ppnFilter === 'ppn') ? ($filteredSubtotalJual + $filteredPpnNominal) : (($ppnFilter === 'non_ppn') ? $filteredSubtotalJual : (float) $item->grand_total);
            $rowHasPpn = ($ppnFilter === 'ppn') ? true : (($ppnFilter === 'non_ppn') ? false : ($parentHasPpn || collect($itemsList)->contains('has_ppn', true)));

            return [
                'id' => (int) $item->id,
                'no_transaksi' => TransaksiFull::formatNoTransaksiFrontend($item->no_transaksi),
                'tipe_transaksi' => $item->tipe_transaksi,
                'waktu' => $waktu,
                'petugas' => $item->pegawai_nama ?: 'Tanpa Pegawai',
                'user_input' => $item->user_input_name ?: 'System',
                'pelunas_nama' => $pelunasNama,
                'pelanggan' => $item->nama_pelanggan,
                'no_hp' => $item->no_hp ?: '-',
                'items_list' => $itemsList,
                'metode_pembayaran' => $item->metode_pembayaran ?: '-',
                'total_modal' => $rowTotalModal,
                'total_jual' => $rowTotalJual,
                'grand_total' => $rowGrandTotal,
                'total_laba' => $rowTotalLaba,
                'has_ppn' => $rowHasPpn,
                'pajak_label' => $rowHasPpn ? 'PPN' : 'Non PPN',
                'status' => $item->status,
                'catatan' => $item->catatan ?: '-',
            ];
        })->filter()->values();

        // Nama Cabang untuk Header PDF
        $cabangNama = 'Semua Cabang (HQ)';
        if (! $isHQ) {
            $c = Cabang::find($user->cabang_id);
            $cabangNama = $c ? $c->nama_cabang : 'Soulmate Komputer';
        } elseif ($cabangFilter !== 'semua' && ! empty($cabangFilter)) {
            $c = Cabang::find($cabangFilter);
            if ($c) $cabangNama = $c->nama_cabang;
        }

        // Hitung Grand Total Akumulasi
        $totalBiayaAll = $processedData->sum('total_jual');
        $totalModalAll = $processedData->sum('total_modal');
        $totalLabaAll = $processedData->sum('total_laba');
        $totalGrandTotalAll = $processedData->sum('grand_total');

        $pdf = Pdf::loadView('pdf.transaksi-full-report', [
            'data' => $processedData,
            'cabangNama' => $cabangNama,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodeText' => ($startDate && $endDate) ? (Carbon::parse($startDate)->format('d/m/Y') . ' s/d ' . Carbon::parse($endDate)->format('d/m/Y')) : 'Semua Periode',
            'tipeFilter' => $tipeFilter,
            'statusFilter' => $statusFilter,
            'ppnFilter' => $ppnFilter,
            'metodeFilter' => $metodeFilter,
            'totalBiayaAll' => $totalBiayaAll,
            'totalModalAll' => $totalModalAll,
            'totalLabaAll' => $totalLabaAll,
            'totalGrandTotalAll' => $totalGrandTotalAll,
            'printedBy' => $user->name,
            'printedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'Laporan_Transaksi_FULL_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }
}
