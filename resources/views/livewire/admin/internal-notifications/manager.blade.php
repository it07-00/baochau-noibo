<div>
    @section('title', 'Thông báo nội bộ')
    @section('page_title', 'Thông báo nội bộ')

    <header class="mb-4">
        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 mb-2"><i class="fa-solid fa-bullhorn me-1"></i>Truyền thông nội bộ</span>
                <h4 class="fw-bold text-body mb-1">Thông báo nội bộ</h4>
                <p class="text-secondary mb-0">Gửi thông tin đến toàn công ty, theo vai trò hoặc từng nhân viên.</p>
            </div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3" wire:click="$dispatch('openComposeModal')"><i class="fa-solid fa-pen-to-square"></i>Soạn thông báo</button>
        </div>
        <div class="d-flex align-items-end gap-2 mt-4">
            <div class="h4 fw-bold text-body mb-0">{{ number_format($sentNotifications->count()) }}</div>
            <div class="small text-secondary pb-1">thông báo đã gửi gần đây</div>
        </div>
    </header>

    <section class="card border shadow-none overflow-hidden" aria-labelledby="sent-notifications-title">
        <div class="card-header bg-body border-bottom p-3">
            <h6 id="sent-notifications-title" class="fw-bold text-body mb-1">Lịch sử gửi</h6>
            <p class="text-secondary small mb-0">Theo dõi nội dung, phạm vi và thời điểm phát hành.</p>
        </div>

        @if($sentNotifications->isEmpty())
            <div class="card-body text-center py-5 px-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary p-4 mb-3"><i class="fa-regular fa-paper-plane fs-2"></i></span>
                <h6 class="fw-bold text-body mb-1">Chưa có thông báo nào</h6>
                <p class="text-secondary mb-3">Soạn thông báo đầu tiên để cập nhật thông tin cho nhân viên.</p>
                <button type="button" class="btn btn-outline-primary" wire:click="$dispatch('openComposeModal')"><i class="fa-solid fa-plus me-1"></i>Soạn thông báo</button>
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($sentNotifications as $notif)
                    <article class="list-group-item p-3 p-lg-4" wire:key="notification-{{ $notif->batch_id }}">
                        <div class="row g-3 align-items-start">
                            <div class="col-auto">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary p-3"><i class="fa-solid fa-envelope"></i></span>
                            </div>
                            <div class="col min-w-0">
                                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-2">
                                    <div>
                                        <h6 class="fw-bold text-body mb-1">{{ $notif->title }}</h6>
                                        <div class="small text-secondary"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($notif->sent_at)->format('H:i · d/m/Y') }}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-body-tertiary text-body border"><i class="fa-solid fa-users me-1"></i>{{ $notif->recipients_label }}</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">{{ number_format($notif->recipient_count) }} người nhận</span>
                                    </div>
                                </div>
                                <p class="text-secondary mb-0 text-break">{{ $notif->message }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <div wire:ignore.self class="modal fade" id="composeModal" tabindex="-1" aria-labelledby="composeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom px-4 py-3">
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-1" id="composeModalLabel">Soạn thông báo</h5>
                        <p class="text-secondary small mb-0">Nội dung sẽ xuất hiện trên chuông thông báo của người nhận.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <form wire:submit.prevent="send">
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="notification-title" class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                            <input id="notification-title" type="text" class="form-control @error('title') is-invalid @enderror" wire:model.defer="title" placeholder="Ví dụ: Lịch nghỉ lễ Quốc khánh">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label for="notification-body" class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
                            <textarea id="notification-body" class="form-control @error('body') is-invalid @enderror" wire:model.defer="body" rows="5" placeholder="Nhập nội dung rõ ràng, ngắn gọn..."></textarea>
                            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <fieldset class="mb-4">
                            <legend class="form-label fw-semibold mb-2">Phạm vi người nhận <span class="text-danger">*</span></legend>
                            <div class="row g-2">
                                <div class="col-12 col-md-4"><div class="form-check border rounded-3 p-3 ps-5 h-100"><input class="form-check-input" type="radio" wire:model.live="recipientType" id="recAll" value="all"><label class="form-check-label fw-semibold" for="recAll">Tất cả nhân viên</label><div class="small text-secondary">Mọi tài khoản đang hoạt động</div></div></div>
                                <div class="col-12 col-md-4"><div class="form-check border rounded-3 p-3 ps-5 h-100"><input class="form-check-input" type="radio" wire:model.live="recipientType" id="recRoles" value="roles"><label class="form-check-label fw-semibold" for="recRoles">Theo vai trò</label><div class="small text-secondary">Chọn một hoặc nhiều nhóm</div></div></div>
                                <div class="col-12 col-md-4"><div class="form-check border rounded-3 p-3 ps-5 h-100"><input class="form-check-input" type="radio" wire:model.live="recipientType" id="recUsers" value="users"><label class="form-check-label fw-semibold" for="recUsers">Người cụ thể</label><div class="small text-secondary">Chọn từng nhân viên</div></div></div>
                            </div>
                        </fieldset>

                        @if($recipientType === 'roles')
                            <fieldset class="border rounded-3 p-3 mb-2">
                                <legend class="float-none w-auto px-2 small fw-bold text-body">Chọn vai trò</legend>
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-2">
                                    @foreach($allRoles as $role)
                                        <div class="col"><div class="form-check"><input class="form-check-input" type="checkbox" wire:model.defer="selectedRoles" value="{{ $role['value'] }}" id="role_{{ $role['value'] }}"><label class="form-check-label" for="role_{{ $role['value'] }}">{{ $role['label'] }}</label></div></div>
                                    @endforeach
                                </div>
                                @error('selectedRoles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </fieldset>
                        @endif

                        @if($recipientType === 'users')
                            <fieldset class="border rounded-3 p-3 mb-2">
                                <legend class="float-none w-auto px-2 small fw-bold text-body">Chọn người nhận</legend>
                                <div class="row row-cols-1 row-cols-sm-2 g-2">
                                    @foreach($allUsers as $u)
                                        <div class="col"><div class="form-check"><input class="form-check-input" type="checkbox" wire:model.defer="selectedUsers" value="{{ $u->id }}" id="user_{{ $u->id }}"><label class="form-check-label" for="user_{{ $u->id }}">{{ $u->name }}</label></div></div>
                                    @endforeach
                                </div>
                                @error('selectedUsers')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            </fieldset>
                        @endif
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled" wire:target="send"><span wire:loading wire:target="send" class="spinner-border spinner-border-sm me-2"></span><i class="fa-solid fa-paper-plane me-2" wire:loading.remove wire:target="send"></i>Gửi thông báo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('openComposeModal', () => bootstrap.Modal.getOrCreateInstance(document.getElementById('composeModal')).show());
    window.addEventListener('closeComposeModal', () => bootstrap.Modal.getInstance(document.getElementById('composeModal'))?.hide());
</script>
@endpush
