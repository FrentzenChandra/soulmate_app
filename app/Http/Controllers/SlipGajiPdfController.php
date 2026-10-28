<?php

namespace App\Http\Controllers;

use App\Models\SlipGaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SlipGajiPdfController extends Controller
{
    public function exportPdf(Request $request, int $slip)
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.export_pdf')), 403, 'Akses cetak slip gaji dibatasi.');

        $slipGaji = SlipGaji::with(['user', 'cabang', 'items'])->findOrFail($slip);

        // Isolasi Cabang
        if (! $user->isOwner() && (int) $user->cabang_id !== (int) $slipGaji->cabang_id) {
            abort(403, 'Akses slip gaji cabang lain dibatasi.');
        }

        // Hanya slip Final yang boleh dicetak PDF
        if (! $slipGaji->isFinal()) {
            abort(400, 'Hanya slip gaji berstatus Final yang dapat dicetak PDF.');
        }

        $namaStaf = $slipGaji->nama_staf_snapshot ?: ($slipGaji->user ? $slipGaji->user->name : 'Staf');
        $namaSlug = Str::slug($namaStaf);
        $periodeSlug = sprintf('%04d-%02d', $slipGaji->periode_tahun, $slipGaji->periode_bulan);
        $filename = "Slip-Gaji-{$namaSlug}-{$periodeSlug}.pdf";

        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'slip' => $slipGaji,
        ])->setPaper('a4', 'portrait')
          ->setOption('isPhpEnabled', true)
          ->setOption('isRemoteEnabled', true);

        return $request->query('download') === '1'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    public function cetak(Request $request, int $slip)
    {
        $user = Auth::user();
        abort_if(! $user || (! $user->isOwner() && ! $user->can('gaji.export_pdf')), 403, 'Akses cetak slip gaji dibatasi.');

        $slipGaji = SlipGaji::with(['user', 'cabang', 'items'])->findOrFail($slip);

        // Isolasi Cabang
        if (! $user->isOwner() && (int) $user->cabang_id !== (int) $slipGaji->cabang_id) {
            abort(403, 'Akses slip gaji cabang lain dibatasi.');
        }

        // Hanya slip Final yang boleh dicetak
        if (! $slipGaji->isFinal()) {
            abort(400, 'Hanya slip gaji berstatus Final yang dapat dicetak.');
        }

        return view('pdf.slip-gaji-print', [
            'slip' => $slipGaji,
        ]);
    }
}

