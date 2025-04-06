const NoticeModule = {
    selectedNoticeId: null,

    showNoticeModal() {
        const modal = document.getElementById('noticeModal');
        const form = document.getElementById('noticeForm');
        form.reset();
        this.clearValidationErrors();
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeNoticeModal() {
        const modal = document.getElementById('noticeModal');
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

    async saveNotice() {
        const type = document.getElementById('noticeType').value;
        const announcement = document.getElementById('noticeAnnouncement').value;

        const formData = {
            type,
            announcement,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        try {
            const response = await fetch('/notifications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': formData._token,
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                this.closeNoticeModal();
                this.showResultModal(true, data.message);
            } else {
                if (data.field) {
                    const input = document.getElementById('notice' + data.field.charAt(0).toUpperCase() + data.field.slice(1));
                    const feedback = input.nextElementSibling;
                    input.classList.add('is-invalid');
                    feedback.textContent = data.message;
                    feedback.style.display = 'block';
                } else {
                    this.showResultModal(false, data.message);
                }
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to save notice. Please try again.');
        }
    },

    async editNotice(noticeId) {
        try {
            const response = await fetch(`/notifications/${noticeId}/edit`);
            const data = await response.json();

            if (data.success) {
                const modal = document.getElementById('noticeEditModal');
                document.getElementById('editNoticeId').value = data.notice.notice_id;
                document.getElementById('editNoticeType').value = data.notice.type;
                document.getElementById('editNoticeAnnouncement').value = data.notice.announcement;

                this.clearValidationErrors();
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('fade-in'), 10);
            } else {
                this.showResultModal(false, data.message);
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to fetch notice details');
        }
    },

    closeEditModal() {
        const modal = document.getElementById('noticeEditModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.clearValidationErrors();
        }, 300);
    },

    async updateNotice(event) {
        event.preventDefault();
        const noticeId = document.getElementById('editNoticeId').value;
        const type = document.getElementById('editNoticeType').value;
        const announcement = document.getElementById('editNoticeAnnouncement').value;

        const formData = {
            type,
            announcement,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };

        try {
            const response = await fetch(`/notifications/${noticeId}`, {
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
                    const input = document.getElementById('editNotice' + data.field.charAt(0).toUpperCase() + data.field.slice(1));
                    const feedback = input.nextElementSibling;
                    input.classList.add('is-invalid');
                    feedback.textContent = data.message;
                    feedback.style.display = 'block';
                } else {
                    this.showResultModal(false, data.message);
                }
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to update notice');
        }
    },

    deleteNotice(noticeId) {
        this.selectedNoticeId = noticeId;
        const modal = document.getElementById('noticeDeleteModal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeDeleteModal() {
        const modal = document.getElementById('noticeDeleteModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            this.selectedNoticeId = null;
        }, 300);
    },

    async confirmDelete() {
        if (!this.selectedNoticeId) return;

        try {
            const response = await fetch(`/notifications/${this.selectedNoticeId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            this.closeDeleteModal();
            this.showResultModal(data.success, data.message);
        } catch (error) {
            this.showResultModal(false, 'Failed to delete notice');
        }
    },

    async searchNotice() {
        const searchInput = document.getElementById('noticeSearchInput');
        const query = searchInput.value.trim();
        const tableBody = document.querySelector('.uni-table tbody');
        const paginationContainer = document.querySelector('.pagination-container');

        const permissions = document.getElementById('userPermissions');
        const canEdit = permissions.dataset.canEdit === 'true';
        const canDelete = permissions.dataset.canDelete === 'true';
        const hasPermissions = canEdit || canDelete;

        try {
            const response = await fetch(`/notifications/search?query=${encodeURIComponent(query)}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });

            const data = await response.json();

            if (data.success) {
                tableBody.innerHTML = '';

                if (data.notifications.length === 0) {
                    const colspan = document.querySelector('.uni-table thead tr').children.length;
                    tableBody.innerHTML = `
                        <tr class="empty-row">
                            <td colspan="${colspan}" class="empty-state">
                                <i class="fas fa-bell-slash"></i>
                                <p>No notifications found</p>
                            </td>
                        </tr>
                    `;
                } else {
                    data.notifications.forEach(notice => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${notice.type}</td>
                            <td style="max-width: 300px; white-space: normal; overflow: hidden; text-overflow: ellipsis; line-height: 1.5em; max-height: 3em;">
                                ${notice.announcement}
                            </td>
                            <td>${notice.created_at}</td>
                            <td>${notice.updated_at}</td>
                            ${hasPermissions ? `
                            <td>
                                <div class="action-buttons">
                                    ${canEdit ? `
                                        <button class="btn_uni btn-view" onclick="NoticeModule.editNotice(${notice.notice_id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    ` : ''}
                                    ${canDelete ? `
                                        <button class="btn_uni btn-deactivate" onclick="NoticeModule.deleteNotice(${notice.notice_id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    ` : ''}
                                </div>
                            </td>` : ''}
                        `;
                        tableBody.appendChild(row);
                    });
                }

                if (paginationContainer) {
                    paginationContainer.style.display = query ? 'none' : 'flex';
                }
            } else {
                this.showResultModal(false, data.message);
            }
        } catch (error) {
            this.showResultModal(false, 'Failed to search notifications');
        }
    },

    showResultModal(success, message) {
        const modal = document.getElementById('noticeResultModal');
        const icon = document.getElementById('noticeResultIcon');
        const title = document.getElementById('noticeResultTitle');
        const messageElement = document.getElementById('noticeResultMessage');

        icon.className = success ? 'success' : 'warning';
        icon.innerHTML = success ?
            '<i class="fas fa-check-circle"></i>' :
            '<i class="fas fa-exclamation-triangle"></i>';
        title.textContent = success ? 'Success' : 'Warning';
        messageElement.textContent = message;

        modal.setAttribute('data-refresh', success ? 'true' : 'false');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('fade-in'), 10);
    },

    closeResultModal() {
        const modal = document.getElementById('noticeResultModal');
        modal.classList.remove('fade-in');
        setTimeout(() => {
            modal.style.display = 'none';
            if (modal.getAttribute('data-refresh') === 'true') {
                window.location.reload();
            }
        }, 300);
    },
};

window.addEventListener('click', function (event) {
    const modals = [
        'noticeModal',
        'noticeEditModal',
        'noticeDeleteModal',
        'noticeResultModal'
    ];

    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            if (modalId === 'noticeResultModal') {
                NoticeModule.closeResultModal();
            } else if (modalId === 'noticeDeleteModal') {
                NoticeModule.closeDeleteModal();
            } else if (modalId === 'noticeEditModal') {
                NoticeModule.closeEditModal();
            } else {
                NoticeModule.closeNoticeModal();
            }
        }
    });
});
