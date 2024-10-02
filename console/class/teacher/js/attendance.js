const markAttendance = document.querySelectorAll('.attendance-mark')
// console.log(markAttendance)

markAttendance.forEach(mark => {
  console.log(mark)
  mark.addEventListener("submit",(event)=>{
    console.log("mark")
    event.preventDefault()
    submitForm(mark)
  })
})

const submitForm = (form)=>{
    continueBtn = form.querySelector("button");

    //submitting the formData form each form
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/attendance.php", true);
    xhr.onload = ()=>{
      if(xhr.readyState === XMLHttpRequest.DONE){
          if(xhr.status === 200){
              let data = xhr.response;
              if(data === "success"){
                //TODO:some logic
                if(form.id == "all"){
                  markAttendance.forEach(mark=>{
                    mark.querySelector("button").querySelector("span").innerText = "Marked"
                    mark.querySelector("button").style.backgroundColor = "var(--btn-bg)" 
                  })

                }else{
                  continueBtn.querySelector("span").innerText = "Marked"
                  continueBtn.style.backgroundColor = "var(--btn-bg)" 
                }
              }else{
                alert("Something went wrong!")
                console.error("Something went wrong")
                console.error(data)
                //TODO:add some sort of a toast notification for the error handling 
              }
          }
      }
    }
    let formData = new FormData(form);
    xhr.send(formData);
}

