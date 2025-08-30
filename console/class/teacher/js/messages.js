// Messages management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const composeMessageForm = document.getElementById('composeMessageForm');
    const errorText = document.querySelector('.error-text');
    const sendBtn = document.getElementById('sendMessage');
    
    if (composeMessageForm) {
        composeMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }
    
    function sendMessage() {
        if (!validateMessageForm()) {
            return;
        }
        
        // Show loading state
        showLoadingState(sendBtn, 'Sending...', true);
        hideError();
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "server/messages.php", true);
        xhr.onload = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    let data = xhr.response.trim();
                    if (data === "success") {
                        // Success - close modal and refresh page
                        showLoadingState(sendBtn, 'Send Message', false);
                        
                        // Show success message
                        showSuccessMessage('Message sent successfully!');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('composeMessageModal'));
                        modal.hide();
                        
                        // Reset form
                        composeMessageForm.reset();
                        updateRecipientOptions(); // Reset recipient options
                        
                        // Refresh page after short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        // Error from server
                        showError(data);
                        showLoadingState(sendBtn, 'Send Message', false);
                    }
                } else {
                    showError('Network error. Please try again.');
                    showLoadingState(sendBtn, 'Send Message', false);
                }
            }
        };
        
        xhr.onerror = function() {
            showError('Network error. Please try again.');
            showLoadingState(sendBtn, 'Send Message', false);
        };
        
        let formData = new FormData(composeMessageForm);
        xhr.send(formData);
    }
    
    function validateMessageForm() {
        const recipientType = document.querySelector('select[name="recipient_type"]').value;
        const studentId = document.querySelector('select[name="student_id"]').value;
        const subject = document.querySelector('input[name="subject"]').value.trim();
        const messageBody = document.querySelector('textarea[name="message_body"]').value.trim();
        
        if (!recipientType) {
            showError('Please select a recipient type.');
            return false;
        }
        
        if (recipientType === 'parent' && !studentId) {
            showError('Please select a student when sending to parents.');
            return false;
        }
        
        if (!subject) {
            showError('Please enter a subject.');
            return false;
        }
        
        if (!messageBody) {
            showError('Please enter a message.');
            return false;
        }
        
        if (messageBody.length > 1000) {
            showError('Message is too long. Please keep it under 1000 characters.');
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

// Function to mark message as read
function markMessageRead(messageUid) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/messages.php", true);
    xhr.onload = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response.trim();
                if (data === "success") {
                    // Message marked as read successfully
                    location.reload();
                } else {
                    console.error('Error marking message as read:', data);
                }
            }
        }
    };
    
    let formData = new FormData();
    formData.append('form-type', 'mark-read');
    formData.append('message_uid', messageUid);
    
    xhr.send(formData);
}

// Function to delete message
function deleteMessage(messageUid) {
    if (!confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
        return;
    }
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "server/messages.php", true);
    xhr.onload = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response.trim();
                if (data === "success") {
                    location.reload();
                } else {
                    alert('Error: ' + data);
                }
            }
        }
    };
    
    let formData = new FormData();
    formData.append('form-type', 'delete-message');
    formData.append('message_uid', messageUid);
    
    xhr.send(formData);
}

// Function to send quick messages based on templates
function sendQuickMessage(type, studentId) {
    const templates = {
        attendance: {
            subject: 'Attendance Concern',
            body: 'Dear Parent,\n\nI wanted to bring to your attention that your child has been absent frequently. Please ensure regular attendance for better academic performance.\n\nBest regards,\nClass Teacher'
        },
        academic: {
            subject: 'Academic Progress Update',
            body: 'Dear Parent,\n\nI would like to discuss your child\'s academic progress. Please let me know when would be a good time to meet.\n\nBest regards,\nClass Teacher'
        },
        behavioral: {
            subject: 'Behavioral Notice',
            body: 'Dear Parent,\n\nI wanted to discuss your child\'s behavior in class. Please contact me at your earliest convenience.\n\nBest regards,\nClass Teacher'
        }
    };
    
    if (templates[type]) {
        // Fill the compose form with template
        document.querySelector('select[name="recipient_type"]').value = 'parent';
        updateRecipientOptions();
        document.querySelector('select[name="student_id"]').value = studentId;
        document.querySelector('select[name="message_type"]').value = type;
        document.querySelector('input[name="subject"]').value = templates[type].subject;
        document.querySelector('textarea[name="message_body"]').value = templates[type].body;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('composeMessageModal'));
        modal.show();
    }
}

// Auto-refresh messages every 30 seconds to check for new messages
setInterval(function() {
    // Only refresh if not currently composing a message
    if (!document.getElementById('composeMessageModal').classList.contains('show')) {
        // Check for new messages without full page reload
        checkNewMessages();
    }
}, 30000);

function checkNewMessages() {
    fetch('server/check_new_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.new_messages > 0) {
                // Show notification for new messages
                showNewMessageNotification(data.new_messages);
            }
        })
        .catch(error => {
            console.error('Error checking for new messages:', error);
        });
}

function showNewMessageNotification(count) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-info alert-dismissible fade show';
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
    notification.innerHTML = `
        <i class="bi bi-envelope"></i> You have ${count} new message${count > 1 ? 's' : ''}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}