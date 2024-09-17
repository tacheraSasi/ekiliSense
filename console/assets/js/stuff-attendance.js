const officeLatitude = -6.832128;
const officeLongitude = 39.23968;
const radius = 100; // in meters
const modalForm = document.querySelectorAll('.modal-form');
const signBtn = document.getElementById('sign')
// console.log(signBtn)

modalForm.forEach(form => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
    //   submitForm(form, e.target.id);
    });
});

signBtn.addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const userLatitude = position.coords.latitude;
            const userLongitude = position.coords.longitude;
            const distance = calculateDistance(userLatitude, userLongitude, officeLatitude, officeLongitude);
            const errorText = document.querySelector('.error-text');

            if (distance <= radius) {
                // Clear error message
                errorText.style.display = 'none';
                
                document.getElementById('latitude').value = userLatitude;
                document.getElementById('longitude').value = userLongitude;

                const formData = new FormData(document.getElementById('sign-attendance'));
                console.log(formData)

                // AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'your-php-server-endpoint.php', true);
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        console.log('Form submitted successfully!');
                        // Handle success (e.g., show a message to the user)
                    } else {
                        console.error('Form submission failed.');
                        // Handle error
                    }
                };
                xhr.send(formData);
            } else {
                // Show error message
                errorText.style.display = 'block';
                errorText.textContent = 'You need to be within 100m of the office to sign in.';
            }
        }, function(error) {
            console.error('Error getting location:', error);
            // Handle error (e.g., show a message to the user)
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
});

function calculateDistance(lat1, lon1, lat2, lon2) {
    const toRad = (value) => value * Math.PI / 180;
    const R = 6371e3; // Earth radius in meters
    const φ1 = toRad(lat1);
    const φ2 = toRad(lat2);
    const Δφ = toRad(lat2 - lat1);
    const Δλ = toRad(lon2 - lon1);

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}