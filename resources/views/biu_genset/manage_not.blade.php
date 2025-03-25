@extends('biu_layout.admin')

@section('title', 'BI-U: Notification SMS Management')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<div class="table-header">
    <h3><i class="fas fa-bell"></i> Notification SMS Management</h3>
    <div class="header-controls">
        <div class="search-container">
            <input type="text" id="noticeSearchInput" placeholder="Search notices..." onkeyup="NoticeModule.filterNoticeTable()">
            <i class="fas fa-search search-icon"></i>
        </div>
        @if($userRole->hasPermission('add-new-notice'))
        <button class="add-btn" onclick="NoticeModule.showNoticeModal()">
            <i class="fas fa-plus"></i> New Notice
        </button>
        @endif
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Announcement</th>
                    <th>Date Created</th>
                    <th>Date Updated</th>
                    @if($userRole->hasPermission('edit-notice') || $userRole->hasPermission('delete-notice'))
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr>
                        <td>{{ $notification->type }}</td>
                        <td style="max-width: 300px; white-space: normal; overflow: hidden; text-overflow: ellipsis; line-height: 1.5em; max-height: 3em;">
                            {{ $notification->announcement }}
                        </td>
                        <td>{{ $notification->created_at->format('M d, Y') }}</td>
                        <td>{{ $notification->updated_at->format('M d, Y') }}</td>
                        @if($userRole->hasPermission('edit-notice') || $userRole->hasPermission('delete-notice'))
                            <td>
                                <div class="action-buttons">
                                    @if($userRole->hasPermission('edit-notice'))
                                        <button class="btn_uni btn-view" onclick="NoticeModule.editNotice({{ $notification->notice_id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    @endif
                                    @if($userRole->hasPermission('delete-notice'))
                                        <button class="btn_uni btn-deactivate" onclick="NoticeModule.deleteNotice({{ $notification->notice_id }})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="{{ ($userRole->hasPermission('edit-notice') || $userRole->hasPermission('delete-notice')) ? '5' : '4' }}" class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <p>No notifications found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Add pagination links -->
        {{ $notifications->links('pagination.custom') }}
    </div>
</div>

<!-- Notice Modal -->
<div id="noticeModal" class="modal">
    <div class="modal-content">
        <h3>Add New Notice</h3>
        <form id="noticeForm">
            <div class="form-group">
                <label for="noticeType">Type</label>
                <input type="text" class="form-control" id="noticeType" name="type" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="noticeAnnouncement">Announcement</label>
                <textarea class="form-control" id="noticeAnnouncement" name="announcement" placeholder="Enter notice statement/message here..." required></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="NoticeModule.closeNoticeModal()">Close</button>
                <button type="button" class="btn_modal btn_verify" onclick="NoticeModule.saveNotice()">Save Notice</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Notice Modal -->
<div id="noticeEditModal" class="modal">
    <div class="modal-content">
        <h3>Edit Notice</h3>
        <form id="editNoticeForm" onsubmit="NoticeModule.updateNotice(event)">
            <input type="hidden" id="editNoticeId">
            <div class="form-group">
                <label for="editNoticeType">Type</label>
                <input type="text" class="form-control" id="editNoticeType" name="type" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="editNoticeAnnouncement">Announcement</label>
                <textarea class="form-control" id="editNoticeAnnouncement" name="announcement" required></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="NoticeModule.closeEditModal()">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="noticeDeleteModal" class="modal">
    <div class="modal-content">
        <h3>Delete Notice</h3>
        <p>Are you sure you want to delete this notice?</p>
        <p>This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="NoticeModule.closeDeleteModal()">Cancel</button>
            <button type="button" class="btn_modal btn_delete" onclick="NoticeModule.confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="noticeResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="noticeResultIcon"></div>
        <h3 id="noticeResultTitle">Success</h3>
        <p id="noticeResultMessage"></p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_verify" onclick="NoticeModule.closeResultModal()">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/notice.js') }}"></script>
@endsection
