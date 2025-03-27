document.addEventListener('DOMContentLoaded', function() {
    const noticeSelect = document.getElementById('noticeSelect');
    
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

function toggleAllCheckboxes() {
    const mainCheckbox = document.getElementById('selectAll');
    const checkboxes = document.getElementsByClassName('bill-checkbox');
    
    Array.from(checkboxes).forEach(checkbox => {
        checkbox.checked = mainCheckbox.checked;
    });
}

function getSelectedBills() {
    const checkboxes = document.getElementsByClassName('bill-checkbox');
    return Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
}

async function sendNotice() {
    const noticeId = document.getElementById('noticeSelect').value;
    const message = document.getElementById('announcementText').value;
    const selectedBills = getSelectedBills();
    
    if (!noticeId) {
        alert('Please select a notice type');
        return;
    }

    if (!message.trim()) {
        alert('Please enter an announcement message');
        return;
    }

    if (selectedBills.length === 0) {
        alert('Please select at least one bill to send notice');
        return;
    }

    const confirmed = await showConfirmDialog(
        `Are you sure you want to send notices to ${selectedBills.length} consumer${selectedBills.length > 1 ? 's' : ''}?`
    );

    if (!confirmed) return;

    // Show loading indicator
    showLoadingModal(`Sending notices to ${selectedBills.length} consumer${selectedBills.length > 1 ? 's' : ''}...`);

    try {
        const response = await fetch('/billing/notice/send-sms', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                bills: selectedBills,
                message: message
            })
        });

        const data = await response.json();
        hideLoadingModal();

        if (data.success) {
            const results = data.results;
            const successful = results.filter(r => r.success).length;
            const failed = results.length - successful;

            showResultModal(
                true,
                `Notice Results`,
                `Successfully sent: ${successful}\nFailed: ${failed}`,
                results
            );
        } else {
            showResultModal(false, 'Error', data.message);
        }
    } catch (error) {
        hideLoadingModal();
        showResultModal(false, 'Error', 'Failed to send notices');
    }
}

function showConfirmDialog(message) {
    return new Promise((resolve) => {
        let modal = document.getElementById('confirmModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'confirmModal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h3><i class="fas fa-question-circle"></i> Confirm Action</h3>
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessage" style="margin: 20px 0; text-align: center;"></p>
                    </div>
                    <div class="modal-actions">
                        <button class="btn_modal btn_cancel" onclick="closeConfirmModal(false)">Cancel</button>
                        <button class="btn_modal btn_verify" onclick="closeConfirmModal(true)">Confirm</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        const messageEl = modal.querySelector('#confirmMessage');
        if (messageEl) {
            messageEl.textContent = message;
        }

        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
        modal.resolve = resolve;
    });
}

function closeConfirmModal(confirmed) {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.resolve) {
            modal.resolve(confirmed);
            modal.resolve = null;
        }
    }, 300);
}

async function showLoadingModal(message) {
    let modal = document.getElementById('loadingModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'loadingModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 300px;">
                <div style="text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2em; margin-bottom: 15px;"></i>
                    <p id="loadingMessage"></p>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    document.getElementById('loadingMessage').textContent = message;
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function hideLoadingModal() {
    const modal = document.getElementById('loadingModal');
    if (modal) {
        modal.classList.remove('fade-in');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

function showResultModal(success, title, message, results = null) {
    let modal = document.getElementById('billResultModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'billResultModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content result-modal" style="max-width: 500px;">
                <div id="billResultIcon"></div>
                <h3 id="billResultTitle"></h3>
                <div id="billResultMessage"></div>
                <div class="modal-actions">
                    <button class="btn_modal btn_verify" onclick="closeResultModal()">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const icon = document.getElementById('billResultIcon');
    const titleEl = document.getElementById('billResultTitle');
    const messageEl = document.getElementById('billResultMessage');

    icon.className = success ? 'success' : 'error';
    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    
    titleEl.textContent = title;
    messageEl.textContent = message;

    if (results) {
        const resultsList = document.createElement('div');
        resultsList.className = 'sms-results';
        resultsList.innerHTML = results.map(r => `
            <div class="sms-result-item ${r.success ? 'success' : 'error'}">
                <span>${r.name} (${r.phone})</span>
                <span>${r.success ? '✓' : '✗'}</span>
            </div>
        `).join('');
        
        messageEl.appendChild(resultsList);
    }

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeResultModal() {
    const modal = document.getElementById('billResultModal');
    if (modal) {
        modal.classList.remove('fade-in');
        setTimeout(() => modal.style.display = 'none', 300);
    }
}

function viewDetails(billId) {
    fetch(`/billing/notice/${billId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const bill = data.data;
                
                document.getElementById('detail_customerId').textContent = bill.consumer.customer_id;
                document.getElementById('detail_consumerName').textContent = `${bill.consumer.firstname} ${bill.consumer.lastname}`;
                document.getElementById('detail_contactNo').textContent = bill.consumer.contact_no;
                document.getElementById('detail_address').textContent = bill.consumer.address;
                document.getElementById('detail_consumerType').textContent = bill.consumer.consumer_type;

                document.getElementById('detail_readingDate').textContent = formatDate(bill.reading_date);
                document.getElementById('detail_dueDate').textContent = formatDate(bill.due_date);
                document.getElementById('detail_previousReading').textContent = bill.previous_reading;
                document.getElementById('detail_presentReading').textContent = bill.present_reading;
                document.getElementById('detail_consumption').textContent = bill.consumption;
                document.getElementById('detail_billAmount').textContent = 
                    bill.bill_payments ? 
                    formatAmount(bill.bill_payments.total_amount) : 
                    'Pending';
                document.getElementById('detail_billStatus').textContent = 
                    bill.bill_payments ? 
                    bill.bill_payments.bill_status.toUpperCase() : 
                    'Pending';

                openViewDetailsModal();
            } else {
                alert('Error loading bill details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading bill details');
        });
}

function openNoticeModal() {
    const modal = document.getElementById('noticeModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeNoticeModal() {
    const modal = document.getElementById('noticeModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        document.getElementById('noticeSelect').value = '';
        document.getElementById('announcementText').value = '';
    }, 300);
}

function openViewDetailsModal() {
    const modal = document.getElementById('viewDetailsModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeViewDetailsModal() {
    const modal = document.getElementById('viewDetailsModal');
    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function formatAmount(amount) {
    return parseFloat(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'noticeModal') {
            closeNoticeModal();
        } else if (event.target.id === 'viewDetailsModal') {
            closeViewDetailsModal();
        } else if (event.target.id === 'billResultModal') {
            closeResultModal();
        } else if (event.target.id === 'confirmModal') {
            closeConfirmModal(false);
        }
    }
}
