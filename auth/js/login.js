const form = document.querySelector(".login form"),
continueBtn = form.querySelector(".button button"),
errorText = form.querySelector(".error-text");

form.onsubmit = (e)=>{
    e.preventDefault();
    loadingState(continueBtn,"Sign in",true)
}

continueBtn.onclick = ()=>{
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/auth/server/login.php", true);
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
              let data = xhr.response;
              if(data === "success"){
                loadingState(continueBtn,"Sign in",false)
                location.href = "../console/";
              }else{
                errorText.style.display = "block";
                errorText.textContent = data;
                loadingState(continueBtn,"Sign in",false)
              }
          }
      }
    }
    let formData = new FormData(form);
    console.log(formData)
    xhr.send(formData);
}

function loadingState(button,initialtext,isLoading) {
  if(isLoading){
    console.log(isLoading)
    button.innerHTML = `<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span> loading...`
  }else{
    console.log(isLoading)
    button.innerHTML = initialtext
  }
}