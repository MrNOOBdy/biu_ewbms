const BillRateModule = {
    selectedBillRateId: null,

    showAddModal() {
        const modal = document.getElementById('addBillRateModal');
        const form = document.getElementById('billRateForm');
        form.reset();
        this.clearValidationErrors();
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeAddModal() {
        const modal = document.getElementById('addBillRateModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.clearValidationErrors();
        }, 300);
    },

    clearValidationErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        document.querySelectorAll('.form-control').forEach(el => {
            el.classList.remove('is-invalid');
        });
    },

    async saveBillRate() {
        const formData = {
            consumer_type: document.getElementById('consumer_type').value,
            cubic_meter: document.getElementById('cubic_meter').value,
            value: document.getElementById('value').value,
            excess_value_per_cubic: document.getElementById('excess_value_per_cubic').value,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        try {
            const response = await fetch('/billRates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData._token,
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.closeAddModal();
                this.showResultModal(true, data.message);
            } else {
                if (data.field) {
                    const input = document.getElementById(data.field);
                    const feedback = input.nextElementSibling;
                    input.classList.add('is-invalid');
                    feedback.textContent = data.message;
                    feedback.style.display = 'block';
                } else {
                    this.showWarningModal(data.message);
                }
            }
        } catch (error) {
            this.showWarningModal('Failed to save bill rate');
        }
    },

    async editBillRate(billrateId) {
        try {
            const response = await fetch(`/billRates/${billrateId}/edit`);
            const data = await response.json();

            if (data.success) {
                const modal = document.getElementById('editBillRateModal');
                document.getElementById('editBillRateId').value = data.billRate.billrate_id;
                document.getElementById('edit_consumer_type').value = data.billRate.consumer_type;
                document.getElementById('edit_cubic_meter').value = data.billRate.cubic_meter;
                document.getElementById('edit_value').value = data.billRate.value;
                document.getElementById('edit_excess_value_per_cubic').value = data.billRate.excess_value_per_cubic;
                
                this.clearValidationErrors();
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                this.showWarningModal(data.message);
            }
        } catch (error) {
            this.showWarningModal('Failed to fetch bill rate details');
        }
    },

    closeEditModal() {
        const modal = document.getElementById('editBillRateModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.clearValidationErrors();
        }, 300);
    },

    async updateBillRate() {
        const billrateId = document.getElementById('editBillRateId').value;
        const formData = {
            consumer_type: document.getElementById('edit_consumer_type').value,
            cubic_meter: document.getElementById('edit_cubic_meter').value,
            value: document.getElementById('edit_value').value,
            excess_value_per_cubic: document.getElementById('edit_excess_value_per_cubic').value,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        try {
            const response = await fetch(`/billRates/${billrateId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData._token,
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.closeEditModal();
                this.showResultModal(true, data.message);
            } else {
                if (data.field) {
                    const input = document.getElementById('edit_' + data.field);
                    const feedback = input.nextElementSibling;
                    input.classList.add('is-invalid');
                    feedback.textContent = data.message;
                    feedback.style.display = 'block';
                } else {
                    this.showWarningModal(data.message);
                }
            }
        } catch (error) {
            this.showWarningModal('Failed to update bill rate');
        }
    },

    showDeleteBillRateModal(billrateId) {
        this.selectedBillRateId = billrateId;
        const modal = document.getElementById('deleteBillRateModal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeDeleteBillRateModal() {
        const modal = document.getElementById('deleteBillRateModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.selectedBillRateId = null;
        }, 300);
    },

    async confirmDeleteBillRate() {
        if (!this.selectedBillRateId) return;

        try {
            const response = await fetch(`/billRates/${this.selectedBillRateId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();
            this.closeDeleteBillRateModal();

            if (data.success) {
                this.showResultModal(true, data.message);
            } else {
                if (data.isUsedByConsumers) {
                    this.showResultModal(false, data.message);
                } else {
                    this.showWarningModal(data.message);
                }
            }
        } catch (error) {
            this.closeDeleteBillRateModal();
            this.showWarningModal('Failed to delete bill rate');
        }
    },

    showResultModal(success, message) {
        const modal = document.getElementById('billRateResultModal');
        const icon = document.getElementById('billRateResultIcon');
        const title = document.getElementById('billRateResultTitle');
        const messageElement = document.getElementById('billRateResultMessage');

        icon.className = success ? 'success' : 'error';
        icon.innerHTML = success ? 
            '<i class="fas fa-check-circle"></i>' : 
            '<i class="fas fa-exclamation-circle"></i>';
        title.textContent = success ? 'Success' : 'Error';
        messageElement.textContent = message;

        modal.setAttribute('data-refresh', success ? 'true' : 'false');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeResultModal() {
        const modal = document.getElementById('billRateResultModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            if (modal.getAttribute('data-refresh') === 'true') {
                window.location.reload();
            }
        }, 300);
    },

    showWarningModal(message) {
        const modal = document.getElementById('warningBillRateModal');
        document.getElementById('warningBillRateMessage').textContent = message;
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeWarningModal() {
        const modal = document.getElementById('warningBillRateModal');
        modal.classList.remove('fade-in');
        setTimeout(() => modal.style.display = 'none', 300);
    }
};

window.addEventListener('click', function(event) {
    const modals = {
        'addBillRateModal': BillRateModule.closeAddModal,
        'editBillRateModal': BillRateModule.closeEditModal,
        'deleteBillRateModal': BillRateModule.closeDeleteBillRateModal,
        'billRateResultModal': BillRateModule.closeResultModal,
        'warningBillRateModal': BillRateModule.closeWarningModal
    };

    Object.entries(modals).forEach(([modalId, closeFunction]) => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            closeFunction.call(BillRateModule);
        }
    });
});
