const modalForm = document.querySelectorAll('.modal-form');
const addLeft = document.querySelectorAll('.add .left');

const numberOfImages = 35;

function getRandomImage() {
  return Math.floor(Math.random() * numberOfImages + 1);
}

addLeft.forEach(card => {
  const i = getRandomImage();
  card.style.background = `linear-gradient(rgba(87, 165, 120, 0.5),
    rgba(9, 66, 77, 0.8)), url("${assetsAt}/img/random/img${i}.jpg") center`;
  card.style.backgroundSize = "cover";
});

const submitForm = (form, id) => {
  if(id === "sign-attendance"){
    stuffSignAttendance()
    return
  }
  const continueBtn = form.querySelector(".button button");
  const errorText = form.querySelector(".error-text");
  const inputs = form.querySelectorAll("input ");

  const initialButtonText = continueBtn.innerHTML

  loadingState(continueBtn, initialButtonText, true);

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "server/add.php", true);
  xhr.onload = () => {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        const data = xhr.response;
        if (data === "success") {
          inputs.forEach(input => {
            // console.log(input.target)
            // console.log(input.type)
            if(input.type != "hidden"){
              input.value = "";

            }
          }); 
          errorText.style.background = "#7df3598f";
          errorText.style.border = "1px solid #9bff7c8f";
          errorText.style.display = "block";
          errorText.textContent = displaySuccessText(id);
          loadingState(continueBtn, initialButtonText, false);
        } else {
          errorText.style.display = "block";
          errorText.textContent = data;
          loadingState(continueBtn, initialButtonText, false);
        }
        loadingState(continueBtn, initialButtonText, false);
      }
    }
  };

  const formData = new FormData(form);
  xhr.send(formData);
};

modalForm.forEach(form => {
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    submitForm(form, e.target.id);
  });
});

function displaySuccessText(id) {
  switch (id) {
    case "teacher":
      return "Teacher was added successfully.";
    case "class":
      return "Class was created successfully.";
    case "class-teacher":
      return "Class-teacher was assigned successfully.";
    default:
      return "Task was successfully.";
  }
}

function loadingState(button, initialText, isLoading) {
  // console.log(initialText)
  if (isLoading) {
    button.innerHTML = `<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>`;
  } else {
    console.log("loading",isLoading)
    button.innerHTML = initialText;
  }
}

function stuffSignAttendance(){
  console.log("signed")

}
