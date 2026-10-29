<?php

namespace App\Http\Controllers;

use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return response()->view('pdf.slip-gaji-print', [
            'slip' => $slipGaji,
            'autoPrint' => $request->boolean('auto_print', true),
        ]);
    }

    public function cetak(Request $request, int $slip)
    {
        $request->query->set('auto_print', '0');

        return $this->exportPdf($request, $slip);
    }

}
