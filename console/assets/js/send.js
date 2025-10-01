const sendForm  = document.getElementById("send-form")
const spinnerBtn = sendForm.querySelector('#spinner-btn')
const submitBtn = sendForm.querySelector('#submit-btn')

sendForm.addEventListener("submit",(e)=>{
    e.preventDefault()
    spinnerBtn.style.display = "block"
    submitBtn.style.display = "none"
    submitSendForm(sendForm)
})

function submitSendForm(form){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../server/send.php", true);
    
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
              let data = xhr.response;
              if(data === "success"){
                // Get the notification type
                const notificationType = form.querySelector('input[name="notificationType"]:checked').value;
                let successMessage = "Successfully sent ";
                
                if(notificationType === "email"){
                  successMessage += "email notification";
                } else if(notificationType === "sms"){
                  successMessage += "SMS notification";
                } else if(notificationType === "both"){
                  successMessage += "email and SMS notifications";
                }
                
                toastAlert(successMessage)
              }else{
                console.error("Something went wrong")
                console.error(data)
                toastAlert(data,"error")
              }
          }
      }
    }
    let formData = new FormData(form);
    xhr.send(formData);

}

function toastAlert(message,type = "success"){
  if(type){
    spinnerBtn.style.display = "none"
    submitBtn.style.display = "block"
    const toast = document.getElementById(`toast-alert-${type}`)
    toast.classList.add("show")
    toast.querySelector('span').innerText = message

    setTimeout(() => {
      toast.classList.remove("show")
    }, 3000);
  }

}