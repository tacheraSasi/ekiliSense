const dn1 = document.querySelectorAll('.dn-1')
const dn2 = document.querySelectorAll('.dn-2')
const card = document.querySelector('.card')


function showDn2(){
    dn1.forEach(element => element.style.display = 'none');
    dn2.forEach(element => element.style.display = 'block');
}


const form = document.querySelector(".login form"),
continueBtn = form.querySelector(".button button"),
errorText = form.querySelector(".error-text");

form.onsubmit = (e)=>{
    e.preventDefault();
}

continueBtn.onclick = ()=>{
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/add-mobile.php", true);
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
              let data = xhr.response;
              if(data === "success"){
                location.href = "../v2/";
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