@extends('biu_layout.admin')

@section('title', 'BI-U: MRs Block Assignment')

@section('tab-content')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/tbl_pagination.css') }}">

<style>
    #substitutionModal { z-index: 1050; }
    #assignBlockModal { z-index: 1050; }
    #resultModal { z-index: 1060; }

    .modal-content { z-index: 1; }
    #resultModal .modal-content { z-index: 2; }
</style>

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
                                <button class="btn_uni btn-substitute" title="Create Substitution" onclick="showSubstitutionModal('{{ $reader->user_id }}')">
                                    <i class="fas fa-user-clock"></i>
                                    Add Substitute
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

<!-- Substitution Modal -->
<div id="substitutionModal" class="modal">
    <div class="modal-content">
        <h3><i class="fas fa-user-clock"></i> Create Substitution</h3>
        <form id="substitutionForm" onsubmit="handleSubstitutionSubmit(event)">
            @csrf
            <input type="hidden" id="absent_reader_id" name="absent_reader_id">
            <div class="form-group">
                <label for="substitute_reader_id">Substitute Reader:</label>
                <select id="substitute_reader_id" name="substitute_reader_id" class="form-control" required>
                    <option value="">Select a substitute reader</option>
                    @foreach($meterReaders as $sub)
                        @if($sub->status === 'activate')
                            <option value="{{ $sub->user_id }}">{{ $sub->firstname }} {{ $sub->lastname }}</option>
                        @endif
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="text" id="start_date" name="start_date" class="form-control date-picker" placeholder="Select date" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="text" id="end_date" name="end_date" class="form-control date-picker" placeholder="Select date" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="reason">Reason for Substitution:</label>
                <textarea id="reason" name="reason" class="form-control" required></textarea>
                <div class="invalid-feedback"></div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn_modal btn_cancel" onclick="closeModal('substitutionModal')">Cancel</button>
                <button type="submit" class="btn_modal btn_verify">Create Substitution</button>
            </div>
        </form>
    </div>
</div>

<!-- Active Substitutions Section -->
<div class="substitutions-section">
    <h3><i class="fas fa-exchange-alt"></i> Active Substitutions</h3>
    <div id="activeSubstitutions" class="substitutions-list"></div>
</div>
<script src="{{ asset('js/mrblock.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDatePicker = flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            minDate: "today",
            onChange: function(selectedDates, dateStr) {
                endDatePicker.set('minDate', dateStr);
            }
        });
    
        const endDatePicker = flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            minDate: "today"
        });
    
        loadActiveSubstitutions();
    });
    </script>
@endsection