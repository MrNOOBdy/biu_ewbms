document.addEventListener('DOMContentLoaded', function() {
    const noticeSelect = document.getElementById('noticeSelect');
    
    // Ensure dropdown is clickable
    noticeSelect.addEventListener('mousedown', function(e) {
        e.stopPropagation();
    });

    noticeSelect.addEventListener('click', function(e) {
        e.stopPropagation();
        this.focus();
    });
    
    noticeSelect.addEventListener('change', function() {
        const selectedNotice = this.value;
        if (selectedNotice) {
            fetchNoticeAnnouncement(selectedNotice);
        }
    });
});

function fetchNoticeAnnouncement(noticeId) {
    const textarea = document.getElementById('announcementText');
    const selectedOption = document.querySelector(`option[value="${noticeId}"]`);
    if (selectedOption) {
        const announcement = selectedOption.textContent.split(' - ')[1] || '';
        textarea.value = announcement;
    }
}

function sendNotice() {
    const noticeId = document.getElementById('noticeSelect').value;
    const message = document.getElementById('announcementText').value;
    
    if (!noticeId) {
        alert('Please select a notice type');
        return;
    }

    if (!message.trim()) {
        alert('Please enter an announcement message');
        return;
    }

    console.log('Sending notice:', { noticeId, message });
}

function viewDetails(billId) {
    console.log('Viewing details for bill:', billId);
}
