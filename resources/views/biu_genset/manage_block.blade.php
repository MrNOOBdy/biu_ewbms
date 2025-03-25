@extends('biu_layout.admin')

@section('title', 'BI-U: Block Management')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">
<script src="{{ asset('js/block.js') }}"></script>
<div class="table-header">
    <h3><i class="fas fa-th-large"></i> Block Management</h3>
    <div class="header-controls">
        <div class="filter-section">
            <select id="blockFilter" onchange="BlockModule.filterBlocks()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                @endforeach
            </select>
        </div>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search barangays..." onkeyup="BlockModule.filterBlocks()">
            <i class="fas fa-search search-icon"></i>
        </div>
        @if($userRole->hasPermission('add-new-block'))
            <button class="add-btn" onclick="BlockModule.showNewBlockModal()">
                <i class="fas fa-plus"></i> Add New Block
            </button>
        @endif
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>Block ID</th>
                    <th>Barangays</th>
                    @if($userRole->hasPermission('edit-block') || $userRole->hasPermission('delete-block'))
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($blocks as $block)
                    <tr>
                        <td>{{ $block->block_id }}</td>

                        <td style="max-width: 0; white-space: normal; overflow: hidden; text-overflow: ellipsis;">
                            @if(is_array($block->barangays))
                                {{ implode(', ', $block->barangays) }}
                            @else
                                {{ $block->barangays }}
                            @endif
                        </td>
                        @if($userRole->hasPermission('edit-block') || $userRole->hasPermission('delete-block'))
                            <td>
                                <div class="action-buttons">
                                    @if($userRole->hasPermission('edit-block'))
                                        <button class="btn_uni btn-view" onclick="BlockModule.showEditBlockModal({{ $block->block_id }})">
                                            <i class="fas fa-edit"></i> Edit Block
                                        </button>
                                    @endif
                                    @if($userRole->hasPermission('delete-block'))
                                        <button class="btn_uni btn-deactivate" onclick="BlockModule.showDeleteBlockModal({{ $block->block_id }})">
                                            <i class="fas fa-trash"></i> Delete Block
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $userRole->hasPermission('edit-block') || $userRole->hasPermission('delete-block') ? '3' : '2' }}" class="text-center">No blocks found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Add pagination links -->
        {{ $blocks->links('pagination.custom') }}
    </div>
</div>

<!-- Add New Block Modal -->
<div id="newBlockModal" class="modal">
    <div class="modal-content">
        <h3>Add New Block</h3>
        <form id="newBlockForm">
            <div class="form-group">
                <label for="new_block_id">Block ID</label>
                <input type="number" class="form-control" id="new_block_id" name="block_id" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="barangays">Barangays (One per line)</label>
                <textarea class="form-control" id="barangays" name="barangays" 
                    rows="4" required 
                    placeholder="Enter barangay names (one per line)"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="BlockModule.closeNewBlockModal()">Cancel</button>
                <button type="button" class="btn_modal btn_verify" onclick="BlockModule.saveNewBlock()">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Block Modal -->
<div id="editBlockModal" class="modal">
    <div class="modal-content">
        <h3>Edit Block</h3>
        <form id="editBlockForm">
            <input type="hidden" id="originalBlockId">
            <div class="form-group">
                <label for="edit_block_id">Block Number</label>
                <input type="number" class="form-control" id="edit_block_id" name="block_id" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="form-group">
                <label for="edit_barangays">Barangays (One per line)</label>
                <textarea class="form-control" id="edit_barangays" name="barangays" 
                    rows="4" required 
                    placeholder="Enter barangay names (one per line)"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="BlockModule.closeEditBlockModal()">Cancel</button>
                <button type="button" class="btn_modal btn_verify" onclick="BlockModule.updateBlock()">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Block Modal -->
<div id="deleteBlockModal" class="modal">
    <div class="modal-content">
        <h3>Delete Block</h3>
        <p>Are you sure you want to delete this block?</p>
        <p>This will also remove all associated barangays.</p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_cancel" onclick="BlockModule.closeDeleteBlockModal()">Cancel</button>
            <button type="button" class="btn_modal btn_delete" onclick="BlockModule.confirmDeleteBlock()">Delete</button>
        </div>
    </div>
</div>

<!-- Result Modal -->
<div id="blockResultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="blockResultIcon"></div>
        <h3 id="blockResultTitle">Success</h3>
        <p id="blockResultMessage"></p>
        <div class="modal-actions">
            <button type="button" class="btn_modal btn_verify" onclick="BlockModule.closeResultModal()">OK</button>
        </div>
    </div>
</div>

@endsection
