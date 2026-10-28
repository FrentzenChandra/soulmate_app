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

        // 1. Prioritaskan Render 1:1 via Headless Chromium
        $pdfContent = $this->generateChromiumPdf($slipGaji);

        if ($pdfContent) {
            $disposition = ($request->query('download') === '0' || $request->query('inline') === '1') ? 'inline' : 'attachment';

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                'Content-Length' => strlen($pdfContent),
            ]);
        }

        // 2. Fallback aman ke DomPDF jika Chromium belum aktif di host
        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'slip' => $slipGaji,
        ])->setPaper([0, 0, 595.28, 396.85], 'portrait')
          ->setOption('isPhpEnabled', true)
          ->setOption('isRemoteEnabled', true);

        return $request->query('download') === '1'
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    public function cetak(Request $request, int $slip)
    {
        $request->query->set('download', '1');

        return $this->exportPdf($request, $slip);
    }

    /**
     * Render PDF slip gaji 1:1 identik dengan tampilan /cetak menggunakan Headless Chromium.
     */
    protected function generateChromiumPdf(SlipGaji $slipGaji): ?string
    {
        $chromiumBin = null;
        foreach (['/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'] as $bin) {
            if (file_exists($bin) && is_executable($bin)) {
                $chromiumBin = $bin;
                break;
            }
        }

        if (! $chromiumBin) {
            return null;
        }

        // Render HTML asli dari view slip-gaji-print
        $html = view('pdf.slip-gaji-print', ['slip' => $slipGaji])->render();
        $html = str_replace('window.print();', '', $html);

        // Ambil build CSS Vite dari manifest atau aset
        $cssContent = '';
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest['resources/css/app.css']['file'])) {
                $cssFile = public_path('build/' . $manifest['resources/css/app.css']['file']);
                if (file_exists($cssFile)) {
                    $cssContent = file_get_contents($cssFile);
                }
            }
        }
        if (! $cssContent) {
            $files = glob(public_path('build/assets/app-*.css'));
            if (! empty($files)) {
                $cssContent = file_get_contents($files[0]);
            }
        }

        // Base64 Logo Soulmate Komputer
        $logoPath = public_path('images/logo_icon.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            $html = str_replace(asset('images/logo_icon.png'), $logoBase64, $html);
            $html = str_replace('/images/logo_icon.png', $logoBase64, $html);
        }

        // Inject Inline CSS untuk layout pas 1 halaman ukuran 210mm x 140mm
        $inlineCss = "<style>\n" . $cssContent . "\n" . '
@page {
    size: 210mm 140mm;
    margin: 0;
}
* {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}
html, body {
    width: 210mm !important;
    height: 140mm !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    overflow: hidden !important;
}
.no-print { display: none !important; }
.print-wrapper {
    padding: 0 !important;
    margin: 0 !important;
    width: 210mm !important;
    height: 140mm !important;
    display: block !important;
    box-sizing: border-box !important;
}
.slip-gaji-root {
    width: 204mm !important;
    max-width: 204mm !important;
    margin: 2mm auto 0 auto !important;
    padding: 12px 18px !important;
    box-shadow: none !important;
    border: 1.5px solid #CBD5E1 !important;
    transform: scale(0.74);
    transform-origin: top center;
}
</style>';

        $html = str_replace('</head>', $inlineCss . '</head>', $html);

        $tempHtml = tempnam(sys_get_temp_dir(), 'slip_html_') . '.html';
        $tempPdf = tempnam(sys_get_temp_dir(), 'slip_pdf_') . '.pdf';

        file_put_contents($tempHtml, $html);

        $cmd = escapeshellcmd($chromiumBin) . ' --headless --disable-gpu --no-sandbox --disable-dev-shm-usage --run-all-compositor-stages-before-draw --print-to-pdf-no-header --print-to-pdf=' . escapeshellarg($tempPdf) . ' ' . escapeshellarg($tempHtml) . ' 2>/dev/null';
        exec($cmd, $out, $ret);

        @unlink($tempHtml);

        if ($ret === 0 && file_exists($tempPdf) && filesize($tempPdf) > 1000) {
            $pdfContent = file_get_contents($tempPdf);
            @unlink($tempPdf);

            return $pdfContent;
        }

        if (file_exists($tempPdf)) {
            @unlink($tempPdf);
        }

        return null;
    }
}
