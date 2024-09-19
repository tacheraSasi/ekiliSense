const stuffAttendance = (sumbitTo)=>{
    console.log("connected")
    const officeLatitude = -6.832128;
    const officeLongitude = 39.23968;
    const radius = 100; // in meters
    const modalForm = document.querySelectorAll('.modal-form');
    const signBtn = document.getElementById('sign')
    console.log(signBtn.textContent)

    modalForm.forEach(form => {
        form.addEventListener("submit", (e) => {
          e.preventDefault();
        //   submitForm(form, e.target.id);
        });
    });

    if (navigator.geolocation) {
        loadingState(signBtn,"PROCEED",true)
        navigator.geolocation.getCurrentPosition(function(position) {
            console.log("position",position)
            const userLatitude = position.coords.latitude;
            const userLongitude = position.coords.longitude;
            const distance = calculateDistance(userLatitude, userLongitude, officeLatitude, officeLongitude);
            const errorText = document.querySelector('.error-text');
            const distanceInKm = Math.floor(distance/1000)
        
            if (distance <= radius) {
                // Clear error message
                errorText.style.display = 'none';
                document.getElementById('latitude').value = userLatitude;
                document.getElementById('longitude').value = userLongitude;
            
                const formData = new FormData(document.getElementById('sign-attendance'));
                console.log(formData)
            
                // AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', sumbitTo, true);
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
                signBtn.style.display = 'none';
                errorText.innerHTML = `You are <b>${distanceInKm}km</b> from the office. <br>You need to be within 100m of the office to sign in.`;
            }
        }, function(error) {
            loadingState(signBtn,"PROCEED",false)
            console.error('Error getting location:', error);
            
        });
    } else {
        loadingState(signBtn,"PROCEED",false)
        alert('Geolocation is not supported by this browser.');
    }


    //loading function
    function loadingState(button,initialtext,isLoading) {
        if(isLoading){
          console.log(isLoading)
          button.innerHTML = `<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>`
        }else{
          console.log(isLoading)
          button.innerHTML = initialtext
          console.log(button.textContent)
        }
    }

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
}



