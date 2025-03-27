@extends('biu_layout.admin')

@section('title', 'BI-U: MRs Block Assignment')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">

<div class="table-header">
    <h3><i class="fas fa-map-marked-alt"></i> Meter Reader Block Assignment</h3>
    <div class="header-controls">
        <div class="search-container">
            <input type="text" id="meterReaderSearchInput" placeholder="Search meter readers..." onkeyup="filterMeterReaderTable()">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="table-container">
        <table class="uni-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Assigned Blocks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($meterReaders as $reader)
                    <tr>
                        <td>{{ $reader->user_id }}</td>
                        <td>{{ $reader->firstname }} {{ $reader->lastname }}</td>
                        <td>{{ $reader->contactnum }}</td>
                        <td>{{ $reader->email }}</td>
                        <td>
                            @if($reader->assigned_blocks)
                                {{ implode(', ', $reader->assigned_blocks) }}
                            @else
                                <span class="text-muted">No blocks assigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $reader->status === 'activate' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($reader->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn_uni btn-view" title="Assign Blocks" onclick="showAssignBlockModal('{{ $reader->user_id }}')">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Assign Block
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>No meter readers found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="pagination-wrapper">
            {{ $meterReaders->links('pagination.custom') }}
        </div>
    </div>
</div>

<!-- Assign Block Modal -->
<div id="assignBlockModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-map-marker-alt"></i> Assign Block</h3>
        <form id="assignBlockForm">
            @csrf
            <input type="hidden" id="reader_id" name="reader_id">
            
            <div class="form-group">
                <label for="block_id">Select Block:</label>
                <select id="block_id" name="blocks" class="form-control">
                    <option value="">Select a block</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->block_id }}">Block {{ $block->block_id }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('assignBlockModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Assign Block</button>
            </div>
        </form>
    </div>
</div>

<!-- Result Modal -->
<div id="resultModal" class="modal">
    <div class="modal-content result-modal">
        <div id="resultIcon"></div>
        <h3 id="resultTitle"></h3>
        <p id="resultMessage"></p>
        <div class="modal-actions">
            <button class="btn_modal btn_verify" onclick="closeModalAndReload()">OK</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/mrblock.js') }}"></script>
@endsection