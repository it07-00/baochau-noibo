<div class="px-3 pt-3">
    <!-- Nút quay lại danh sách -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('app.hr.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Danh sách nhân sự
        </a>
    </div>

    <div class="row g-4">
        <!-- CỘT TRÁI: Thẻ thông tin nhanh nhân sự -->
        <div class="col-lg-3 col-md-4">
            <div class="card bg-body border border-light-subtle shadow-sm rounded-3 sticky-top" style="top: 1rem; z-index: 10;">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle img-thumbnail mx-auto d-block" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center mx-auto fw-bold fs-2" style="width: 100px; height: 100px;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <h5 class="fw-bold text-body mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }}</p>

                    <div class="d-flex flex-wrap justify-content-center gap-1 mb-2">
                        @php
                            $statusBadgeClass = match($user->employment_status) {
                                'chinh_thuc' => 'bg-success-subtle text-success border-success-subtle',
                                'thu_viec'   => 'bg-warning-subtle text-warning border-warning-subtle',
                                'thuc_tap'   => 'bg-info-subtle text-info border-info-subtle',
                                'nghi_viec'  => 'bg-danger-subtle text-danger border-danger-subtle',
                                default      => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                            };
                            $workTypeBadgeClass = match($user->work_type) {
                                'full_time' => 'bg-primary-subtle text-primary border-primary-subtle',
                                'part_time' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                default     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                            };
                        @endphp
                        <span class="badge {{ $statusBadgeClass }} border rounded-pill px-2 py-1">
                            {{ $user->employment_status_label }}
                        </span>
                        <span class="badge {{ $workTypeBadgeClass }} border rounded-pill px-2 py-1">
                            {{ $user->work_type_label }}
                        </span>
                    </div>
                </div>

                <div class="border-top border-light-subtle px-4 py-3 small bg-body-tertiary rounded-bottom-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-hashtag me-2"></i>Mã NV:</span>
                        <span class="fw-semibold text-body">{{ $user->employee_code ?: '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-building me-2"></i>Phòng ban:</span>
                        <span class="fw-semibold text-body">{{ $user->department->name ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-phone me-2"></i>SĐT:</span>
                        <span class="fw-semibold text-body">{{ $user->phone ?: '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><i class="fa-solid fa-calendar-day me-2"></i>Ngày vào:</span>
                        <span class="fw-semibold text-body">{{ $user->start_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-file-contract me-2"></i>HĐ hiện tại:</span>
                        <span class="fw-semibold text-body text-truncate ms-2" style="max-width: 120px;" title="{{ $user->active_contract?->contract_type_label }}">
                            {{ $user->active_contract?->contract_type_label ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: Nội dung các Tabs -->
        <div class="col-lg-9 col-md-8">
            <div class="card bg-body border border-light-subtle shadow-sm rounded-3">
                <!-- Thanh chuyển Tab -->
                <div class="card-header bg-body-tertiary border-bottom border-light-subtle p-0">
                    <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0">
                        <li class="nav-item">
                            <button wire:click="$set('activeTab', 'info')" class="nav-link py-3 px-4 fw-semibold border-0 rounded-0 {{ $activeTab === 'info' ? 'active bg-body text-primary border-bottom border-primary border-2' : 'text-secondary' }}">
                                <i class="fa-solid fa-user-vcard me-2"></i>Thông tin cá nhân
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="$set('activeTab', 'contracts')" class="nav-link py-3 px-4 fw-semibold border-0 rounded-0 {{ $activeTab === 'contracts' ? 'active bg-body text-primary border-bottom border-primary border-2' : 'text-secondary' }}">
                                <i class="fa-solid fa-file-contract me-2"></i>Hợp đồng lao động
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">{{ $contracts->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button wire:click="$set('activeTab', 'documents')" class="nav-link py-3 px-4 fw-semibold border-0 rounded-0 {{ $activeTab === 'documents' ? 'active bg-body text-primary border-bottom border-primary border-2' : 'text-secondary' }}">
                                <i class="fa-solid fa-folder-open me-2"></i>Hồ sơ giấy tờ
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">{{ $documents->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    {{-- ═══ TAB 1: THÔNG TIN CÁ NHÂN ═══ --}}
                    @if($activeTab === 'info')
                        <form wire:submit.prevent="savePersonalInfo">
                            <!-- Nhóm 1: Giấy tờ tùy thân & Thông tin cá nhân -->
                            <div class="card bg-body-tertiary border border-light-subtle rounded-3 mb-4">
                                <div class="card-header bg-transparent border-bottom border-light-subtle py-2 px-3 fw-bold small text-uppercase text-secondary">
                                    <i class="fa-solid fa-address-card me-2 text-primary"></i>Giấy tờ tùy thân & Thông tin cá nhân
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Mã nhân viên</label>
                                            <input type="text" wire:model="employee_code" class="form-control form-control-sm rounded-3" placeholder="BC-001">
                                            @error('employee_code') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Số CCCD / CMND</label>
                                            <input type="text" wire:model="id_card_number" class="form-control form-control-sm rounded-3">
                                            @error('id_card_number') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Ngày cấp</label>
                                            <input type="date" wire:model="id_card_issued_date" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Nơi cấp</label>
                                            <input type="text" wire:model="id_card_issued_place" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Số điện thoại</label>
                                            <input type="text" wire:model="phone" class="form-control form-control-sm rounded-3">
                                            @error('phone') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Giới tính</label>
                                            <select wire:model="gender" class="form-select form-select-sm rounded-3">
                                                <option value="">-- Chọn giới tính --</option>
                                                <option value="nam">Nam</option>
                                                <option value="nu">Nữ</option>
                                                <option value="khac">Khác</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Ngày sinh</label>
                                            <input type="date" wire:model="date_of_birth" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nhóm 2: Địa chỉ & Liên hệ -->
                            <div class="card bg-body-tertiary border border-light-subtle rounded-3 mb-4">
                                <div class="card-header bg-transparent border-bottom border-light-subtle py-2 px-3 fw-bold small text-uppercase text-secondary">
                                    <i class="fa-solid fa-location-dot me-2 text-primary"></i>Địa chỉ & Liên hệ
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Quê quán</label>
                                            <input type="text" wire:model="hometown" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Địa chỉ thường trú</label>
                                            <input type="text" wire:model="permanent_address" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Địa chỉ tạm trú</label>
                                            <input type="text" wire:model="temporary_address" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Địa chỉ liên hệ hiện tại</label>
                                            <input type="text" wire:model="address" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nhóm 3: Tài chính & Bảo hiểm -->
                            <div class="card bg-body-tertiary border border-light-subtle rounded-3 mb-4">
                                <div class="card-header bg-transparent border-bottom border-light-subtle py-2 px-3 fw-bold small text-uppercase text-secondary">
                                    <i class="fa-solid fa-building-columns me-2 text-primary"></i>Tài chính & Bảo hiểm
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Mã số thuế cá nhân</label>
                                            <input type="text" wire:model="tax_code" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Số sổ bảo hiểm xã hội</label>
                                            <input type="text" wire:model="social_insurance_number" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Số tài khoản ngân hàng</label>
                                            <input type="text" wire:model="bank_account" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Tên ngân hàng & Chi nhánh</label>
                                            <input type="text" wire:model="bank_name" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nhóm 4: Liên hệ khẩn cấp & Học vấn -->
                            <div class="card bg-body-tertiary border border-light-subtle rounded-3 mb-4">
                                <div class="card-header bg-transparent border-bottom border-light-subtle py-2 px-3 fw-bold small text-uppercase text-secondary">
                                    <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Liên hệ khẩn cấp & Trình độ học vấn
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Người liên hệ khẩn cấp</label>
                                            <input type="text" wire:model="emergency_contact_name" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">SĐT người liên hệ khẩn cấp</label>
                                            <input type="text" wire:model="emergency_contact_phone" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Trình độ học vấn</label>
                                            <input type="text" wire:model="education_level" class="form-control form-control-sm rounded-3" placeholder="Đại học, Cao đẳng...">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Chuyên ngành đào tạo</label>
                                            <input type="text" wire:model="major" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nhóm 5: Trạng thái công việc & Ghi chú -->
                            <div class="card bg-body-tertiary border border-light-subtle rounded-3 mb-4">
                                <div class="card-header bg-transparent border-bottom border-light-subtle py-2 px-3 fw-bold small text-uppercase text-secondary">
                                    <i class="fa-solid fa-briefcase me-2 text-primary"></i>Trạng thái công việc & Ghi chú HR
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Trạng thái nhân sự *</label>
                                            <select wire:model="employment_status" class="form-select form-select-sm rounded-3">
                                                @foreach(\App\Models\User::EMPLOYMENT_STATUSES as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('employment_status') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Hình thức làm việc *</label>
                                            <select wire:model="work_type" class="form-select form-select-sm rounded-3">
                                                @foreach(\App\Models\User::WORK_TYPES as $val => $label)
                                                    <option value="{{ $val }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('work_type') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Ngày bắt đầu làm việc</label>
                                            <input type="date" wire:model="start_date" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Ngày nghỉ việc</label>
                                            <input type="date" wire:model="end_date" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-semibold">Ghi chú nhân sự (HR Note)</label>
                                            <textarea wire:model="hr_notes" class="form-control form-control-sm rounded-3" rows="3" placeholder="Nhập các ghi chú quản lý nội bộ nhân sự..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @can('hr-profiles.edit')
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi
                                    </button>
                                </div>
                            @endcan
                        </form>

                    {{-- ═══ TAB 2: HỢP ĐỒNG LAO ĐỘNG ═══ --}}
                    @elseif($activeTab === 'contracts')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold m-0 text-body">
                                <i class="fa-solid fa-file-contract me-2 text-primary"></i>Danh sách hợp đồng lao động
                            </h6>
                            @can('hr-profiles.edit')
                                <button wire:click="openContractModal" class="btn btn-primary btn-sm rounded-3 shadow-sm">
                                    <i class="fa-solid fa-plus me-1"></i> Thêm hợp đồng
                                </button>
                            @endcan
                        </div>

                        @forelse($contracts as $c)
                            @php
                                $contractBadgeClass = match($c->status) {
                                    'active'    => 'bg-success-subtle text-success border-success-subtle',
                                    'expired'   => 'bg-warning-subtle text-warning border-warning-subtle',
                                    'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    default     => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                };
                            @endphp
                            <div class="card bg-body-tertiary border border-light-subtle rounded-3 mb-3">
                                <div class="card-body p-3 d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="fw-bold text-body fs-6">{{ $c->contract_type_label }}</span>
                                            <span class="badge {{ $contractBadgeClass }} border rounded-pill px-2 py-1 small">
                                                {{ $c->status_label }}
                                            </span>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <span><strong>Số HĐ:</strong> {{ $c->contract_number ?: '—' }}</span>
                                            <span class="mx-2">&bull;</span>
                                            <span><strong>Ngày ký:</strong> {{ $c->signed_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="text-muted small mb-1">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            <span><strong>Hiệu lực:</strong> {{ $c->start_date->format('d/m/Y') }}</span>
                                            <i class="fa-solid fa-arrow-right mx-1 text-secondary" style="font-size: 0.75rem;"></i>
                                            <span>{{ $c->end_date?->format('d/m/Y') ?? 'Không thời hạn' }}</span>
                                        </div>
                                        @if($c->salary)
                                            <div class="small fw-semibold text-success mt-1">
                                                <i class="fa-solid fa-money-bill-wave me-1"></i>
                                                Mức lương: {{ number_format($c->salary, 0, ',', '.') }} VNĐ
                                            </div>
                                        @endif
                                        @if($c->notes)
                                            <div class="text-muted small fst-italic mt-1">
                                                <i class="fa-solid fa-note-sticky me-1"></i> {{ $c->notes }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1 align-items-center">
                                        @can('hr-profiles.edit')
                                            <button wire:click="openContractModal({{ $c->id }})" class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        @endcan
                                        @if($c->file_path)
                                            <button wire:click="downloadContract({{ $c->id }})" class="btn btn-sm btn-outline-secondary rounded-2 px-2 py-1" title="Tải file scan HĐ">
                                                <i class="fa-solid fa-download"></i>
                                            </button>
                                        @endif
                                        @can('hr-profiles.delete')
                                            <button wire:click="deleteContract({{ $c->id }})" wire:confirm="Bạn có chắc chắn muốn xóa hợp đồng này?" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="Xóa">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5 my-3 bg-body-tertiary border border-dashed rounded-3">
                                <i class="fa-solid fa-file-circle-xmark fs-1 d-block mb-3 opacity-50"></i>
                                Chưa có hợp đồng lao động nào được lưu.
                            </div>
                        @endforelse

                    {{-- ═══ TAB 3: HỒ SƠ GIẤY TỜ ═══ --}}
                    @elseif($activeTab === 'documents')
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold m-0 text-body">
                                <i class="fa-solid fa-folder-open me-2 text-primary"></i>Danh sách hồ sơ giấy tờ lưu trữ
                            </h6>
                            @can('hr-profiles.edit')
                                <button wire:click="openDocumentModal" class="btn btn-primary btn-sm rounded-3 shadow-sm">
                                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên giấy tờ
                                </button>
                            @endcan
                        </div>

                        <div class="row g-3">
                            @forelse($documents as $doc)
                                <div class="col-md-6">
                                    <div class="card bg-body-tertiary border border-light-subtle rounded-3 h-100 shadow-sm">
                                        <div class="card-body p-3 d-flex align-items-center gap-3">
                                            <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                                <i class="fa-solid {{ $doc->is_image ? 'fa-file-image' : 'fa-file-pdf' }} fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="fw-bold text-truncate text-body mb-1" title="{{ $doc->title }}">{{ $doc->title }}</div>
                                                <div class="text-muted small">
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill me-1">{{ $doc->document_type_label }}</span>
                                                    <span>{{ $doc->file_size_formatted }}</span>
                                                </div>
                                                @if($doc->issued_date || $doc->expiry_date)
                                                    <div class="text-muted small mt-1">
                                                        @if($doc->issued_date) <span>Cấp: {{ $doc->issued_date->format('d/m/Y') }}</span> @endif
                                                        @if($doc->expiry_date) <span class="ms-2">Hạn: {{ $doc->expiry_date->format('d/m/Y') }}</span> @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="d-flex gap-1 flex-shrink-0">
                                                <button wire:click="downloadDocument({{ $doc->id }})" class="btn btn-sm btn-outline-primary rounded-2 px-2 py-1" title="Tải về">
                                                    <i class="fa-solid fa-download"></i>
                                                </button>
                                                @can('hr-profiles.delete')
                                                    <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Bạn có chắc chắn muốn xóa giấy tờ này?" class="btn btn-sm btn-outline-danger rounded-2 px-2 py-1" title="Xóa">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center text-muted py-5 my-3 bg-body-tertiary border border-dashed rounded-3">
                                        <i class="fa-solid fa-folder-open fs-1 d-block mb-3 opacity-50"></i>
                                        Chưa có giấy tờ hồ sơ nào được tải lên.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ MODAL CHUẨN BOOTSTRAP 5: HỢP ĐỒNG ═══ --}}
    @if($showContractModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);" x-data @keydown.escape.window="$wire.set('showContractModal', false)">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-body border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom border-light-subtle py-3 px-4">
                        <h5 class="modal-title fw-bold text-body">
                            <i class="fa-solid fa-file-contract me-2 text-primary"></i>{{ $editingContractId ? 'Chỉnh sửa' : 'Thêm mới' }} hợp đồng lao động
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showContractModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="saveContract">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Loại hợp đồng <span class="text-danger">*</span></label>
                                    <select wire:model="contract_type" class="form-select form-select-sm rounded-3">
                                        <option value="">-- Chọn loại hợp đồng --</option>
                                        @foreach(\App\Models\EmployeeContract::CONTRACT_TYPES as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('contract_type') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Số hợp đồng</label>
                                    <input type="text" wire:model="contract_number" class="form-control form-control-sm rounded-3" placeholder="VD: HĐLĐ-2026/01">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Ngày ký <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="contract_signed_date" class="form-control form-control-sm rounded-3">
                                    @error('contract_signed_date') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Ngày bắt đầu hiệu lực <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="contract_start_date" class="form-control form-control-sm rounded-3">
                                    @error('contract_start_date') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Ngày kết thúc</label>
                                    <input type="date" wire:model="contract_end_date" class="form-control form-control-sm rounded-3">
                                    @error('contract_end_date') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Mức lương thỏa thuận (VNĐ)</label>
                                    <input type="number" wire:model="contract_salary" class="form-control form-control-sm rounded-3" placeholder="VD: 15000000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Trạng thái hợp đồng</label>
                                    <select wire:model="contract_status" class="form-select form-select-sm rounded-3">
                                        @foreach(\App\Models\EmployeeContract::STATUSES as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">File scan hợp đồng (PDF, Docx, Image)</label>
                                    <input type="file" wire:model="contract_file" class="form-control form-control-sm rounded-3">
                                    @error('contract_file') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Ghi chú hợp đồng</label>
                                    <textarea wire:model="contract_notes" class="form-control form-control-sm rounded-3" rows="2" placeholder="Ghi chú thêm về điều khoản..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-light-subtle py-3 px-4">
                            <button type="button" wire:click="$set('showContractModal', false)" class="btn btn-outline-secondary btn-sm rounded-3 px-3">Hủy</button>
                            <button type="submit" class="btn btn-primary btn-sm rounded-3 px-4 shadow-sm">
                                <i class="fa-solid fa-check me-1"></i> {{ $editingContractId ? 'Cập nhật' : 'Thêm mới' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ MODAL CHUẨN BOOTSTRAP 5: GIẤY TỜ ═══ --}}
    @if($showDocumentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);" x-data @keydown.escape.window="$wire.set('showDocumentModal', false)">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-body border-0 shadow-lg rounded-3">
                    <div class="modal-header border-bottom border-light-subtle py-3 px-4">
                        <h5 class="modal-title fw-bold text-body">
                            <i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Tải lên giấy tờ hồ sơ
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showDocumentModal', false)"></button>
                    </div>
                    <form wire:submit.prevent="saveDocuments">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Loại giấy tờ <span class="text-danger">*</span></label>
                                    <select wire:model="document_type" class="form-select form-select-sm rounded-3">
                                        @foreach(\App\Models\EmployeeDocument::DOCUMENT_TYPES as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Tên hiển thị</label>
                                    <input type="text" wire:model="document_title" class="form-control form-control-sm rounded-3" placeholder="Để trống để tự động lấy tên loại giấy tờ">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Chọn tập tin (có thể chọn nhiều file) <span class="text-danger">*</span></label>
                                    <input type="file" wire:model="document_files" class="form-control form-control-sm rounded-3" multiple>
                                    @error('document_files') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                    @error('document_files.*') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Ngày cấp</label>
                                    <input type="date" wire:model="document_issued_date" class="form-control form-control-sm rounded-3">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Ngày hết hạn</label>
                                    <input type="date" wire:model="document_expiry_date" class="form-control form-control-sm rounded-3">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Ghi chú</label>
                                    <textarea wire:model="document_notes" class="form-control form-control-sm rounded-3" rows="2" placeholder="Ghi chú chi tiết về tài liệu này..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-light-subtle py-3 px-4">
                            <button type="button" wire:click="$set('showDocumentModal', false)" class="btn btn-outline-secondary btn-sm rounded-3 px-3">Hủy</button>
                            <button type="submit" class="btn btn-primary btn-sm rounded-3 px-4 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
