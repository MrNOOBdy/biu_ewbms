function filterIncome() {
    const month = document.getElementById('monthFilter').value.toLowerCase();
    const year = document.getElementById('yearFilter').value.toLowerCase();
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.uni-table tbody tr');

    rows.forEach(row => {
        const dateCell = row.querySelector('td:nth-child(4)');
        if (!dateCell) return;

        const date = new Date(dateCell.textContent);
        const rowMonth = date.getMonth() + 1;
        const rowYear = date.getFullYear();
        const text = row.textContent.toLowerCase();

        const matchesMonth = !month || rowMonth === parseInt(month);
        const matchesYear = !year || rowYear === parseInt(year);
        const matchesSearch = !search || text.includes(search);

        row.style.display = matchesMonth && matchesYear && matchesSearch ? '' : 'none';
    });
}

function filterBalance() {
    const block = document.getElementById('blockFilter').value.toLowerCase();
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.uni-table tbody tr');

    rows.forEach(row => {
        const blockCell = row.querySelector('td:first-child');
        if (!blockCell) return;

        const blockText = blockCell.textContent.toLowerCase();
        const text = row.textContent.toLowerCase();

        const matchesBlock = !block || blockText.includes(`block ${block}`);
        const matchesSearch = !search || text.includes(search);

        row.style.display = matchesBlock && matchesSearch ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    const blockFilter = document.getElementById('blockFilter');

    if (monthFilter && yearFilter) {
        filterIncome();
    }

    if (blockFilter) {
        filterBalance();
    }
});
