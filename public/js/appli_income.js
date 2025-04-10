const AppIncome = {
    async filterIncome() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const monthValue = document.getElementById('monthFilter').value;
        const yearValue = document.getElementById('yearFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        if (!searchValue && !blockValue && !monthValue && !yearValue) {
            if (paginationContainer) {
                paginationContainer.style.display = 'flex';
            }
            return;
        }

        try {
            const response = await fetch(`/appli_income/search?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';

                if (data.payments.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-chart-bar"></i>
                                <p>No application income data found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.payments.forEach(payment => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${payment.block}</td>
                            <td>${payment.consumer_name}</td>
                            <td>₱${payment.application_fee}</td>
                            <td>₱${payment.amount_paid}</td>
                            <td>₱${payment.balance}</td>
                            <td>${payment.payment_date}</td>
                        `;
                        tbody.appendChild(row);
                    });
                }

                if (data.totals) {
                    this.updateTotals(
                        data.totals.application_fee,
                        data.totals.amount_paid,
                        data.totals.balance
                    );
                }

                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to filter payments:', error);
        }
    },

    updateTotals(applicationFee, amountPaid, balance) {
        const tfoot = document.querySelector('.uni-table tfoot');
        const appliBalance = document.querySelector('.appli-balance p strong:last-child');

        if (tfoot) {
            const totalRow = tfoot.querySelector('.total-row');
            totalRow.innerHTML = `
                <td colspan="2"><strong>Total</strong></td>
                <td><strong>₱${Number(applicationFee).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></td>
                <td><strong>₱${Number(amountPaid).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></td>
                <td><strong>₱${Number(balance).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></td>
                <td></td>
            `;
        }

        if (appliBalance) {
            appliBalance.textContent = `₱${Number(amountPaid).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
    },

    async printReport() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const monthValue = document.getElementById('monthFilter').value;
        const yearValue = document.getElementById('yearFilter').value;

        const printWindow = window.open(`/appli_income/print-report?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}`, '_blank');

        printWindow.addEventListener('load', () => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script.onload = () => {
                printWindow.printApplicationReport = function () {
                    printWindow.print();
                };

                printWindow.downloadApplicationReport = function () {
                    const element = printWindow.document.querySelector('#application-income-report');
                    const opt = {
                        margin: 1,
                        filename: 'application-income-report.pdf',
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
};

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const blockFilter = document.getElementById('blockFilter');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');

    const searchButton = document.querySelector('.btn-search');
    const filterButton = document.querySelector('.btn-filter');

    if (searchButton) searchButton.addEventListener('click', () => AppIncome.filterIncome());
    if (filterButton) filterButton.addEventListener('click', () => AppIncome.filterIncome());
});
