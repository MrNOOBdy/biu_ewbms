const MeterReadings = {
    async filter() {
        const searchValue = document.getElementById('searchInput').value.trim();
        const blockValue = document.getElementById('blockFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        if (!searchValue && !blockValue) {
            window.location.reload();
            return;
        }

        try {
            const queryParams = new URLSearchParams();
            if (searchValue) queryParams.append('query', searchValue);
            if (blockValue) queryParams.append('block', blockValue);

            const response = await fetch(`/meter-readings/search?${queryParams.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';

                if (data.readings.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>No readings found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.readings.forEach(reading => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>Block ${reading.block_id}</td>
                            <td>${reading.customer_id}</td>
                            <td>${reading.consumer_name}</td>
                            <td>${reading.consumer_type}</td>
                            <td>${reading.previous_reading}</td>
                            <td>${reading.present_reading}</td>
                            <td>${reading.consumption}</td>
                            <td>${reading.meter_reader}</td>
                        `;
                        tbody.appendChild(row);
                    });
                }

                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Failed to filter readings:', error);
        }
    }
};
