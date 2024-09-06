const form = document.querySelector(".onboard form"),
continueBtn = form.querySelector(".button button"),
errorText = form.querySelector(".error-text");
const proceedBtn = document.querySelector('.proceed');
const card1 = document.getElementById('card-1');
const finishBtn = document.getElementById('finish');
const redirectContainer = document.querySelector('.redirect-container')
let loadingText = 'creating...';

let isError = false;


let index = 8;

form.addEventListener('submit', (e) => {
    e.preventDefault();

});
proceedBtn.addEventListener('click',()=>{
    function loadingAnimation(isError) {
        if (!isError) {
            proceedBtn.innerHTML = loadingText.substring(0, index) + '.'; 
            index++;
            if (index > loadingText.length) {
                index = 8; 
            }
            setTimeout(loadingAnimation, 500);
        }
    }
    loadingAnimation(isError);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/join.php", true);
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
              let data = xhr.response;
              if(data === "success"){
                showCard()
              }else{
                errorText.style.display = "block";
                errorText.textContent = data;
                isError = true; 
                clearTimeout(loadingAnimation);
                proceedBtn.innerHTML = 'Try again';//Fix this 
              }
          }
      }
    }
    let formData = new FormData(form);
    console.log(formData)
    xhr.send(formData);
})

const showCard=()=>{
    card1.style.display = 'none'
    document.getElementById('card-2').style.display = 'flex'
}

document.querySelector('#card-2').style.flexDirection='row'

finishBtn.addEventListener('click',()=>{
    redirectContainer.style.display = 'block'
    let redirectDiv = document.querySelector('#redirect')
    let redirectText = redirectDiv.innerText
    finishBtn.style.display = 'none'

    let index2 = 11
    function loadingAnimation2() {
        redirectDiv.innerHTML = redirectText.substring(0, index2) + '.'; 
        index2++;
        if (index2 > redirectText.length) {
            index2 = 11; 
        }
        setTimeout(loadingAnimation2, 500);
    }
    loadingAnimation2();

    setTimeout(()=>{window.location.href='../console/?u=new'},3000)
})