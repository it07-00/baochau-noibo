<div>
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="page-title mb-1 fw-bold">Thông báo nội bộ</h4>
            <p class="text-muted mb-0">Soạn và gửi thông báo đến nhân viên trong công ty.</p>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2"
            wire:click="$dispatch('openComposeModal')">
            <i class="fa-solid fa-bullhorn-fill"></i>
            Soạn thông báo mới
        </button>
    </div>

    {{-- Sent notifications table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-semibold">Đã gửi gần đây</h6>
        </div>
        <div class="card-body p-0">
            @if($sentNotifications->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-bullhorn fs-1 d-block mb-2 opacity-50"></i>
                    Chưa có thông báo nào được gửi.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                    <td class="text-muted" style="max-width: 300px;">
                                        <span class="d-inline-block text-truncate" style="max-width: 280px;" title="{{ $notif->message }}">
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold text-white" id="composeModalLabel">
                        <i class="fa-solid fa-bullhorn-fill me-2"></i>Soạn thông báo nội bộ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <form wire:submit.prevent="send">
                    <div class="modal-body p-4">

                        {{-- Title --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                wire:model.defer="title" placeholder="Nhập tiêu đề thông báo...">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Body --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('body') is-invalid @enderror"
                                wire:model.defer="body" rows="5"
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
                                <div style="max-height: 200px; overflow-y: auto;" class="border rounded p-2">
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
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
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
