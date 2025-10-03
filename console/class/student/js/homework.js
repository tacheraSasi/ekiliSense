// Student homework management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const submitForm = document.getElementById('submitHomeworkForm');
    const errorText = document.querySelector('.error-text');
    const submitBtn = document.getElementById('submitBtn');
    
    if (submitForm) {
        submitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitHomeworkForm();
        });
    }
    
    function submitHomeworkForm() {
        if (!validateForm()) {
            return;
        }
        
        // Show loading state
        showLoadingState(submitBtn, 'Submitting...', true);
        hideError();
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/submit_homework.php", true);
        xhr.onload = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response.trim();
                    if (data === "success") {
                        showLoadingState(submitBtn, 'Submit Homework', false);
                        
                        // Show success message
                        showSuccessMessage('Homework submitted successfully!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('submitHomeworkModal'));
                        modal.hide();
                        
                        // Reset form
                        submitForm.reset();
                        
                        // Refresh page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        showError(data);
                        showLoadingState(submitBtn, 'Submit Homework', false);
                    }
                } else {
                    showError('Network error. Please try again.');
                    showLoadingState(submitBtn, 'Submit Homework', false);
                }
            }
        };
        
        xhr.onerror = function() {
            showError('Network error. Please try again.');
            showLoadingState(submitBtn, 'Submit Homework', false);
        };
        
        let formData = new FormData(submitForm);
        xhr.send(formData);
    }
    
    function validateForm() {
        const submissionText = document.getElementById('submission_text').value.trim();
        const fileInput = document.getElementById('file_upload');
        
        if (!submissionText) {
            showError('Please enter your answer or response.');
            return false;
        }
        
        // Validate file size if file is selected
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            
            if (file.size > maxSize) {
                showError('File size must be less than 5MB.');
                return false;
            }
            
            // Validate file type
            const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showError('Invalid file type. Please upload PDF, DOC, DOCX, TXT, JPG, or PNG files only.');
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

// Function to open submit homework modal
function submitHomework(assignmentUid, title, type) {
    document.getElementById('assignmentUid').value = assignmentUid;
    document.getElementById('homeworkTitle').textContent = title;
    document.getElementById('assignmentType').value = type;
    
    const modal = new bootstrap.Modal(document.getElementById('submitHomeworkModal'));
    modal.show();
}

// Function to view student's own submission
function viewMySubmission(assignmentUid) {
    const modal = new bootstrap.Modal(document.getElementById('viewMySubmissionModal'));
    const contentDiv = document.getElementById('mySubmissionContent');
    
    // Show loading
    contentDiv.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>';
    
    modal.show();
    
    // Fetch submission details
    let xhr = new XMLHttpRequest();
    xhr.open("GET", `server/get_my_submission.php?assignment_uid=${assignmentUid}`, true);
    xhr.onload = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    let data = JSON.parse(xhr.response);
                    if (data.success) {
                        displayMySubmission(data.submission);
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

// Function to display student's submission
function displayMySubmission(submission) {
    const contentDiv = document.getElementById('mySubmissionContent');
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Assignment:</strong> ${submission.assignment_title}</p>
                <p><strong>Submitted:</strong> ${submission.submission_date}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Status:</strong> <span class="badge bg-${submission.status === 'graded' ? 'success' : 'info'}">${submission.status}</span></p>
                ${submission.grade !== null ? `<p><strong>Grade:</strong> ${submission.grade} / ${submission.max_points}</p>` : '<p><strong>Grade:</strong> <span class="text-muted">Not graded yet</span></p>'}
            </div>
        </div>
        <hr>
    `;
    
    if (submission.submission_text) {
        html += `
            <div class="mb-3">
                <strong>Your Response:</strong>
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
                <div class="p-3 alert alert-info mt-2">
                    ${submission.teacher_feedback.replace(/\n/g, '<br>')}
                </div>
            </div>
        `;
    } else if (submission.status === 'submitted') {
        html += `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Your submission is pending review by the teacher.
            </div>
        `;
    }
    
    contentDiv.innerHTML = html;
}
