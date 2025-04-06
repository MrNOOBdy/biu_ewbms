const BalanceReport = {
    async filterBalance() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        if (!searchValue && !blockValue) {
            if (paginationContainer) {
                paginationContainer.style.display = 'flex';
            }
            return;
        }

        try {
            const response = await fetch(`/balance_rep/search?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}`, {
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
    }
};

function updateFooterTotal(total) {
    const tfoot = document.querySelector('.uni-table tfoot');
    if (tfoot) {
        const totalCell = tfoot.querySelector('td[colspan="2"] strong');
        totalCell.textContent = `₱${Number(total).toFixed(2)}`;
    }
}
