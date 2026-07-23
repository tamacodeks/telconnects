@php
    $type = $usersV2Type ?? ($usersV2Config['type'] ?? 'users');
    $labels = $usersV2Config['labels'] ?? [];
@endphp

@if($type === 'users' && !empty($usersV2Config['canManageCorrections']))
    <div class="modal fade v2-users-modal" id="usersV2ResetCorrectionsModal" tabindex="-1" aria-labelledby="usersV2ResetCorrectionsTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usersV2ResetCorrectionsTitle">{{ $labels['resetCorrectionsTitle'] ?? 'Reset Transaction Corrections' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ $labels['close'] ?? 'Close' }}"></button>
                </div>
                <div class="modal-body">
                    <div class="v2-users-reset-date">
                        <span>{{ $labels['date'] ?? 'Date' }}</span>
                        <strong id="usersV2ResetDateLabel"></strong>
                    </div>
                    <label for="usersV2ResetFrom">
                        <span>{{ $labels['from'] ?? 'From' }}</span>
                        <input type="time" id="usersV2ResetFrom" class="form-control">
                    </label>
                    <label for="usersV2ResetTo">
                        <span>{{ $labels['to'] ?? 'To' }}</span>
                        <input type="time" id="usersV2ResetTo" class="form-control">
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="v2-users-btn" data-bs-dismiss="modal">{{ $labels['close'] ?? 'Close' }}</button>
                    <button type="button" class="v2-users-btn v2-users-btn-warning" id="usersV2SubmitResetCorrections">
                        <i class="fa fa-history"></i>
                        <span>{{ $labels['reset'] ?? 'Reset' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
