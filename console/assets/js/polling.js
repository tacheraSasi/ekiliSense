/* console.log("works")
function fetchCounts() {
    fetch('server/data.php')
        .then(response => response.json())
        .then(data => {
            console.log(data)
            // document.getElementById('classes-count').innerText = data.classes;
            // document.getElementById('teachers-count').innerText = data.teachers;
        })
        .catch(error => console.error('Error fetching counts:', error));
}

// Fetch counts initially
fetchCounts();

// Polling interval (in milliseconds)
const pollingInterval = 5000; // Example: poll every 5 seconds

// Polling function
setInterval(fetchCounts, pollingInterval); */