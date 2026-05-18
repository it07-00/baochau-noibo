<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/hr-profile.css') }}?v={{ config('app.version') }}">
    @endpush

    <div class="px-3 pt-3">
        <!-- Back button -->
        <a href="{{ route('app.hr.index') }}" class="btn btn-sm btn-outline-secondary mb-3 rounded-8px" >
            <i class="bi bi-arrow-left me-1"></i> Danh sách nhân sự
        </a>

        <div class="row g-4">
            <!-- LEFT: Profile Sidebar -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hr-profile-sidebar">
                    <div class="card-body text-center p-4">
                        <div class="hr-profile-avatar-wrapper mb-3">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted fs-1 fw-bold">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-2 fs-85" >{{ $user->email }}</p>
                        <span class="hr-badge hr-badge-{{ $user->employment_status }}">{{ $user->employment_status_label }}</span>
                        <span class="hr-badge hr-badge-{{ $user->work_type }} ms-1">{{ $user->work_type_label }}</span>
                    </div>
                    <div class="border-top px-4 py-3 fs-85" >
                        <div class="mb-2"><i class="bi bi-hash me-2 text-muted"></i><strong>Mã NV:</strong> {{ $user->employee_code ?: '—' }}</div>
                        <div class="mb-2"><i class="bi bi-building me-2 text-muted"></i><strong>Phòng ban:</strong> {{ $user->department->name ?? '—' }}</div>
                        <div class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i><strong>SĐT:</strong> {{ $user->phone ?: '—' }}</div>
                        <div class="mb-2"><i class="bi bi-calendar-event me-2 text-muted"></i><strong>Ngày vào:</strong> {{ $user->start_date?->format('d/m/Y') ?? '—' }}</div>
                        <div><i class="bi bi-file-earmark-text me-2 text-muted"></i><strong>HĐ hiện tại:</strong> {{ $user->active_contract?->contract_type_label ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Tabs Content -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm rounded-16px" >
                    <!-- Tabs -->
                    <div class="card-header bg-white border-bottom p-0">
                        <ul class="nav hr-tabs">
                            <li class="nav-item">
                                <button wire:click="$set('activeTab', 'info')" class="nav-link {{ $activeTab === 'info' ? 'active' : '' }}">
                                    <i class="bi bi-person me-1"></i> Thông tin cá nhân
                                </button>
                            </li>
                            <li class="nav-item">
                                <button wire:click="$set('activeTab', 'contracts')" class="nav-link {{ $activeTab === 'contracts' ? 'active' : '' }}">
                                    <i class="bi bi-file-earmark-text me-1"></i> Hợp đồng <span class="badge bg-secondary ms-1">{{ $contracts->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item">
                                <button wire:click="$set('activeTab', 'documents')" class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}">
                                    <i class="bi bi-folder me-1"></i> Hồ sơ giấy tờ <span class="badge bg-secondary ms-1">{{ $documents->count() }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        {{-- ═══ TAB: THÔNG TIN CÁ NHÂN ═══ --}}
                        @if($activeTab === 'info')
                            <form wire:submit.prevent="savePersonalInfo">
                                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-person-vcard me-1"></i> Giấy tờ tùy thân</h6>
                                <div class="hr-info-grid mb-4">
                                    <div class="hr-info-field">
                                        <label>Mã nhân viên</label>
                                        <input type="text" wire:model="employee_code" class="form-control form-control-sm" placeholder="BC-001">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Số CCCD/CMND</label>
                                        <input type="text" wire:model="id_card_number" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Ngày cấp</label>
                                        <input type="date" wire:model="id_card_issued_date" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Nơi cấp</label>
                                        <input type="text" wire:model="id_card_issued_place" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> Địa chỉ</h6>
                                <div class="hr-info-grid mb-4">
                                    <div class="hr-info-field">
                                        <label>Quê quán</label>
                                        <input type="text" wire:model="hometown" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Thường trú</label>
                                        <input type="text" wire:model="permanent_address" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Tạm trú</label>
                                        <input type="text" wire:model="temporary_address" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Địa chỉ liên hệ</label>
                                        <input type="text" wire:model="address" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-bank me-1"></i> Tài chính & Bảo hiểm</h6>
                                <div class="hr-info-grid mb-4">
                                    <div class="hr-info-field">
                                        <label>Mã số thuế</label>
                                        <input type="text" wire:model="tax_code" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Số sổ BHXH</label>
                                        <input type="text" wire:model="social_insurance_number" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Số tài khoản</label>
                                        <input type="text" wire:model="bank_account" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Ngân hàng</label>
                                        <input type="text" wire:model="bank_name" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-telephone me-1"></i> Liên hệ khẩn cấp & Học vấn</h6>
                                <div class="hr-info-grid mb-4">
                                    <div class="hr-info-field">
                                        <label>Người liên hệ KC</label>
                                        <input type="text" wire:model="emergency_contact_name" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>SĐT khẩn cấp</label>
                                        <input type="text" wire:model="emergency_contact_phone" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Trình độ học vấn</label>
                                        <input type="text" wire:model="education_level" class="form-control form-control-sm" placeholder="Đại học, Cao đẳng...">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Chuyên ngành</label>
                                        <input type="text" wire:model="major" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <h6 class="fw-bold text-muted mb-3"><i class="bi bi-briefcase me-1"></i> Trạng thái công việc</h6>
                                <div class="hr-info-grid mb-4">
                                    <div class="hr-info-field">
                                        <label>Trạng thái</label>
                                        <select wire:model="employment_status" class="form-select form-select-sm">
                                            @foreach(\App\Models\User::EMPLOYMENT_STATUSES as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Loại làm việc</label>
                                        <select wire:model="work_type" class="form-select form-select-sm">
                                            @foreach(\App\Models\User::WORK_TYPES as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Ngày vào làm</label>
                                        <input type="date" wire:model="start_date" class="form-control form-control-sm">
                                    </div>
                                    <div class="hr-info-field">
                                        <label>Ngày nghỉ việc</label>
                                        <input type="date" wire:model="end_date" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="hr-info-field mb-4">
                                    <label>Ghi chú HR</label>
                                    <textarea wire:model="hr_notes" class="form-control form-control-sm rounded-8px" rows="3" ></textarea>
                                </div>

                                @can('hr-profiles.edit')
                                    <button type="submit" class="btn btn-primary px-4 rounded-8px" >
                                        <i class="bi bi-check-lg me-1"></i> Lưu thông tin
                                    </button>
                                @endcan
                            </form>

                        {{-- ═══ TAB: HỢP ĐỒNG ═══ --}}
                        @elseif($activeTab === 'contracts')
                            @can('hr-profiles.edit')
                                <div class="d-flex justify-content-end mb-3">
                                    <button wire:click="openContractModal" class="btn btn-sm btn-primary rounded-8px" >
                                        <i class="bi bi-plus-lg me-1"></i> Thêm hợp đồng
                                    </button>
                                </div>
                            @endcan

                            @forelse($contracts as $c)
                                <div class="hr-contract-card hr-contract-status-{{ $c->status }} mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold">{{ $c->contract_type_label }}</div>
                                            <div class="text-muted fs-82" >
                                                Số HĐ: {{ $c->contract_number ?: '—' }} &bull;
                                                Ký ngày: {{ $c->signed_date->format('d/m/Y') }}
                                            </div>
                                            <div class="text-muted fs-82" >
                                                Hiệu lực: {{ $c->start_date->format('d/m/Y') }} → {{ $c->end_date?->format('d/m/Y') ?? 'Không thời hạn' }}
                                            </div>
                                            @if($c->salary)
                                                <div class="fs-82"><strong>Lương:</strong> {{ number_format($c->salary, 0, ',', '.') }} VNĐ</div>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-1 align-items-center">
                                            <span class="hr-badge {{ $c->status === 'active' ? 'hr-badge-chinh-thuc' : ($c->status === 'expired' ? 'hr-badge-thu-viec' : 'hr-badge-nghi-viec') }}">
                                                {{ $c->status_label }}
                                            </span>
                                            @can('hr-profiles.edit')
                                                <button wire:click="openContractModal({{ $c->id }})" class="btn btn-sm btn-outline-primary py-0 px-2 fs-75 rounded-2" >
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endcan
                                            @if($c->file_path)
                                                <button wire:click="downloadContract({{ $c->id }})" class="btn btn-sm btn-outline-secondary py-0 px-2 fs-75 rounded-2" >
                                                    <i class="bi bi-download"></i>
                                                </button>
                                            @endif
                                            @can('hr-profiles.delete')
                                                <button wire:click="deleteContract({{ $c->id }})" wire:confirm="Xóa hợp đồng này?" class="btn btn-sm btn-outline-danger py-0 px-2 fs-75 rounded-2" >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2 opacity-50"></i>
                                    Chưa có hợp đồng nào.
                                </div>
                            @endforelse

                        {{-- ═══ TAB: HỒ SƠ GIẤY TỜ ═══ --}}
                        @elseif($activeTab === 'documents')
                            @can('hr-profiles.edit')
                                <div class="d-flex justify-content-end mb-3">
                                    <button wire:click="openDocumentModal" class="btn btn-sm btn-primary rounded-8px" >
                                        <i class="bi bi-cloud-upload me-1"></i> Tải lên giấy tờ
                                    </button>
                                </div>
                            @endcan

                            <div class="row g-3">
                                @forelse($documents as $doc)
                                    <div class="col-md-6">
                                        <div class="hr-doc-card d-flex gap-3 align-items-center">
                                            <div class="hr-doc-icon">
                                                <i class="bi bi-{{ $doc->is_image ? 'image' : 'file-earmark-pdf' }}"></i>
                                            </div>
                                            <div class="flex-grow-1 min-w-0" >
                                                <div class="fw-bold text-truncate fs-88" >{{ $doc->title }}</div>
                                                <div class="text-muted fs-75" >
                                                    {{ $doc->document_type_label }} &bull; {{ $doc->file_size_formatted }}
                                                    @if($doc->issued_date) &bull; {{ $doc->issued_date->format('d/m/Y') }} @endif
                                                </div>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <button wire:click="downloadDocument({{ $doc->id }})" class="btn btn-sm btn-outline-primary py-0 px-2 fs-75 rounded-2"  title="Tải về">
                                                    <i class="bi bi-download"></i>
                                                </button>
                                                @can('hr-profiles.delete')
                                                    <button wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Xóa giấy tờ này?" class="btn btn-sm btn-outline-danger py-0 px-2 fs-75 rounded-2"  title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-center text-muted py-5">
                                        <i class="bi bi-folder2-open fs-1 d-block mb-2 opacity-50"></i>
                                        Chưa có giấy tờ nào.
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ CONTRACT MODAL ═══ --}}
    @if($showContractModal)
        <div class="hr-modal-overlay" x-data @keydown.escape.window="$wire.set('showContractModal', false)">
            <div class="hr-modal-backdrop" wire:click="$set('showContractModal', false)"></div>
            <div class="hr-modal-content" @click.stop>
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>{{ $editingContractId ? 'Sửa' : 'Thêm' }} hợp đồng</h5>
                    <button wire:click="$set('showContractModal', false)" class="btn-close"></button>
                </div>
                <form wire:submit.prevent="saveContract" class="p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Loại hợp đồng *</label>
                            <select wire:model="contract_type" class="form-select form-select-sm rounded-8px" >
                                <option value="">-- Chọn --</option>
                                @foreach(\App\Models\EmployeeContract::CONTRACT_TYPES as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('contract_type') <span class="text-danger fs-78" >{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Số hợp đồng</label>
                            <input type="text" wire:model="contract_number" class="form-control form-control-sm rounded-8px" >
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold fs-82" >Ngày ký *</label>
                            <input type="date" wire:model="contract_signed_date" class="form-control form-control-sm rounded-8px" >
                            @error('contract_signed_date') <span class="text-danger fs-78" >{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold fs-82" >Bắt đầu *</label>
                            <input type="date" wire:model="contract_start_date" class="form-control form-control-sm rounded-8px" >
                            @error('contract_start_date') <span class="text-danger fs-78" >{{ $message }}</span> @enderror
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold fs-82" >Kết thúc</label>
                            <input type="date" wire:model="contract_end_date" class="form-control form-control-sm rounded-8px" >
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Lương (VNĐ)</label>
                            <input type="number" wire:model="contract_salary" class="form-control form-control-sm rounded-8px" >
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Trạng thái</label>
                            <select wire:model="contract_status" class="form-select form-select-sm rounded-8px" >
                                @foreach(\App\Models\EmployeeContract::STATUSES as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold fs-82" >File scan HĐ</label>
                            <input type="file" wire:model="contract_file" class="form-control form-control-sm rounded-8px" >
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold fs-82" >Ghi chú</label>
                            <textarea wire:model="contract_notes" class="form-control form-control-sm rounded-8px" rows="2" ></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-8px" >
                            <i class="bi bi-check-lg me-1"></i> {{ $editingContractId ? 'Cập nhật' : 'Thêm mới' }}
                        </button>
                        <button type="button" wire:click="$set('showContractModal', false)" class="btn btn-outline-secondary rounded-8px" >Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══ DOCUMENT MODAL ═══ --}}
    @if($showDocumentModal)
        <div class="hr-modal-overlay" x-data @keydown.escape.window="$wire.set('showDocumentModal', false)">
            <div class="hr-modal-backdrop" wire:click="$set('showDocumentModal', false)"></div>
            <div class="hr-modal-content" @click.stop>
                <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cloud-upload me-2"></i>Tải lên giấy tờ</h5>
                    <button wire:click="$set('showDocumentModal', false)" class="btn-close"></button>
                </div>
                <form wire:submit.prevent="saveDocuments" class="p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Loại giấy tờ *</label>
                            <select wire:model="document_type" class="form-select form-select-sm rounded-8px" >
                                @foreach(\App\Models\EmployeeDocument::DOCUMENT_TYPES as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Tên hiển thị</label>
                            <input type="text" wire:model="document_title" class="form-control form-control-sm rounded-8px" placeholder="Tự động theo loại" >
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold fs-82" >Chọn file (nhiều file) *</label>
                            <input type="file" wire:model="document_files" class="form-control form-control-sm rounded-8px" multiple >
                            @error('document_files') <span class="text-danger fs-78" >{{ $message }}</span> @enderror
                            @error('document_files.*') <span class="text-danger fs-78" >{{ $message }}</span> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Ngày cấp</label>
                            <input type="date" wire:model="document_issued_date" class="form-control form-control-sm rounded-8px" >
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold fs-82" >Ngày hết hạn</label>
                            <input type="date" wire:model="document_expiry_date" class="form-control form-control-sm rounded-8px" >
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold fs-82" >Ghi chú</label>
                            <textarea wire:model="document_notes" class="form-control form-control-sm rounded-8px" rows="2" ></textarea>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 rounded-8px" >
                            <i class="bi bi-cloud-upload me-1"></i> Tải lên
                        </button>
                        <button type="button" wire:click="$set('showDocumentModal', false)" class="btn btn-outline-secondary rounded-8px" >Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
