// Homework submissions management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const gradeForm = document.getElementById('gradeSubmissionForm');
    const errorText = document.querySelector('.error-text');
    const submitBtn = document.getElementById('submitGrade');
    
    if (gradeForm) {
        gradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitGradeForm();
        });
    }
    
    function submitGradeForm() {
        // Show loading state
        showLoadingState(submitBtn, 'Submitting...', true);
        hideError();
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/homework.php", true);
        xhr.onload = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response.trim();
                    if (data === "success") {
                        showLoadingState(submitBtn, 'Submit Grade', false);
                        
                        // Show success message
                        showSuccessMessage('Grade submitted successfully!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('gradeSubmissionModal'));
                        modal.hide();
                        
                        // Reset form
                        gradeForm.reset();
                        
                        // Refresh page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        showError(data);
                        showLoadingState(submitBtn, 'Submit Grade', false);
                    }
                } else {
                    showError('Network error. Please try again.');
                    showLoadingState(submitBtn, 'Submit Grade', false);
                }
            }
        };
        
        xhr.onerror = function() {
            showError('Network error. Please try again.');
            showLoadingState(submitBtn, 'Submit Grade', false);
        };
        
        let formData = new FormData(gradeForm);
        xhr.send(formData);
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

// Function to view submission details
function viewSubmission(submissionId) {
    const modal = new bootstrap.Modal(document.getElementById('viewSubmissionModal'));
    const contentDiv = document.getElementById('submissionContent');
    
    // Show loading
    contentDiv.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>';
    
    modal.show();
    
    // Fetch submission details
    let xhr = new XMLHttpRequest();
    xhr.open("GET", `server/get_submission.php?id=${submissionId}`, true);
    xhr.onload = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    let data = JSON.parse(xhr.response);
                    if (data.success) {
                        displaySubmissionDetails(data.submission);
                    } else {
                        contentDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                } catch (e) {
                    contentDiv.innerHTML = '<div class="alert alert-danger">Error loading submission details.</div>';
                }
            } else {
                contentDiv.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
            }
        }
    };
    
    xhr.onerror = function() {
        contentDiv.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
    };
    
    xhr.send();
}

// Function to display submission details in modal
function displaySubmissionDetails(submission) {
    const contentDiv = document.getElementById('submissionContent');
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Student:</strong> ${submission.student_name || 'Unknown'}</p>
                <p><strong>Submitted:</strong> ${submission.submission_date}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> <span class="badge bg-${submission.status === 'graded' ? 'success' : 'info'}">${submission.status}</span></p>
                ${submission.grade !== null ? `<p><strong>Grade:</strong> ${submission.grade}</p>` : ''}
            </div>
        </div>
        <hr>
    `;
    
    if (submission.submission_text) {
        html += `
            <div class="mb-3">
                <strong>Submission Text:</strong>
                <div class="p-3 bg-light rounded mt-2">
                    ${submission.submission_text.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    }
    
    if (submission.file_name) {
        html += `
            <div class="mb-3">
                <strong>Attached File:</strong><br>
                <a href="${submission.file_path}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                    <i class="bi bi-download"></i> Download ${submission.file_name}
                </a>
            </div>
        `;
    }
    
    if (submission.teacher_feedback) {
        html += `
            <div class="mb-3">
                <strong>Teacher Feedback:</strong>
                <div class="p-3 bg-light rounded mt-2">
                    ${submission.teacher_feedback.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    }
    
    contentDiv.innerHTML = html;
}

// Function to open grade submission modal
function gradeSubmission(submissionId, maxPoints) {
    document.getElementById('gradeSubmissionId').value = submissionId;
    document.getElementById('maxPoints').textContent = maxPoints;
    document.getElementById('grade').max = maxPoints;
    
    const modal = new bootstrap.Modal(document.getElementById('gradeSubmissionModal'));
    modal.show();
}
