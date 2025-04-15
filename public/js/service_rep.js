const ServiceReport = {
    async filterReport() {
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
            const response = await fetch(`/service_rep/search?query=${encodeURIComponent(searchValue)}&block=${encodeURIComponent(blockValue)}&month=${encodeURIComponent(monthValue)}&year=${encodeURIComponent(yearValue)}`, {
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
                                <p>No service fee data found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.payments.forEach(payment => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${payment.block}</td>
                            <td>${payment.consumer_name}</td>
                            <td>₱${payment.service_amount}</td>
                            <td>₱${payment.reconnection_fee}</td>
                            <td>${payment.status}</td>
                            <td>${payment.payment_date}</td>
                        `;
                        tbody.appendChild(row);
                    });
                }

                if (data.totals) {
                    this.updateTotals(
                        data.totals.service_amount,
                        data.totals.reconnection_fee
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

    updateTotals(serviceAmount, reconnectionFee) {
        const tfoot = document.querySelector('.uni-table tfoot');
        const serviceTotals = document.querySelector('.service-totals p strong:last-child');
        const total = serviceAmount + reconnectionFee;

        if (tfoot) {
            const totalRow = tfoot.querySelector('.total-row');
            totalRow.innerHTML = `
                <td colspan="2"><strong>Total</strong></td>
                <td><strong>₱${Number(serviceAmount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></td>
                <td><strong>₱${Number(reconnectionFee).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></td>
                <td colspan="2"></td>
            `;
        }

        if (serviceTotals) {
            serviceTotals.textContent = `₱${Number(total).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const blockFilter = document.getElementById('blockFilter');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');

    const searchButton = document.querySelector('.btn-search');
    const filterButton = document.querySelector('.btn-filter');

    if (searchButton) searchButton.addEventListener('click', () => ServiceReport.filterReport());
    if (filterButton) filterButton.addEventListener('click', () => ServiceReport.filterReport());
});
