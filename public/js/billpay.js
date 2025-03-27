function handlePayment(consreadId) {
    fetch(`/billing/get-bill-details/${consreadId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const presentReading = parseFloat(data.present_reading) || 0;

                document.getElementById('billId').value = consreadId;
                document.getElementById('present_reading').value = `₱${presentReading.toFixed(2)}`;
                document.getElementById('penalty_amount').value = '0';
                document.getElementById('total_amount').value = `₱${presentReading.toFixed(2)}`;
                document.getElementById('bill_tendered_amount').value = '';

                const modal = document.getElementById('paymentModal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);

                document.getElementById('penalty_amount').addEventListener('input', updateTotalAmount);
                document.getElementById('bill_tendered_amount').addEventListener('input', validateTendered);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPaymentResultModal(false, 'Error fetching bill details');
        });
}

function updateTotalAmount() {
    const presentReading = parseFloat(document.getElementById('present_reading').value.replace('₱', '')) || 0;
    const penaltyAmount = parseFloat(document.getElementById('penalty_amount').value) || 0;
    const totalAmount = presentReading + penaltyAmount;
    document.getElementById('total_amount').value = `₱${totalAmount.toFixed(2)}`;

    const tenderedInput = document.getElementById('bill_tendered_amount');
    if (tenderedInput.value) {
        validateTendered();
    }
}

function validateTendered() {
    const totalAmount = parseFloat(document.getElementById('total_amount').value.replace('₱', ''));
    const tendered = parseFloat(document.getElementById('bill_tendered_amount').value) || 0;

    const input = document.getElementById('bill_tendered_amount');
    const feedback = input.nextElementSibling;
    const submitButton = document.querySelector('#paymentForm button[type="submit"]');

    if (tendered < totalAmount) {
        input.classList.add('is-invalid');
        feedback.textContent = `Amount tendered is insufficient. Required amount is ₱${totalAmount.toFixed(2)}`;
        submitButton.disabled = true;
    } else if (tendered > totalAmount) {
        input.classList.add('is-invalid');
        feedback.textContent = `Amount tendered is too high. Required amount is ₱${totalAmount.toFixed(2)}`;
        submitButton.disabled = true;
    } else {
        input.classList.remove('is-invalid');
        feedback.textContent = '';
        submitButton.disabled = false;
    }
}

async function processPayment(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const totalAmount = parseFloat(document.getElementById('total_amount').value.replace('₱', ''));
    const tendered = parseFloat(document.getElementById('bill_tendered_amount').value);
    const penalty = parseFloat(document.getElementById('penalty_amount').value) || 0;

    if (tendered !== totalAmount) {
        return;
    }

    fetch('/billing/process-payment', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            bill_id: formData.get('bill_id'),
            bill_tendered_amount: tendered,
            penalty_amount: penalty
        })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closeModal('paymentModal');
                showPaymentResultModal(true, 'Payment processed successfully');
                setTimeout(() => location.reload(), 1500);
            } else {
                showPaymentResultModal(false, result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPaymentResultModal(false, 'Payment processing failed');
        });
}

function showPaymentResultModal(success, message) {
    const modal = document.getElementById('paymentResultModal');
    const icon = document.getElementById('paymentResultIcon');
    const title = document.getElementById('paymentResultTitle');
    const messageEl = document.getElementById('paymentResultMessage');

    icon.className = success ? 'success' : 'error';
    icon.innerHTML = success ?
        '<i class="fas fa-check-circle"></i>' :
        '<i class="fas fa-exclamation-triangle"></i>';
    title.textContent = success ? 'Success' : 'Error';
    messageEl.textContent = message;

    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function printBill(button) {
    const billId = button.dataset.billId;
    const printWindow = window.open(`/billing/print-bill/${billId}`, '_blank');

    printWindow.addEventListener('load', () => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            printWindow.printBillReceipt = function () {
                printWindow.print();
            };

            printWindow.downloadBillReceipt = function () {
                const element = printWindow.document.querySelector(`#bill-receipt`);
                const opt = {
                    margin: 1,
                    filename: `water-bill-${billId}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                };

                const buttons = element.querySelector('.print-buttons');
                buttons.style.display = 'none';

                printWindow.html2pdf().set(opt).from(element).save().then(() => {
                    buttons.style.display = 'block';
                });
            };
        };
        printWindow.document.head.appendChild(script);
    });
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

function filterBills() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
    const statusValue = document.getElementById('statusFilter').value.toLowerCase();
    const tbody = document.querySelector('.uni-table tbody');
    const rows = tbody.querySelectorAll('tr');
    let hasMatches = false;

    rows.forEach(row => {
        if (row.classList.contains('empty-state-row')) {
            row.remove();
            return;
        }

        const cells = row.querySelectorAll('td');
        if (cells.length > 0) {
            const consumerId = cells[0].textContent.toLowerCase();
            const name = cells[1].textContent.toLowerCase();
            const dueDate = cells[2].textContent.toLowerCase();
            const statusBadge = row.querySelector('.status-badge');
            const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';

            const matchesSearch = !searchValue ||
                consumerId.includes(searchValue) ||
                name.includes(searchValue) ||
                dueDate.includes(searchValue);

            const matchesStatus = !statusValue || status === statusValue;

            const matches = matchesSearch && matchesStatus;
            row.style.display = matches ? '' : 'none';
            if (matches) hasMatches = true;
        }
    });

    if (!hasMatches) {
        showEmptyState(tbody);
    }
}

function showEmptyState(tbody) {
    const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
    tbody.innerHTML = `
        <tr class="empty-state-row">
            <td colspan="${colspan}" class="empty-state">
                <i class="fas fa-search"></i>
                <p>No bills found for your search criteria</p>
            </td>
        </tr>`;
}

document.addEventListener('DOMContentLoaded', function () {
    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target.id);
        }
    };
});
