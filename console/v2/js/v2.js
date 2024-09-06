const dn1 = document.querySelectorAll('.dn-1')
const dn2 = document.querySelectorAll('.dn-2')
const card = document.querySelector('.card')


function showDn2(){
    dn1.forEach(element => element.style.display = 'none');
    dn2.forEach(element => element.style.display = 'block');
}


fetch("https://restcountries.com/v3.1/all")
  .then(response => response.json())
  .then(data => {
    const countries = data.map(country => country.name.common);
    countries.sort(); 
    const selectElement = document.getElementById("countrySelect");
    
    countries.forEach(country => {
      const option = document.createElement("option");
      option.text = country;
      option.value = country;
      selectElement.appendChild(option);
    });
  })
  .catch(error => {
    console.error('Error:', error);
  });



  const form = document.querySelector(".login form"),
  continueBtn = form.querySelector(".button button"),
  errorText = form.querySelector(".error-text");
  
  form.onsubmit = (e)=>{
      e.preventDefault();
  }
  
  continueBtn.onclick = ()=>{
      let xhr = new XMLHttpRequest();
      xhr.open("POST", "server/add-country.php", true);
      xhr.onload = ()=>{
        if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
                let data = xhr.response;
                if(data === "success"){
                  location.href = "../";
                }else{
                  errorText.style.display = "block";
                  errorText.textContent = data;
                }
            }
        }
      }
      let formData = new FormData(form);
      xhr.send(formData);
  }