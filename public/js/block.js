const BlockModule = {
    selectedBlockId: null,

    showNewBlockModal() {
        const modal = document.getElementById('newBlockModal');
        document.getElementById('newBlockForm').reset();
        this.clearValidationErrors();
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeNewBlockModal() {
        const modal = document.getElementById('newBlockModal');
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

    async saveNewBlock() {
        const formData = {
            block_id: document.getElementById('new_block_id').value,
            barangays: document.getElementById('barangays').value,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        try {
            const response = await fetch('/blocks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData._token,
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.closeNewBlockModal();
                this.showResultModal(true, data.message);
            } else {
                if (data.field) {
                    const input = document.getElementById(data.field);
                    if (input) {
                        const feedback = input.nextElementSibling;
                        input.classList.add('is-invalid');
                        feedback.textContent = data.message;
                        feedback.style.display = 'block';
                    } else {
                        this.showResultModal(false, data.message);
                    }
                } else {
                    this.showResultModal(false, data.message);
                }
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to save block');
        }
    },

    async showEditBlockModal(blockId) {
        try {
            const response = await fetch(`/blocks/${blockId}/edit`);
            const data = await response.json();

            if (data.success) {
                const modal = document.getElementById('editBlockModal');
                document.getElementById('originalBlockId').value = blockId;
                document.getElementById('edit_block_id').value = data.block.block_id;
                document.getElementById('edit_barangays').value = data.block.barangays;
                
                this.clearValidationErrors();
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                this.showResultModal(false, data.message);
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to fetch block details');
        }
    },

    closeEditBlockModal() {
        const modal = document.getElementById('editBlockModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.clearValidationErrors();
        }, 300);
    },

    async updateBlock() {
        const blockId = document.getElementById('originalBlockId').value;
        const formData = {
            block_id: document.getElementById('edit_block_id').value,
            barangays: document.getElementById('edit_barangays').value,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        try {
            const response = await fetch(`/blocks/${blockId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData._token,
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.closeEditBlockModal();
                this.showResultModal(true, data.message);
            } else {
                if (data.field) {
                    const input = document.getElementById(data.field);
                    if (input) {
                        const feedback = input.nextElementSibling;
                        input.classList.add('is-invalid');
                        feedback.textContent = data.message;
                        feedback.style.display = 'block';
                    } else {
                        this.showResultModal(false, data.message);
                    }
                } else {
                    this.showResultModal(false, data.message);
                }
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to update block');
        }
    },

    showDeleteBlockModal(blockId) {
        this.selectedBlockId = blockId;
        const modal = document.getElementById('deleteBlockModal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeDeleteBlockModal() {
        const modal = document.getElementById('deleteBlockModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.selectedBlockId = null;
        }, 300);
    },

    async confirmDeleteBlock() {
        if (!this.selectedBlockId) return;

        try {
            const response = await fetch(`/blocks/${this.selectedBlockId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();
            this.closeDeleteBlockModal();

            if (data.success) {
                this.showResultModal(true, data.message);
            } else {
                if (data.isUsedByConsumers) {
                    this.showResultModal(false, data.message);
                } else {
                    this.showResultModal(false, data.message);
                }
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to delete block');
        }
    },

    showResultModal(success, message) {
        const modal = document.getElementById('blockResultModal');
        const icon = document.getElementById('blockResultIcon');
        const title = document.getElementById('blockResultTitle');
        const messageElement = document.getElementById('blockResultMessage');

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
        const modal = document.getElementById('blockResultModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            if (modal.getAttribute('data-refresh') === 'true') {
                window.location.reload();
            }
        }, 300);
    },

    filterBlocks() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const blockFilter = document.getElementById('blockFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const rows = tbody.getElementsByTagName('tr');
        const pagination = document.querySelector('.pagination-wrapper');

        let hasVisibleRows = false;

        for (const row of rows) {
            if (!row.classList.contains('empty-row')) {
                const blockId = row.cells[0].textContent;
                const barangays = row.cells[1].textContent.toLowerCase();
                
                const matchesSearch = barangays.includes(searchInput);
                const matchesBlock = !blockFilter || blockId === blockFilter;

                if (matchesSearch && matchesBlock) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            }
        }

        if (pagination) {
            pagination.style.display = (searchInput || blockFilter) ? 'none' : 'flex';
        }

        const existingEmptyRow = tbody.querySelector('.empty-row');
        if (!hasVisibleRows) {
            if (!existingEmptyRow) {
                const colspan = document.querySelector('.uni-table thead th:last-child').cellIndex + 1;
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-row';
                emptyRow.innerHTML = `
                    <td colspan="${colspan}" class="text-center">
                        No blocks found matching the search criteria
                    </td>`;
                tbody.appendChild(emptyRow);
            } else {
                existingEmptyRow.style.display = '';
            }
        } else if (existingEmptyRow) {
            existingEmptyRow.style.display = 'none';
        }
    }
};

window.addEventListener('click', function(event) {
    const modals = {
        'newBlockModal': BlockModule.closeNewBlockModal,
        'editBlockModal': BlockModule.closeEditBlockModal,
        'deleteBlockModal': BlockModule.closeDeleteBlockModal,
        'blockResultModal': BlockModule.closeResultModal
    };

    Object.entries(modals).forEach(([modalId, closeFunction]) => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            closeFunction.call(BlockModule);
        }
    });
});
