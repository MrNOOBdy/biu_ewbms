const IncomeReport = {
    async filterIncome() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const monthValue = document.getElementById('monthFilter').value;
        const yearValue = document.getElementById('yearFilter').value;
        const blockValue = document.getElementById('blockFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        if (!searchValue && !monthValue && !yearValue && !blockValue) {
            if (paginationContainer) {
                paginationContainer.style.display = 'flex';
            }
            return;
        }

        try {
            const response = await fetch(`/income_rep/search?query=${encodeURIComponent(searchValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}&block=${encodeURIComponent(blockValue)}`, {
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
                                <i class="fas fa-coins"></i>
                                <p>No paid bills found</p>
                            </td>
                        </tr>
                    `;
                    this.updateTotal(0);
                } else {
                    data.bills.forEach(bill => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${bill.block_id}</td>
                            <td>${bill.consumer_name}</td>
                            <td>₱${bill.total_amount}</td>
                            <td>${bill.date_paid}</td>
                        `;
                        tbody.appendChild(row);
                    });

                    this.updateTotal(data.totalIncome);
                }

                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to filter income:', error);
        }
    },

    updateTotal(total) {
        const tfoot = document.querySelector('.uni-table tfoot');
        if (tfoot) {
            const totalCell = tfoot.querySelector('td[colspan="2"] strong');
            if (totalCell) {
                totalCell.textContent = `₱${Number(total).toFixed(2)}`;
            }
        }
    },

    async printReport() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const monthValue = document.getElementById('monthFilter').value;
        const yearValue = document.getElementById('yearFilter').value;

        const printWindow = window.open(`/income_rep/print-report?query=${encodeURIComponent(searchValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}`, '_blank');

        printWindow.addEventListener('load', () => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script.onload = () => {
                printWindow.printIncomeReport = function () {
                    printWindow.print();
                };

                printWindow.downloadIncomeReport = function () {
                    const element = printWindow.document.querySelector('#income-report');
                    const opt = {
                        margin: 1,
                        filename: 'income-report.pdf',
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
