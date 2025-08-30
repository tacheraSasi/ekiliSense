// Homework management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const addHomeworkForm = document.getElementById('addHomeworkForm');
    const errorText = document.querySelector('.error-text');
    const submitBtn = document.getElementById('submitHomework');
    
    if (addHomeworkForm) {
        addHomeworkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitHomeworkForm();
        });
    }
    
    function submitHomeworkForm() {
        if (!validateForm()) {
            return;
        }
        
        // Show loading state
        showLoadingState(submitBtn, 'Assigning...', true);
        hideError();
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/homework.php", true);
        xhr.onload = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response.trim();
                    if (data === "success") {
                        // Success - close modal and refresh page
                        showLoadingState(submitBtn, 'Assign Homework', false);
                        
                        // Show success message
                        showSuccessMessage('Homework assigned successfully!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addHomeworkModal'));
                        modal.hide();
                        
                        // Reset form
                        addHomeworkForm.reset();
                        
                        // Refresh page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        // Error from server
                        showError(data);
                        showLoadingState(submitBtn, 'Assign Homework', false);
                    }
                } else {
                    showError('Network error. Please try again.');
                    showLoadingState(submitBtn, 'Assign Homework', false);
                }
            }
        };
        
        xhr.onerror = function() {
            showError('Network error. Please try again.');
            showLoadingState(submitBtn, 'Assign Homework', false);
        };
        
        let formData = new FormData(addHomeworkForm);
        xhr.send(formData);
    }
    
    function validateForm() {
        const title = document.querySelector('input[name="title"]').value.trim();
        const subjectId = document.querySelector('select[name="subject_id"]').value;
        const dueDate = document.querySelector('input[name="due_date"]').value;
        const maxPoints = document.querySelector('input[name="max_points"]').value;
        
        if (!title) {
            showError('Please enter an assignment title.');
            return false;
        }
        
        if (!subjectId) {
            showError('Please select a subject.');
            return false;
        }
        
        if (!dueDate) {
            showError('Please select a due date.');
            return false;
        }
        
        // Check if due date is not in the past
        const today = new Date();
        const selectedDate = new Date(dueDate);
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showError('Due date cannot be in the past.');
            return false;
        }
        
        if (maxPoints && (maxPoints < 1 || maxPoints > 1000)) {
            showError('Max points must be between 1 and 1000.');
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

// Function to handle homework deletion
function deleteHomework(assignmentUid) {
    if (!confirm('Are you sure you want to delete this homework assignment? This action cannot be undone.')) {
        return;
    }
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/homework.php", true);
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
    formData.append('form-type', 'delete-homework');
    formData.append('assignment_uid', assignmentUid);
    
    xhr.send(formData);
}

// Function to toggle homework status
function toggleHomeworkStatus(assignmentUid, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'closed' : 'active';
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/homework.php", true);
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
    formData.append('form-type', 'update-homework');
    formData.append('assignment_uid', assignmentUid);
    formData.append('status', newStatus);
    
    xhr.send(formData);
}