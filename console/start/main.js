// Get references to buttons
const downloadMobileButton = document.getElementById("download-mobile");
const downloadWinButton = document.getElementById("download-win");

// Variable to hold the deferred prompt
let deferredPrompt;

// Listen for the 'beforeinstallprompt' event
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-info bar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    console.log('A2HS event fired');
});

// Handle mobile download button click
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
    } else {
        console.log('No deferred prompt available'); // Log when there's no prompt
    }
});

// Add event listener for Windows download button
downloadWinButton.addEventListener("click", () => {
    // Start download for Windows
    const downloadUrl = "https://init.ekilie.com/win/setup.exe"; // URL for setup.exe
    const a = document.createElement("a");
    a.href = downloadUrl;
    a.download = "setup.exe"; // Suggests the filename for download
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a); // Clean up after the click
});

// Register the service worker (if needed)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./service-worker.js')
            .then((registration) => {
                console.log('Service Worker registered with scope:', registration.scope);
            })
            .catch((error) => {
                console.error('Service Worker registration failed:', error);
            });
    });
}
