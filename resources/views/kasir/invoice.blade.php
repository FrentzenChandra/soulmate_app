<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Invoice - {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }} - Soulmate Komputer</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo_bulat.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .sales-invoice-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 9.5in !important;
                max-width: 9.5in !important;
            }
            @page {
                size: 9.5in 11in;
                margin: 6mm 10mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4 font-sans antialiased text-gray-900 selection:bg-purple-500 selection:text-white">
    
    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="{{ route('kasir.produk') }}" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-all inline-flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali ke POS</span>
            </a>
            <span class="text-xs font-mono text-gray-500 font-semibold">| {{ $penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <button id="btnSavePdf" onclick="saveInvoiceAsPdf()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-900/20 inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span id="txtSavePdf">Simpan PDF (9.5 &times; 11 Inci)</span>
            </button>
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold transition-all shadow-md inline-flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak Print</span>
            </button>
        </div>
    </div>

    <!-- Reusable Invoice Template -->
    <x-sales-invoice :penjualan="$penjualan" />

    <!-- html2pdf.js Bundle for direct PDF rendering & auto-saving -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        function saveInvoiceAsPdf() {
            const btn = document.getElementById('btnSavePdf');
            const txt = document.getElementById('txtSavePdf');
            const element = document.getElementById('salesInvoicePrintArea') || document.querySelector('.sales-invoice-container');

            if (!element) return;

            if (txt) txt.textContent = 'Membuat PDF...';
            if (btn) btn.disabled = true;

            const opt = {
                margin:       [0.2, 0.25, 0.2, 0.25],
                filename:     'Sales_Invoice_{{ Str::slug($penjualan->no_transaksi_frontend ?? $penjualan->no_transaksi) }}.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
                jsPDF:        { unit: 'in', format: [9.5, 11], orientation: 'portrait' }
            };

            return html2pdf().set(opt).from(element).save().then(() => {
                if (txt) txt.innerHTML = 'Simpan PDF (9.5 &times; 11 Inci)';
                if (btn) btn.disabled = false;
            }).catch(err => {
                console.error('PDF export error:', err);
                if (txt) txt.innerHTML = 'Simpan PDF (9.5 &times; 11 Inci)';
                if (btn) btn.disabled = false;
            });
        }

        // Auto Save on load if requested via query param ?autosave=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autosave') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    saveInvoiceAsPdf();
                }, 400);
            });
        } else if (urlParams.get('autoprint') === '1') {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>
</body>
</html>
