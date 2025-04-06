const AppIncome = {
    async filterIncome() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const monthValue = document.getElementById('monthFilter').value;
        const yearValue = document.getElementById('yearFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        // Only make the API call if at least one filter has a value
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
        if (tfoot) {
            const totalRow = tfoot.querySelector('.total-row');
            totalRow.cells[2].innerHTML = `<strong>₱${applicationFee.toFixed(2)}</strong>`;
            totalRow.cells[3].innerHTML = `<strong>₱${amountPaid.toFixed(2)}</strong>`;
            totalRow.cells[4].innerHTML = `<strong>₱${balance.toFixed(2)}</strong>`;
        }
    }
};

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const blockFilter = document.getElementById('blockFilter');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');

    // Remove all keyup/change event listeners - only use button click
    const searchButton = document.querySelector('.btn-search');
    const filterButton = document.querySelector('.btn-filter');

    if (searchButton) searchButton.addEventListener('click', () => AppIncome.filterIncome());
    if (filterButton) filterButton.addEventListener('click', () => AppIncome.filterIncome());
});
