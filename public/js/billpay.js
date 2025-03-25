function handlePayment(button) {
    const billId = button.dataset.billId;
    fetch(`/billing/get-bill-details/${billId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('billId').value = billId;
            document.getElementById('presentReading').value = data.present_reading;
            document.getElementById('penaltyAmount').value = `₱${data.penalty_amount.toFixed(2)}`;
            document.getElementById('totalAmount').value = `₱${data.total_amount.toFixed(2)}`;
            
            const modal = document.getElementById('paymentModal');
            modal.style.display = 'block';
            modal.classList.add('fade-in');

            const amountTenderedInput = document.getElementById('amountTendered');
            amountTenderedInput.addEventListener('input', calculateChange);
            amountTenderedInput.value = '';
            document.getElementById('changeAmount').value = '';
        });
}

function calculateChange() {
    const totalAmount = parseFloat(document.getElementById('totalAmount').value.replace('₱', ''));
    const amountTendered = parseFloat(document.getElementById('amountTendered').value) || 0;
    const change = amountTendered - totalAmount;
    
    document.getElementById('changeAmount').value = change === 0 ? '₱0.00' : '';
    
    const submitButton = document.querySelector('#paymentForm button[type="submit"]');
    submitButton.disabled = amountTendered !== totalAmount;

    const invalidFeedback = document.querySelector('#amountTendered + .invalid-feedback');
    if (amountTendered !== totalAmount) {
        invalidFeedback.textContent = 'Amount must match the total amount exactly';
        invalidFeedback.style.display = 'block';
    } else {
        invalidFeedback.style.display = 'none';
    }
}

function closePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.classList.remove('fade-in');
    setTimeout(() => modal.style.display = 'none', 300);
}

async function processPayment(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    try {
        const response = await fetch('/billing/process-payment', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            closePaymentModal();
            location.reload();
        } else {
            alert(result.message || 'Payment processing failed');
        }
    } catch (error) {
        console.error('Payment processing error:', error);
        alert('Payment processing failed. Please try again.');
    }
}

function printBill(button) {
    const billId = button.dataset.billId;
    const printWindow = window.open(`/billing/print-bill/${billId}`, '_blank');
    
    printWindow.addEventListener('load', () => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => {
            printWindow.printBillReceipt = function() {
                printWindow.print();
            };

            printWindow.downloadBillReceipt = function() {
                const element = printWindow.document.querySelector(`#bill-receipt`);
                const opt = {
                    margin: 1,
                    filename: `water-bill-${billId}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                };

                // Hide print buttons before generating PDF
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
