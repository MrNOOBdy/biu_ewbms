function filterMeterReaderTable() {
    const searchInput = document.getElementById('meterReaderSearchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.uni-table tbody tr:not(.empty-state-row)');
    let visibleRows = 0;
    
    rows.forEach(row => {
        const textContent = row.textContent.toLowerCase();
        const matches = textContent.includes(searchInput);
        
        if (matches) {
            row.style.display = '';
            visibleRows++;
        } else {
            row.style.display = 'none';
        }
    });

    const existingEmptyRow = document.querySelector('.empty-state-row');
    if (existingEmptyRow) {
        existingEmptyRow.remove();
    }

    if (visibleRows === 0) {
        const tbody = document.querySelector('.uni-table tbody');
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-state-row';
        emptyRow.innerHTML = `
            <td colspan="7" class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>No meter readers found</p>
            </td>
        `;
        tbody.appendChild(emptyRow);
    }
}

function showAssignBlockModal(readerId) {
    const modal = document.getElementById('assignBlockModal');
    document.getElementById('reader_id').value = readerId;
    
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
    
    fetch(`/meter-readers/${readerId}/blocks`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.blocks.length > 0) {
            const selectElement = document.getElementById('block_id');
            selectElement.value = data.blocks[0];
        }
    })
    .catch(error => console.error('Error fetching blocks:', error));
}

document.getElementById('assignBlockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const blockId = document.getElementById('block_id').value;
    const blockSelect = document.getElementById('block_id');
    const invalidFeedback = blockSelect.nextElementSibling;
    
    blockSelect.classList.remove('is-invalid');
    if (invalidFeedback) {
        invalidFeedback.style.display = 'none';
    }
    
    if (!blockId) {
        blockSelect.classList.add('is-invalid');
        if (invalidFeedback) {
            invalidFeedback.textContent = 'Please select a block';
            invalidFeedback.style.display = 'block';
        }
        return;
    }
    
    formData.set('blocks[]', blockId);
    
    fetch('/meter-readers/assign-blocks', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.errors) {
            blockSelect.classList.add('is-invalid');
            if (invalidFeedback) {
                const errorMessage = Array.isArray(data.errors.blocks) 
                    ? data.errors.blocks[0] 
                    : data.errors.blocks;
                invalidFeedback.textContent = errorMessage;
                invalidFeedback.style.display = 'block';
            }
            return;
        }
        
        if (data.success) {
            closeModal('assignBlockModal');
            showResultModal('success', 'Success', data.message);
        } else {
            showResultModal('error', 'Error', data.message || 'Failed to assign block');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResultModal('error', 'Error', 'Failed to assign block');
    });
});

document.getElementById('block_id').addEventListener('change', function() {
    this.classList.remove('is-invalid');
    const feedback = this.nextElementSibling;
    feedback.style.display = 'none';
});

function showResultModal(type, title, message) {
    const modal = document.getElementById('resultModal');
    const icon = document.getElementById('resultIcon');
    const titleElement = document.getElementById('resultTitle');
    const messageElement = document.getElementById('resultMessage');

    icon.innerHTML = type === 'success' 
        ? '<i class="fas fa-check-circle" style="color: #28a745;"></i>' 
        : '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
    icon.className = type === 'success' ? 'success-icon' : 'error-icon';
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    
    if (type === 'success') {
        modal.setAttribute('data-refresh', 'true');
    }
    
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeModal(modalId) {
    if (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('fade-in');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }
}

function closeModalAndReload() {
    closeModal('resultModal');
    setTimeout(() => {
        window.location.reload();
    }, 300);
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        if (event.target.id === 'resultModal') {
            closeModalAndReload();
        } else {
            closeModal(event.target.id);
        }
    }
}
