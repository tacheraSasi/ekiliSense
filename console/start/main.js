// Get references to buttons
const downloadMobileButton = document.getElementById("download-mobile");
const downloadWinButton = document.getElementById("download-win");

downloadMobileButton.addEventListener("click", () => {
    if (deferredPrompt) {
        deferredPrompt.prompt(); // Show the install prompt for PWA
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null; // Clear the deferred prompt
        });
    }
});

// Add event listener for Windows download button
downloadWinButton.addEventListener("click", () => {
    // Start download for Windows
    const downloadUrl = "https://init.ekilie.com/win/setup.exe"; // Replace with your actual URL
    const a = document.createElement("a");
    a.href = downloadUrl;
    a.download = "setup.exe"; // This will suggest the filename to save as
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a); // Clean up
});

// Register the service worker (if needed)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then((registration) => {
                console.log('Service Worker registered with scope:', registration.scope);
            })
            .catch((error) => {
                console.error('Service Worker registration failed:', error);
            });
    });
}
