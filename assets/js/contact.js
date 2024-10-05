import EkiliRelay from "./ekiliRelay.js"

const emailForm = document.getElementById("email-contact-form")
let emailFormData = new FormData(emailForm) 
const apiKey = emailFormData.get("ekilirelay-apikey")


const mailer = new EkiliRelay(apiKey)

emailForm.addEventListener("submit",(event)=>{
    event.preventDefault()
    let to  = "support@ekilie.com"
    let email  = emailFormData.get("email")
    let subject = emailFormData.get("subject")
    let name = emailFormData.get("name")
    let message = emailFormData.get("message")
    let headers = `From: ${name} <${email}>`
    console.log(email,subject,name)
    mailer.sendEmail(
        to,
        subject,
        message,
        headers
    ).then(
        response=>{
            if (response.status === "success") {
                console.log('Email sent successfully.');
                emailForm.querySelector(".sent-message").style.display = "block"
            }else{
                //some mechanism to send me notification that something failed
                console.log('Failed to send email: ' + response.message);
                console.log(response);
            }
        }
    ).catch(error => {
        console.log('Error:', error);
    });
})