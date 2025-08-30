// Exam management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const addExamForm = document.getElementById('addExamForm');
    const errorText = document.querySelector('.error-text');
    const submitBtn = document.getElementById('submitExam');
    
    if (addExamForm) {
        addExamForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitExamForm();
        });
    }
    
    function submitExamForm() {
        if (!validateExamForm()) {
            return;
        }
        
        // Show loading state
        showLoadingState(submitBtn, 'Scheduling...', true);
        hideError();
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/exams.php", true);
        xhr.onload = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response.trim();
                    if (data === "success") {
                        // Success - close modal and refresh page
                        showLoadingState(submitBtn, 'Schedule Exam', false);
                        
                        // Show success message
                        showSuccessMessage('Exam scheduled successfully!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addExamModal'));
                        modal.hide();
                        
                        // Reset form
                        addExamForm.reset();
                        
                        // Refresh page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        // Error from server
                        showError(data);
                        showLoadingState(submitBtn, 'Schedule Exam', false);
                    }
                } else {
                    showError('Network error. Please try again.');
                    showLoadingState(submitBtn, 'Schedule Exam', false);
                }
            }
        };
        
        xhr.onerror = function() {
            showError('Network error. Please try again.');
            showLoadingState(submitBtn, 'Schedule Exam', false);
        };
        
        let formData = new FormData(addExamForm);
        xhr.send(formData);
    }
    
    function validateExamForm() {
        const examTitle = document.querySelector('input[name="exam_title"]').value.trim();
        const subjectId = document.querySelector('select[name="subject_id"]').value;
        const examDate = document.querySelector('input[name="exam_date"]').value;
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        const maxMarks = document.querySelector('input[name="max_marks"]').value;
        
        if (!examTitle) {
            showError('Please enter an exam title.');
            return false;
        }
        
        if (!subjectId) {
            showError('Please select a subject.');
            return false;
        }
        
        if (!examDate) {
            showError('Please select an exam date.');
            return false;
        }
        
        if (!startTime) {
            showError('Please select start time.');
            return false;
        }
        
        if (!endTime) {
            showError('Please select end time.');
            return false;
        }
        
        // Check if exam date is not in the past
        const today = new Date();
        const selectedDate = new Date(examDate);
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showError('Exam date cannot be in the past.');
            return false;
        }
        
        // Check if start time is before end time
        if (startTime >= endTime) {
            showError('Start time must be before end time.');
            return false;
        }
        
        if (maxMarks && (maxMarks < 1 || maxMarks > 1000)) {
            showError('Max marks must be between 1 and 1000.');
            return false;
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
        // Create a success alert that will be visible briefly
        const alertHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <i class="bi bi-check-circle-fill"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', alertHTML);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }
});

// Function to mark exam as completed
function markExamCompleted(examUid) {
    if (!confirm('Are you sure you want to mark this exam as completed? You can then add results for students.')) {
        return;
    }
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/exams.php", true);
    xhr.onload = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response.trim();
                if (data === "success") {
                    window.location.reload();
                } else {
                    alert('Error: ' + data);
                }
            }
        }
    };
    
    let formData = new FormData();
    formData.append('form-type', 'mark-completed');
    formData.append('exam_uid', examUid);
    
    xhr.send(formData);
}

// Function to delete exam
function deleteExam(examUid) {
    if (!confirm('Are you sure you want to delete this exam? This action cannot be undone and will also delete all associated results.')) {
        return;
    }
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/exams.php", true);
    xhr.onload = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response.trim();
                if (data === "success") {
                    window.location.reload();
                } else {
                    alert('Error: ' + data);
                }
            }
        }
    };
    
    let formData = new FormData();
    formData.append('form-type', 'delete-exam');
    formData.append('exam_uid', examUid);
    
    xhr.send(formData);
}