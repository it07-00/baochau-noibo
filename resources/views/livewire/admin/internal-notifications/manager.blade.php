<div>
    <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3"><i class="fa-solid fa-bullhorn fs-5"></i></span>
            <div>
                <h4 class="fw-bold text-body mb-1">Thông báo nội bộ</h4>
                <p class="text-secondary mb-0">Soạn và gửi thông tin đến đúng nhóm nhân viên.</p>
            </div>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
            wire:click="$dispatch('openComposeModal')">
            <i class="fa-solid fa-pen-to-square"></i>
            Soạn thông báo mới
        </button>
    </div>

    {{-- Sent notifications table --}}
    <div class="card border shadow-sm">
        <div class="card-header bg-body border-bottom p-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div>
                <h6 class="fw-bold text-body mb-1"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Thông báo đã gửi</h6>
                <p class="text-secondary small mb-0">Tối đa 50 thông báo gần nhất do bạn gửi.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">{{ number_format($sentNotifications->count()) }} thông báo</span>
        </div>
        <div class="card-body p-0">
            @if($sentNotifications->isEmpty())
                <div class="text-center py-5 px-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body-tertiary text-secondary p-4 mb-3"><i class="fa-solid fa-envelope-open-text fs-3"></i></span>
                    <h6 class="fw-bold text-body mb-1">Chưa có thông báo đã gửi</h6>
                    <p class="text-secondary mb-3">Thông báo đầu tiên của bạn sẽ xuất hiện tại đây.</p>
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="$dispatch('openComposeModal')"><i class="fa-solid fa-plus me-1"></i>Soạn thông báo</button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th class="ps-4">Tiêu đề</th>
                                <th>Nội dung</th>
                                <th>Người nhận</th>
                                <th class="text-center">Số người</th>
                                <th>Ngày gửi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sentNotifications as $notif)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $notif->title }}</td>
                                    <td class="text-secondary">
                                        <span class="d-inline-block text-truncate w-100" title="{{ $notif->message }}">
                                            {{ $notif->message }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $notif->recipients_label }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info">{{ $notif->recipient_count }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ \Carbon\Carbon::parse($notif->sent_at)->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Compose Modal --}}
    <div wire:ignore.self class="modal fade" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white" id="composeModalLabel">
                        <i class="fa-solid fa-bullhorn-fill me-2"></i>Soạn thông báo nội bộ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <form wire:submit.prevent="send">
                    <div class="modal-body p-4">

                        {{-- Title --}}
                        <div class="mb-3">
                            <label for="notification-title" class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="notification-title" wire:model.defer="title" placeholder="Nhập tiêu đề thông báo...">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Body --}}
                        <div class="mb-3">
                            <label for="notification-body" class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('body') is-invalid @enderror"
                                id="notification-body" wire:model.defer="body" rows="5"
                                placeholder="Nhập nội dung thông báo..."></textarea>
                            @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Recipient type --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Đối tượng nhận <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="recipientType"
                                        id="recAll" value="all">
                                    <label class="form-check-label" for="recAll">Tất cả nhân viên</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="recipientType"
                                        id="recRoles" value="roles">
                                    <label class="form-check-label" for="recRoles">Theo vai trò</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="recipientType"
                                        id="recUsers" value="users">
                                    <label class="form-check-label" for="recUsers">Chọn người cụ thể</label>
                                </div>
                            </div>
                        </div>

                        {{-- Role selection --}}
                        @if($recipientType === 'roles')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Chọn vai trò</label>
                                <div class="row row-cols-2 row-cols-md-3 g-2">
                                    @foreach($allRoles as $role)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model.defer="selectedRoles"
                                                    value="{{ $role['value'] }}"
                                                    id="role_{{ $role['value'] }}">
                                                <label class="form-check-label" for="role_{{ $role['value'] }}">
                                                    {{ $role['label'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedRoles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        {{-- User selection --}}
                        @if($recipientType === 'users')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Chọn người nhận</label>
                                <div class="border rounded p-3">
                                    @foreach($allUsers as $u)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                wire:model.defer="selectedUsers"
                                                value="{{ $u->id }}"
                                                id="user_{{ $u->id }}">
                                            <label class="form-check-label" for="user_{{ $u->id }}">
                                                {{ $u->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedUsers') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer bg-body-tertiary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="send" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="fa-solid fa-paper-plane-fill me-1" wire:loading.remove wire:target="send"></i>
                            Gửi thông báo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('openComposeModal', () => {
        new bootstrap.Modal(document.getElementById('composeModal')).show();
    });

    window.addEventListener('closeComposeModal', () => {
        const el = document.getElementById('composeModal');
        const modal = bootstrap.Modal.getInstance(el);
        if (modal) modal.hide();
    });
</script>
@endpush
