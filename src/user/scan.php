<?php
$page_title = "Scan QR Code";
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Sistem Absensi QR'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style> 
      body { font-family: 'Inter', sans-serif; } 
      .toastify.on { opacity: 1 !important; }
      #reader video { width: 100%; height: 100%; object-fit: cover; }
      #reader__dashboard_section_csr button { background-color: #4f46e5 !important; color: white !important; font-weight: bold; }
      #reader__dashboard_section_swaplink { display: none !important; }
      
      .scanner-laser {
          position: absolute;
          left: 0;
          width: 100%;
          height: 3px;
          background-color: #3b82f6;
          box-shadow: 0 0 10px #3b82f6, 0 0 20px #3b82f6;
          animation: scan 3s linear infinite;
          display: none;
      }
      @keyframes scan {
          0% { top: 0; }
          50% { top: calc(100% - 3px); }
          100% { top: 0; }
      }
    </style>
</head>
<body class="bg-slate-900">

<div class="relative min-h-screen flex flex-col items-center justify-center p-4">
    <a href="index.php" class="absolute top-4 left-4 z-20 bg-white/20 backdrop-blur-sm text-white py-2 px-4 rounded-lg hover:bg-white/30 transition-colors">
        &larr; Kembali
    </a>

    <div class="w-full max-w-md text-center">
        <h1 class="text-2xl font-bold text-white mb-2">Pindai QR Code</h1>
        <p class="text-slate-400 mb-4">Posisikan QR code di dalam area pemindaian.</p>

        <div id="reader-container" class="w-full bg-black rounded-xl overflow-hidden shadow-2xl border-4 border-slate-700 transition-all duration-300">
            <div id="reader" class="relative">
                <div class="scanner-laser"></div>
            </div>
        </div>
        <div id="result" class="mt-4 text-center font-medium"></div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    const resultContainer = document.getElementById('result');
    const readerContainer = document.getElementById('reader-container');
    const laser = document.querySelector('.scanner-laser');
    let isProcessing = false;

    function showNotification(message, type = 'success') {
        const borderLeftColor = type === 'success' ? '#28a745' : '#dc3545';
        Toastify({ // Pastikan 'T' besar
            text: message, duration: 2500, close: true, gravity: "bottom", position: "right", stopOnFocus: true,
            style: { background: '#ffffff', color: '#333333', borderRadius: '8px', borderLeft: `5px solid ${borderLeftColor}`, boxShadow: '0 3px 6px -1px rgba(0, 0, 0, 0.12), 0 10px 36px -4px rgba(77, 96, 232, 0.15)' },
            offset: { x: 20, y: 20 }
        }).showToast();
    }

    async function processQRCode(token) {
        try {
            const response = await fetch('proses_absensi.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token: token }) });
            const result = await response.json();
            if (result.success) {
                showNotification('Absensi berhasil dicatat!', 'success');
                setTimeout(() => { window.location.href = 'index.php'; }, 1500);
            } else {
                showNotification(result.message, 'error');
                readerContainer.classList.add('border-red-500');
                setTimeout(() => { 
                    isProcessing = false; 
                    try { if(html5QrcodeScanner.getState() === Html5QrcodeScannerState.PAUSED) html5QrcodeScanner.resume(); } catch(e){}
                    readerContainer.classList.remove('border-red-500');
                }, 3000);
            }
        } catch (error) {
            showNotification('Terjadi kesalahan. Coba lagi.', 'error');
            setTimeout(() => { isProcessing = false; try { if(html5QrcodeScanner.getState() === Html5QrcodeScannerState.PAUSED) html5QrcodeScanner.resume(); } catch(e){} }, 3000);
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        isProcessing = true;
        
        html5QrcodeScanner.pause();
        readerContainer.classList.remove('border-slate-700');
        readerContainer.classList.add('border-green-500');
        laser.style.display = 'none';
        
        processQRCode(decodedText);
    }

    function onScanFailure(error) { /* Abaikan */ }

    const qrboxFunction = (viewfinderWidth, viewfinderHeight) => {
        let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
        let qrboxSize = Math.floor(minEdgeSize * 0.7);
        return { width: qrboxSize, height: qrboxSize };
    }

    let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
        fps: 10,
        qrbox: qrboxFunction,
        rememberLastUsedCamera: true, 
        defaultCamera: "environment"
    }, false);

    function startScanner() {
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        const videoCheckInterval = setInterval(() => {
            const videoElement = document.querySelector('#reader video');
            if (videoElement && videoElement.readyState >= 3) {
                laser.style.display = 'block';
                clearInterval(videoCheckInterval);
            }
        }, 500);
    }

    startScanner();
</script>
</body>
</html>