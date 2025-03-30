function filterServices() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
    const blockValue = document.getElementById('blockFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const rows = tbody.querySelectorAll('tr');
    let hasMatches = false;

    rows.forEach(row => {
        if (row.classList.contains('empty-state-row')) {
            row.remove();
        }
    });

    const dataRows = Array.from(rows).filter(row => !row.querySelector('.empty-state'));
    if (dataRows.length === 0) {
        showEmptyState(tbody);
        return;
    }

    dataRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const block = cells[0].textContent.toLowerCase();
            const customerId = cells[1].textContent.toLowerCase();
            const firstName = cells[2].textContent.toLowerCase();
            const middleName = cells[3].textContent.toLowerCase();
            const lastName = cells[4].textContent.toLowerCase();
            const status = row.querySelector('.status-badge')?.textContent.trim().toLowerCase() || '';

            const matchesSearch = !searchValue ||
                customerId.includes(searchValue) ||
                firstName.includes(searchValue) ||
                middleName.includes(searchValue) ||
                lastName.includes(searchValue);

            const matchesBlock = !blockValue || block.includes(`block ${blockValue}`);
            const matchesStatus = !statusValue || status === statusValue;

            const matches = matchesSearch && matchesBlock && matchesStatus;
            row.style.display = matches ? '' : 'none';
            if (matches) hasMatches = true;
        }
    });

    if (!hasMatches) {
        showEmptyState(tbody);
    }
}

function showEmptyState(tbody) {
    const emptyRow = document.createElement('tr');
    emptyRow.classList.add('empty-state-row');
    const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
    emptyRow.innerHTML = `
        <td colspan="${colspan}" class="empty-state">
            <i class="fas fa-tools"></i>
            <p>No reconnection payments found for your search</p>
        </td>`;
    tbody.appendChild(emptyRow);
}

function showServicePaymentModal(customerId) {
    const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
    const reconnectionFee = parseFloat(row.querySelector('td:nth-child(6)').textContent.replace('₱', '').replace(',', '').trim());

    document.getElementById('servicePaymentCustomerId').value = customerId;
    document.getElementById('reconnectionFee').value = reconnectionFee.toFixed(2);
    document.getElementById('serviceAmountTendered').value = '';

    const modal = document.getElementById('servicePaymentModal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeServiceResultModal() {
    const modal = document.getElementById('serviceResultModal');
    modal.classList.remove('fade-in');
    
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            window.location.reload();
        }
    }, 300);
}

function handleServicePayment(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const amountTendered = parseFloat(formData.get('amount_tendered'));
    const reconnectionFee = parseFloat(document.getElementById('reconnectionFee').value);
    const input = document.getElementById('serviceAmountTendered');
    const feedback = input.nextElementSibling;

    input.classList.remove('is-invalid');
    if (feedback) feedback.textContent = '';

    if (!amountTendered || isNaN(amountTendered)) {
        input.classList.add('is-invalid');
        if (feedback) feedback.textContent = 'Please enter a valid amount';
        return false;
    }

    if (amountTendered !== reconnectionFee) {
        input.classList.add('is-invalid');
        if (feedback) {
            if (amountTendered < reconnectionFee) {
                feedback.textContent = `Amount tendered is insufficient. Required amount is ₱${reconnectionFee.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            } else {
                feedback.textContent = `Amount tendered is too high. Required amount is ₱${reconnectionFee.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        }
        return false;
    }

    fetch('/service/process-payment', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            customer_id: formData.get('customer_id'),
            amount_tendered: amountTendered
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.errors) {
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = data.errors.amount_tendered[0];
                }
            } else if (data.success) {
                closeModal('servicePaymentModal');
                showServiceResultModal(true, data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showServiceResultModal(false, 'An error occurred while processing the payment');
        });
}

function updateTableRow(customerId, amountPaid) {
    const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
    if (row) {
        row.querySelector('td:nth-child(7)').textContent = `₱${parseFloat(amountPaid).toFixed(2)}`;
        const statusBadge = row.querySelector('.status-badge');
        statusBadge.textContent = 'Paid';
        statusBadge.classList.remove('status-inactive');
        statusBadge.classList.add('status-active');

        const actionCell = row.querySelector('td:last-child');
        if (actionCell) {
            const actionButtons = actionCell.querySelector('.action-buttons');
            if (actionButtons) {
                actionButtons.innerHTML = `
                    <button class="btn_uni btn-billing" title="Print Receipt" onclick="printServiceReceipt('${customerId}')">
                        <i class="fas fa-print"></i>Print
                    </button>
                `;
            }
        }
    }
}

document.getElementById('servicePaymentForm').addEventListener('submit', handleServicePayment);

function showServiceResultModal(success, message) {
    const modal = document.getElementById('serviceResultModal');
    const icon = document.getElementById('serviceResultIcon');
    const title = document.getElementById('serviceResultTitle');
    const messageEl = document.getElementById('serviceResultMessage');

    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    icon.className = success ? 'success' : 'error';
    title.textContent = success ? 'Success' : 'Error';
    messageEl.textContent = message;

    modal.setAttribute('data-refresh', success ? 'true' : 'false');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modalId === 'servicePaymentModal') {
            document.getElementById('serviceAmountTendered').value = '';
            document.getElementById('serviceAmountTendered').classList.remove('is-invalid');
        }
    }, 300);
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'serviceResultModal') {
            closeServiceResultModal();
        } else {
            closeModal(event.target.id);
        }
    }
};

function printServiceReceipt(customerId) {
    const printWindow = window.open(`/service/print-receipt/${customerId}`, '_blank');
    printWindow.addEventListener('load', () => {
        // Add the html2pdf script dynamically
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            // Define the print and download functions in the new window
            printWindow.printServiceReceipt = function (customerId) {
                printWindow.print();
            };

            printWindow.downloadServiceReceipt = function (customerId) {
                const element = printWindow.document.querySelector(`#receipt-${customerId}`);
                const opt = {
                    margin: 1,
                    filename: `service-fee-receipt-${customerId}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                };

                // Hide the buttons before generating PDF
                const buttons = element.querySelector('.print-buttons');
                buttons.style.display = 'none';

                // Generate PDF
                printWindow.html2pdf().set(opt).from(element).save().then(() => {
                    // Show the buttons again after PDF generation
                    buttons.style.display = 'block';
                });
            };
        };
        printWindow.document.head.appendChild(script);
    });
}
