const modalForm = document.querySelectorAll('.modal-form');
const addLeft = document.querySelectorAll('.add .left');


let i = 1;
function getRandomImage(){
     i = Math.floor(Math.random()*35)
     /* 
     setInterval(getRandomImage,5000) */

     if(i <= 0){
      i = 1;
     }
    //  console.log(i)
}

addLeft.forEach(card=>{
    getRandomImage()
    card.style.background = `linear-gradient(rgba(87, 165, 120, 0.5),
    rgba(9, 66, 77, 0.8)), url("../assets/img/random/img${i}.jpg") center`;
    card.style.backgroundSize = "cover"
    
})

//function that submits the form to the server 
const submitForm = (form, id)=>{
    continueBtn = form.querySelector(".button button"),
    errorText = form.querySelector(".error-text");
    inputs = form.querySelectorAll(".plan-input")
    console.log("form submitted")

    //submitting the formData form each form
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../server/add.php", true);
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
            let data = xhr.response;
            console.log(data)
            if(data === "success"){
              inputs.forEach(input=>{
                input.value = "";
              })
              errorText.style.background = "#7df3598f";
              errorText.style.border = "1px solid #9bff7c8f";
              errorText.style.display = "block";
              errorText.textContent = displaySuccessText(id);
            }else{
              errorText.style.display = "block";
              errorText.textContent = data;
            }
          }
      }
    }
    let formData = new FormData(form);
    console.log(formData)
    xhr.send(formData);
}

modalForm.forEach(form =>{
  
    form.addEventListener("submit",(e)=>{
        e.preventDefault()
        console.log("id:",e.target.id)
        submitForm(form,e.target.id)
    })
})

function displaySuccessText(id) {
  switch(id){
    case "plan":
      return "Plan was created successfully.";
    default:
      return"Task was successfully.";
  }
}

//displaying the users plans
function fetchPlans() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "../server/get_plans.php", true);
  xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
          
          var plansContent = xhr.responseText;
          
          document.querySelector("#plans-list").innerHTML = plansContent;
      }
  };
  xhr.send();
}

// Call fetchPlans() function to fetch and display plans when the page loads
setInterval(()=>{
fetchPlans()
},500)


