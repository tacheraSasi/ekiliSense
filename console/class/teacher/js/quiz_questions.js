// Quiz questions management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const addQuestionForm = document.getElementById('addQuestionForm');
    const errorText = document.querySelector('.error-text');
    const submitBtn = document.getElementById('submitQuestion');
    
    if (addQuestionForm) {
        addQuestionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitQuestionForm();
        });
    }
    
    function submitQuestionForm() {
        if (!validateForm()) {
            return;
        }
        
        // Show loading state
        showLoadingState(submitBtn, 'Adding...', true);
        hideError();
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/quiz_questions.php", true);
        xhr.onload = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response.trim();
                    if (data === "success") {
                        showLoadingState(submitBtn, 'Add Question', false);
                        
                        // Show success message
                        showSuccessMessage('Question added successfully!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addQuestionModal'));
                        modal.hide();
                        
                        // Reset form
                        addQuestionForm.reset();
                        
                        // Refresh page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        showError(data);
                        showLoadingState(submitBtn, 'Add Question', false);
                    }
                } else {
                    showError('Network error. Please try again.');
                    showLoadingState(submitBtn, 'Add Question', false);
                }
            }
        };
        
        xhr.onerror = function() {
            showError('Network error. Please try again.');
            showLoadingState(submitBtn, 'Add Question', false);
        };
        
        let formData = new FormData(addQuestionForm);
        xhr.send(formData);
    }
    
    function validateForm() {
        const questionText = document.getElementById('question_text').value.trim();
        const questionType = document.getElementById('question_type').value;
        const correctAnswer = document.getElementById('correct_answer').value.trim();
        
        if (!questionText) {
            showError('Please enter the question text.');
            return false;
        }
        
        if (!correctAnswer) {
            showError('Please enter the correct answer.');
            return false;
        }
        
        // Validate multiple choice questions have options
        if (questionType === 'multiple_choice') {
            const optionA = document.getElementById('option_a').value.trim();
            const optionB = document.getElementById('option_b').value.trim();
            
            if (!optionA || !optionB) {
                showError('Multiple choice questions must have at least options A and B.');
                return false;
            }
        }
        
        return true;
    }
    
    function showError(message) {
        errorText.textContent = message;
        errorText.style.display = 'block';
    }
    
    function hideError() {
        errorText.style.display = 'none';
    }
    
    function showLoadingState(button, originalText, isLoading) {
        if (isLoading) {
            button.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...`;
            button.disabled = true;
        } else {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    }
    
    function showSuccessMessage(message) {
        // Create and show a success toast/alert
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }
});
