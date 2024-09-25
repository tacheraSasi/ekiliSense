// const downloadBtn = document.querySelector(".download-mobile")

document.addEventListener("DOMContentLoaded", function () {
    const downloadOptionsDiv = document.getElementById("downloadOptions");
  
    // Functioning to detect the user's platform
    function getPlatform() {
      const userAgent = navigator.userAgent;
  
      if (/windows/i.test(userAgent)) {
        return "windows";
      } else if (/android/i.test(userAgent) || /iphone|ipad|ipod/i.test(userAgent)) {
        return "mobile";
      }
      return "other";
    }
  
    // Creating download options based on platform
    function displayDownloadOptions() {
      const platform = getPlatform();
  
      if (platform === "windows") {
        downloadOptionsDiv.innerHTML = `
          <a href="https://example.com/windows-download" class="btn btn-primary w-100">Download for Windows</a>
        `;
      } else if (platform === "mobile") {
        downloadOptionsDiv.innerHTML = `
          <a href="https://example.com/mobile-download" class="btn btn-primary w-100">Download for Mobile</a>
        `;
      } else {
        downloadOptionsDiv.innerHTML = `
          <p class="text-center ">No download options available for your device.</p>
        `;
      }
    }
  
    // Calling the function to display options
    displayDownloadOptions();
  });
  

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
  