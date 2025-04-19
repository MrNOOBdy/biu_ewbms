const BalanceReport = {
    async filterBalance() {
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
            const response = await fetch(`/balance_rep/search?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';

                if (data.bills.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fas fa-receipt"></i>
                                <p>No unpaid bills found</p>
                            </td>
                        </tr>
                    `;
                    updateFooterTotal(0);
                } else {
                    data.bills.forEach(bill => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${bill.block_id}</td>
                            <td>${bill.consumer_name}</td>
                            <td>₱${bill.total_amount}</td>
                            <td>${bill.reading_date}</td>
                            <td>${bill.due_date}</td>
                        `;
                        tbody.appendChild(row);
                    });

                    updateFooterTotal(data.totalBalance);
                }

                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to filter balances:', error);
        }
    },

    async printReport() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const monthValue = document.getElementById('monthFilter').value;
        const yearValue = document.getElementById('yearFilter').value;

        const printWindow = window.open(`/balance_rep/print-report?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}`, '_blank');

        printWindow.addEventListener('load', () => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script.onload = () => {
                printWindow.printBalanceReport = function () {
                    printWindow.print();
                };

                printWindow.downloadBalanceReport = function () {
                    const element = printWindow.document.querySelector('#balance-report');
                    const opt = {
                        margin: 1,
                        filename: 'balance-report.pdf',
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

function updateFooterTotal(total) {
    const tfoot = document.querySelector('.uni-table tfoot');
    if (tfoot) {
        const filteredRow = tfoot.querySelector('.filtered-total-row');
        const overallRow = tfoot.querySelector('.overall-total-row');
        const filteredTotal = tfoot.querySelector('.filtered-total');

        if (filteredRow && overallRow && filteredTotal) {
            if (total === 0) {
                filteredRow.style.display = 'none';
                overallRow.style.display = 'table-row';
            } else {
                filteredRow.style.display = 'table-row';
                overallRow.style.display = 'none';
                filteredTotal.textContent = `₱${Number(total).toFixed(2)}`;
            }
        }
    }
}
