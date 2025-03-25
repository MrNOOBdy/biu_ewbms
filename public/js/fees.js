document.addEventListener('DOMContentLoaded', function() {
    const feeForm = document.getElementById('feeForm');
    if (feeForm) {
        feeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateFees();
        });
    }
});

function editFees() {
    const modal = document.getElementById('editFeeModal');
    const applicationFeeInput = document.querySelector('#application_fee');
    const reconnectionFeeInput = document.querySelector('#reconnection_fee');
    
    applicationFeeInput.defaultValue = applicationFeeInput.value;
    reconnectionFeeInput.defaultValue = reconnectionFeeInput.value;
    
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeEditFeeModal() {
    const modal = document.getElementById('editFeeModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

function updateFees() {
    const applicationFee = document.querySelector('#application_fee');
    const reconnectionFee = document.querySelector('#reconnection_fee');
    
    if (applicationFee.value === applicationFee.defaultValue && 
        reconnectionFee.value === reconnectionFee.defaultValue) {
        showFeeResultModal(false, 'No changes were made to update');
        return;
    }

    const formData = {
        application_fee: applicationFee.value,
        reconnection_fee: reconnectionFee.value,
        _token: document.querySelector('meta[name="csrf-token"]').content
    };

    fetch('/update-fees', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData._token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditFeeModal();
            showFeeResultModal(true, 'Fees updated successfully');
            document.getElementById('feeResultModal').setAttribute('data-refresh', 'true');
        } else {
            throw new Error(data.message || 'Failed to update fees');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFeeResultModal(false, error.message || 'Error updating fees');
    });
}

function showFeeResultModal(success, message) {
    const modal = document.getElementById('feeResultModal');
    const icon = document.getElementById('feeResultIcon');
    const title = document.getElementById('feeResultTitle');
    const messageElement = document.getElementById('feeResultMessage');

    if (!success) {
        icon.className = 'warning';
        icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        title.textContent = 'Warning';
    } else {
        icon.className = 'success';
        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
        title.textContent = 'Success';
    }

    messageElement.textContent = message;
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('fade-in'), 10);
}

function closeFeeResultModal() {
    const modal = document.getElementById('feeResultModal');
    modal.classList.remove('fade-in');
    setTimeout(() => {
        modal.style.display = 'none';
        if (modal.getAttribute('data-refresh') === 'true') {
            location.reload();
        }
    }, 300);
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('feeResultModal');
    if (event.target === modal) {
        closeFeeResultModal();
    }
});