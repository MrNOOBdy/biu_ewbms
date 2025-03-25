function filterIncome() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
    const blockValue = document.getElementById('blockFilter').value;
    const monthValue = document.getElementById('monthFilter').value;
    const yearValue = document.getElementById('yearFilter').value;
    const tbody = document.querySelector('.uni-table tbody');
    const rows = tbody.querySelectorAll('tr');
    let hasMatches = false;
    
    let filteredApplicationFee = 0;
    let filteredAmountPaid = 0;
    let filteredBalance = 0;

    rows.forEach(row => {
        if (row.classList.contains('empty-state-row')) {
            row.remove();
        }
    });

    const dataRows = Array.from(rows).filter(row => !row.querySelector('.empty-state'));
    if (dataRows.length === 0) {
        showEmptyState(tbody, monthValue, yearValue);
        return;
    }

    dataRows.forEach(row => {
        const block = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const paymentDate = row.cells[5].textContent;
        
        let dateMatch = true;
        if (monthValue || yearValue) {
            if (paymentDate === 'Not paid yet') {
                dateMatch = false;
            } else {
                const date = new Date(paymentDate);
                const month = (date.getMonth() + 1).toString();
                const year = date.getFullYear().toString();
                
                dateMatch = (!monthValue || month === monthValue) && 
                           (!yearValue || year === yearValue);
            }
        }
        
        const matchesSearch = !searchValue || name.includes(searchValue);
        const matchesBlock = !blockValue || block.includes(`block ${blockValue}`);
        
        const matches = matchesSearch && matchesBlock && dateMatch;
        row.style.display = matches ? '' : 'none';
        
        if (matches) {
            hasMatches = true;
            filteredApplicationFee += parseFloat(row.cells[2].textContent.replace('₱', '').replace(/,/g, ''));
            filteredAmountPaid += parseFloat(row.cells[3].textContent.replace('₱', '').replace(/,/g, ''));
            filteredBalance += parseFloat(row.cells[4].textContent.replace('₱', '').replace(/,/g, ''));
        }
    });

    updateTotals(filteredApplicationFee, filteredAmountPaid, filteredBalance);

    if (!hasMatches) {
        showEmptyState(tbody, monthValue, yearValue);
    }
}

function updateTotals(applicationFee, amountPaid, balance) {
    const tfoot = document.querySelector('.uni-table tfoot');
    if (tfoot) {
        const totalRow = tfoot.querySelector('.total-row');
        totalRow.cells[2].innerHTML = `<strong>₱${applicationFee.toFixed(2)}</strong>`;
        totalRow.cells[3].innerHTML = `<strong>₱${amountPaid.toFixed(2)}</strong>`;
        totalRow.cells[4].innerHTML = `<strong>₱${balance.toFixed(2)}</strong>`;
    }
}

function showEmptyState(tbody, monthValue, yearValue) {
    const emptyRow = document.createElement('tr');
    emptyRow.classList.add('empty-state-row');
    
    let message = 'No matching records found';
    if (monthValue || yearValue) {
        const month = monthValue ? document.getElementById('monthFilter').options[monthValue].text : '';
        const year = yearValue || '';
        message = `No paid application fee found for ${month} ${year}`.trim();
    }

    emptyRow.innerHTML = `
        <td colspan="6" class="empty-state">
            <i class="fas fa-search"></i>
            <p>${message}</p>
        </td>`;
    tbody.appendChild(emptyRow);
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const blockFilter = document.getElementById('blockFilter');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    
    if (searchInput) searchInput.addEventListener('keyup', filterIncome);
    if (blockFilter) blockFilter.addEventListener('change', filterIncome);
    if (monthFilter) monthFilter.addEventListener('change', filterIncome);
    if (yearFilter) yearFilter.addEventListener('change', filterIncome);
    
    filterIncome();
});
