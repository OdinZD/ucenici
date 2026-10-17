{{-- PWA: instalabilno na Android početni zaslon. Statika je u public/ (vidi public/sw.js);
     manifest je dinamičan (route('manifest')) pa nosi ime kluba iz config('club.name'). --}}
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#2D3E7B">
<link rel="icon" href="/icons/icon-192.png">
<link rel="apple-touch-icon" href="/icons/icon-192.png">
<script>
    // Uhvati Chromeov install event da ga custom "Instaliraj" gumb (login) može okinuti.
    // Okida se samo na Chromium/Androidu kad je instalabilno; nikad na iOS/Firefox.
    window.deferredInstallPrompt = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        window.deferredInstallPrompt = e;
        window.dispatchEvent(new CustomEvent('pwa-installable'));
    });
    window.addEventListener('appinstalled', function () {
        window.deferredInstallPrompt = null;
        window.dispatchEvent(new CustomEvent('pwa-installed'));
    });

    // Registriraj service worker samo preko HTTPS-a (produkcija) da lokalni http dev ostane netaknut.
    if ('serviceWorker' in navigator && location.protocol === 'https:') {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js');
        });
    }
</script>
