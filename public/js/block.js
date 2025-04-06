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

    async filterBlocks() {
        const searchInput = document.getElementById('searchInput').value.trim();
        const blockFilter = document.getElementById('blockFilter').value;
        const tbody = document.querySelector('.uni-table tbody');
        const paginationWrapper = document.querySelector('.pagination-wrapper');

        const permissions = document.getElementById('blockPermissions');
        const canEdit = permissions.dataset.canEdit === 'true';
        const canDelete = permissions.dataset.canDelete === 'true';
        const hasPermissions = canEdit || canDelete;

        try {
            const response = await fetch(`/blocks/search?query=${encodeURIComponent(searchInput)}&block_id=${encodeURIComponent(blockFilter)}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tbody.innerHTML = '';

                if (data.blocks.length === 0) {
                    const colspan = document.querySelector('.uni-table thead tr').children.length;
                    tbody.innerHTML = `
                        <tr class="empty-row">
                            <td colspan="${colspan}" class="text-center">
                                <i class="fas fa-th-large"></i>
                                <p>No blocks found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.blocks.forEach(block => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${block.block_id}</td>
                            <td style="max-width: 0; white-space: normal; overflow: hidden; text-overflow: ellipsis;">
                                ${block.barangays}
                            </td>
                            ${hasPermissions ? `
                            <td>
                                <div class="action-buttons">
                                    ${canEdit ? `
                                        <button class="btn_uni btn-view" onclick="BlockModule.showEditBlockModal(${block.block_id})">
                                            <i class="fas fa-edit"></i> Edit Block
                                        </button>
                                    ` : ''}
                                    ${canDelete ? `
                                        <button class="btn_uni btn-deactivate" onclick="BlockModule.showDeleteBlockModal(${block.block_id})">
                                            <i class="fas fa-trash"></i> Delete Block
                                        </button>
                                    ` : ''}
                                </div>
                            </td>` : ''}
                        `;
                        tbody.appendChild(row);
                    });
                }

                if (paginationWrapper) {
                    paginationWrapper.style.display = searchInput || blockFilter ? 'none' : 'flex';
                }
            } else {
                this.showResultModal(false, data.message);
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to search blocks');
        }
    }
};

window.addEventListener('click', function (event) {
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

window.addEventListener('DOMContentLoaded', function () {
    const searchButton = document.querySelector('.btn-search');
    if (searchButton) {
        searchButton.addEventListener('click', () => BlockModule.filterBlocks());
    }
});
