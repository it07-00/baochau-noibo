<div>

    <div class="page-header d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-0">NC & CĐ Công nghệ</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng thống kê</a></li>
                    <li class="breadcrumb-item active">NC & CĐ Công nghệ</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 ms-auto justify-content-end">
            @can('contracts-commercial.create')
                <button wire:click="create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Thêm Hợp Đồng
                </button>
            @endcan
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tìm kiếm theo SHD hoặc Tên KH"
                    wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" />
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>



    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Bộ lọc Hợp đồng thương mại</h6>
            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="collapse"
                data-bs-target="#filterBody">−</button>
        </div>
        <div class="collapse show" id="filterBody">
            <div class="card-body p-4">
                <div class="row g-3">
                    @unless ($this->isRestrictedRole)
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Ngày ký hợp đồng</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.signed_from">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.signed_to">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Ngày xuất hóa đơn</label>
                            <div class="d-flex gap-2">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.submitted_from">
                                <input type="date" class="form-control form-control-xs"
                                    wire:model.live="filter.submitted_to">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.province">
                                <option value="">Chọn tỉnh thành</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end flex-wrap gap-3 pb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cm_offset"
                                    wire:model.live="filter.is_offset">
                                <label class="form-check-label " for="cm_offset">Có bù trừ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cm_roomfund"
                                    wire:model.live="filter.has_room_fund">
                                <label class="form-check-label " for="cm_roomfund">Có quỹ phòng</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cm_overdue"
                                    wire:model.live="filter.is_overdue">
                                <label class="form-check-label " for="cm_overdue">Trễ hạn</label>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold custom-filter-label">Nguồn thông tin</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.info_source">
                                <option value="">Chọn Nguồn thông...</option>
                                @foreach ($info_sources as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold custom-filter-label">Phương thức thanh toán</label>
                            <select class="form-select form-control-xs" wire:model.live="filter.payment_method">
                                <option value="">Chọn phương thức...</option>
                                @foreach ($payment_methods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (auth()->user()->hasAnyRole([\App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                            <div class="col-md-2">
                                <label class="form-label fw-bold custom-filter-label">Nhân viên chăm sóc</label>
                                <select class="form-select form-control-xs" wire:model.live="filter.staff_id">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach ($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @else
                        {{-- Bộ lọc cho tư vấn / kỹ thuật --}}
                        @include('livewire.admin.contracts.partials.restricted-contract-filters')
                    @endunless
                    @unless (auth()->user()->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]))
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.status">
                            <option value="">Chọn tình trạng</option>
                            @foreach ($all_statuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng chứng từ</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.voucher_status">
                            <option value="">Chọn tình trạng</option>
                            @foreach ($voucher_status_options as $voucherStatus)
                                <option value="{{ $voucherStatus }}">{{ $voucherStatus }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Loại dịch vụ</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.loai_dich_vu">
                            <option value="">Chọn loại dịch vụ</option>
                            @foreach ($loai_dich_vu_options as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Nhà thầu phụ</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.handler_id">
                            <option value="">Tất cả</option>
                            @foreach ($handlers as $h)
                                <option value="{{ $h->id }}">{{ $h->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Sắp xếp</label>
                        <select class="form-select form-control-xs" wire:model.live="sortDirection">
                            <option value="asc">Cũ nhất trước</option>
                            <option value="desc">Mới nhất trước</option>
                        </select>
                    </div>
                    @endunless

                    <div class="col-md-12 d-flex flex-wrap gap-2 mt-2">
                        <button class="btn btn-info text-white px-4 btn-filter" wire:click="$refresh">
                            <i class="bi bi-search me-1"></i>Lọc
                        </button>
                        <button class="btn btn-secondary px-4 btn-filter" wire:click="resetFilters">
                            <i class="bi bi-x-circle me-1"></i>Xóa lọc
                        </button>
                        @if ($this->canBulkDelete)
                            <button class="btn btn-danger px-4 btn-filter" wire:click="bulkDeleteSelected"
                                wire:confirm="Xác nhận xóa các hợp đồng đã chọn?"
                                @if (empty($selectedDocIds)) disabled @endif>
                                <i class="bi bi-trash me-1"></i>Xóa đã chọn ({{ count($selectedDocIds) }})
                            </button>
                        @endif
                        @unless ($this->isRestrictedRole)
                            <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel"
                                class="btn btn-success px-4 btn-filter">
                                <span wire:loading wire:target="exportExcel"
                                    class="spinner-border spinner-border-sm me-1"></span>
                                <i wire:loading.remove wire:target="exportExcel"
                                    class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                            </button>
                        @endunless
                        <button class="btn btn-primary px-4 btn-filter">
                            <i class="bi bi-diagram-3 me-1"></i>Quy trình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Danh sách Hợp đồng thương mại</h6>
        </div>
        <div class="table-responsive mh-350" >
            <table class="table table-hover align-middle mb-0 table-xs">
                <thead class="bg-light bg-opacity-50">
                    <tr class=" text-muted fw-bold">
                        @if ($this->canBulkDelete)
                            <th class="text-center w-42px" >Chọn</th>
                        @endif
                        <th class="text-center w-45px" >STT</th>
                        <th class="ps-4 col-ct-customer">Khách hàng</th>
                        @if (auth()->user()->hasRole(\App\Enums\Role::KY_THUAT->value))
                            <th class="text-center">Báo cáo số</th>
                        @endif
                        @unless ($this->isRestrictedRole)
                            <th class="text-center col-ct-finance">Tài chính</th>
                        @endunless
                        @unless (auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                            <th class="text-center col-ct-assigned">Được giao</th>
                            <th class="text-center col-ct-deadline">Hạn chót</th>
                        @endunless
                        <th class="text-center col-ct-status">Tình trạng</th>
                        @unless ($this->isRestrictedRole)
                        <th class="text-center voucher-status-cell">Tình trạng chứng từ</th>
                        @endunless
                        <th class="text-center col-ct-actions pe-2">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                        <tr class="border-bottom border-light" wire:key="commercial-row-{{ $doc->id }}">
                            @if ($this->canBulkDelete)
                                <td class="text-center">
                                    @if (!auth()->user()->hasRole(\App\Enums\Role::TP_KINH_DOANH->value) || $doc->staff_id === auth()->id())
                                        <input class="form-check-input" type="checkbox"
                                            wire:model.live="selectedDocIds" value="{{ $doc->id }}">
                                    @endif
                                </td>
                            @endif
                            <td class="text-center text-muted  fw-semibold">
                                {{ ($docs->currentPage() - 1) * $docs->perPage() + $loop->iteration }}
                            </td>
                            <td class="ps-3 py-2 col-ct-customer">
                                <div class="d-flex flex-column gap-1">
                                    <a href="{{ $doc->customer ? route('app.customers.contracts', $doc->customer->slug) : '#' }}" class="fw-bold text-primary text-decoration-none lh-sm">
                                        {{ $doc->customer?->name }}
                                    </a>
                                    <span class="text-muted fs-85">{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }}</span>
                                    <span class="text-muted fs-85">{{ Str::limit($doc->customer?->address, 50) }}</span>
                                    <div class="d-flex gap-2 flex-wrap contract-text-08 border-top mt-1 pt-1 text-secondary">
                                        <span>NTP: <span class="fw-semibold text-dark">{{ $doc->shd_cxl ?: '-' }}</span></span>
                                        <span>BC: <span class="fw-semibold text-dark">{{ $doc->shd_bc ?: '-' }}</span></span>
                                        <span>Ký: <span class="fw-semibold text-dark">{{ $doc->signed_at ? $doc->signed_at->format('d/m/Y') : '-' }}</span></span>
                                        <span>CS: <span class="fw-semibold text-dark">{{ $doc->staff?->name }}</span></span>
                                    </div>
                                </div>
                            </td>
                            @if (auth()->user()->hasRole(\App\Enums\Role::KY_THUAT->value))
                                <td class="text-center align-middle">
                                    <input type="text"
                                           x-data="inlineReportEdit({{ $doc->id }}, @js($doc->report_number))"
                                           class="form-control form-control-sm text-center fw-semibold text-primary report-number-input bg-light"
                                           :value="value"
                                           @change="updateReport"
                                           placeholder="Nhập..."
                                           title="Sửa trực tiếp">
                                </td>
                            @endif
                            @unless ($this->isRestrictedRole)
                                <td class="py-2 px-3 col-ct-finance">
                                    <div class="d-flex flex-column gap-1 contract-text-08">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Giá trị HĐ:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Hoa hồng:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Doanh số:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Chi nhà thầu phụ:</span>
                                            <span class="fw-bold text-danger">{{ number_format($doc->ncc_payment ?? 0) }}đ</span>
                                        </div>
                                    </div>
                                </td>
                            @endunless
                            @unless (auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                                <td class="text-center">
                                    @php
                                        $completedSteps = $doc->workflowSteps->pluck('step_name')->unique()->count();
                                        $totalSteps = $doc->getMorphClass()::TOTAL_STEPS;
                                        $progressPercent = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
                                        $progressColor = $progressPercent >= 100 ? 'success' : ($progressPercent >= 50 ? 'primary' : 'warning');
                                    @endphp
                                    @if ($doc->assignments->count() > 0)
                                        <div class="d-flex flex-column gap-1 align-items-center">
                                            @include('livewire.admin.contracts.partials.assignment-compact-list', [
                                                'assignments' => $doc->assignments,
                                                'badgeClass' => 'contract-text-08',
                                                'metaClass' => 'contract-text-08',
                                            ])
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                    <div class="mt-2">
                                        <div class="progress h-6px w-80px mx-auto" >
                                            <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                        <span class="fw-semibold text-{{ $progressColor }} contract-text-08" >{{ $completedSteps }}/{{ $totalSteps }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $deadline = $doc->assignments->first()?->deadline;
                                        $isFinished = in_array($doc->status ?? '', ['Đã hoàn thành', 'Hợp đồng hủy', 'HOÀN THÀNH']);
                                        $isOverdue = $deadline && $deadline->isPast() && !$isFinished;
                                        $daysLeft = $deadline ? (int) now()->startOfDay()->diffInDays($deadline->startOfDay(), false) : null;
                                        $isNearDue = $deadline && !$isOverdue && !$isFinished && $daysLeft !== null && $daysLeft <= 3;
                                    @endphp
                                    @if($deadline)
                                        @if($isFinished)
                                            <span class="fw-semibold text-success contract-text-08" >{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-success contract-text-08" ><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>
                                        @elseif($isOverdue)
                                            <span class="fw-bold text-danger contract-text-08" >{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-danger contract-text-08" ><i class="bi bi-exclamation-triangle me-1"></i>Quá hạn {{ abs($daysLeft) }} ngày</span>
                                        @elseif($isNearDue)
                                            <span class="fw-semibold text-warning contract-text-08" >{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-warning text-dark contract-text-08" ><i class="bi bi-clock me-1"></i>Còn {{ $daysLeft }} ngày</span>
                                        @else
                                            <span class="fw-semibold text-success contract-text-08" >{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-success bg-opacity-75 contract-text-08" >Còn {{ $daysLeft }} ngày</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endunless
                            <td class="text-center">
                                <div class="d-flex flex-column align-items-center">
                                    @php $statusColor = $doc->detailed_status_color; @endphp
                                    @php
                                        $canUpdateStatus =
                                            !auth()
                                                ->user()
                                                ->hasAnyRole([\App\Enums\Role::TU_VAN->value, \App\Enums\Role::KY_THUAT->value]) &&
                                            (!auth()->user()->hasRole(\App\Enums\Role::TP_KINH_DOANH->value) ||
                                                $doc->staff_id === auth()->id());
                                    @endphp

                                    @if (!$canUpdateStatus)
                                        <span class="btn btn-sm rounded-pill px-3 py-1 fw-semibold border-0 status-badge-view"
                                            style="--sbc:{{ $statusColor['bg'] }}; --stc:{{ $statusColor['text'] }};">
                                            {{ $doc->status ?: '—' }}
                                        </span>
                                    @else
                                        <div class="position-relative" x-data="{ open: false }">
                                            <button type="button" @click="open = !open"
                                                class="btn btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1 fw-semibold border-0 status-badge-btn" style="--sbc:{{ $statusColor['bg'] }}; --stc:{{ $statusColor['text'] }};">
                                                {{ $doc->status ?: '—' }}
                                                <svg width="12" height="12" viewBox="0 0 12 12"
                                                    fill="currentColor">
                                                    <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor"
                                                        stroke-width="1.5" fill="none" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-cloak
                                                class="position-absolute bg-white rounded-3 shadow-lg py-1 mt-1 dropdown-menu-status"
                                                >
                                                @foreach ($all_statuses as $opt)
                                                    <button type="button"
                                                        class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 {{ $doc->status === $opt ? 'fw-bold' : '' }} contract-text-08"

                                                        wire:click="updateStatus({{ $doc->id }}, '{{ $opt }}')"
                                                        @click="open = false">
                                                        {{ $opt }}
                                                        @if ($doc->status === $opt)
                                                            <i class="bi bi-check2 ms-2"></i>
                                                        @endif
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <span
                                        class=" text-muted mt-1">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                                </div>
                            </td>
                            @unless ($this->isRestrictedRole)
                            <td class="text-center voucher-status-cell">
                                @php $vInfo = $doc->voucher_badge_info; @endphp
                                <span class="badge voucher-status-badge {{ $vInfo['class'] }}"
                                    title="{{ $vInfo['full_value'] }}">
                                    {{ $vInfo['label'] }}
                                </span>
                            </td>
                            @endunless
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-sm p-0 text-primary"
                                        wire:click="viewDetail({{ $doc->id }})" title="Xem chi tiết">
                                        <i class="bi bi-eye fs-5"></i>
                                    </button>
                                    @if ($this->canAssign())
                                        <button class="btn btn-sm p-0 text-success"
                                            wire:click="openAssign({{ $doc->id }})" title="Giao việc">
                                            <i class="bi bi-person-check fs-5"></i>
                                        </button>
                                    @endif
                                    @can('contracts-commercial.edit')
                                        @php
                                            $canEditDelete =
                                                !auth()->user()->hasRole(\App\Enums\Role::TP_KINH_DOANH->value) ||
                                                $doc->staff_id === auth()->id();
                                        @endphp
                                        @if ($canEditDelete)
                                            @if (!auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                                                <button class="btn btn-sm p-0 text-secondary"
                                                    wire:click="duplicate({{ $doc->id }})" title="Nhân bản">
                                                    <i class="bi bi-copy fs-5"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-sm p-0 text-warning"
                                                wire:click="edit({{ $doc->id }})" title="Chỉnh sửa">
                                                <i class="bi bi-pencil fs-5"></i>
                                            </button>
                                        @endif
                                    @endcan
                                    @can('contracts-commercial.delete')
                                        @php
                                            $canDelete =
                                                !auth()->user()->hasRole(\App\Enums\Role::TP_KINH_DOANH->value) ||
                                                $doc->staff_id === auth()->id();
                                        @endphp
                                        @if ($canDelete)
                                            <button class="btn btn-sm p-0 text-danger"
                                                wire:click="delete({{ $doc->id }})"
                                                onclick="return confirm('Xóa hợp đồng này?')" title="Xóa">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($this->isRestrictedRole? 5: 8) + ($this->canBulkDelete ? 1 : 0) }}"
                                class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($docs->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $docs->links('livewire.admin.users.pagination') }}
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModalCommercial" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold modal-title-custom">
                        Chi tiết HĐ {{ $contractTypeName }}
                        @if ($selectedDoc?->customer?->name)
                            — {{ $selectedDoc->customer->name }}
                        @elseif ($selectedDoc?->shd_cxl)
                            #{{ $selectedDoc->shd_cxl }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($selectedDoc)
                        {{-- Tabs --}}
                        <ul class="nav nav-tabs px-4 pt-3 bg-white" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-info-commercial-{{ $selectedDoc->id }}" type="button">
                                    <i class="bi bi-info-circle me-1"></i>Thông tin HĐ
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-semibold" data-bs-toggle="tab"
                                    data-bs-target="#tab-progress-commercial-{{ $selectedDoc->id }}" type="button">
                                    <i class="bi bi-diagram-3 me-1"></i>Tiến độ hoàn thành
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            {{-- Tab 1: Thông tin HĐ --}}
                            <div class="tab-pane fade show active" id="tab-info-commercial-{{ $selectedDoc->id }}"
                                role="tabpanel">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light w-30">Số HĐ NTP</th>
                                            <td class="fw-bold">{{ $selectedDoc->shd_cxl }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light w-30">Số HĐ BC</th>
                                            <td class="fw-bold">{{ $selectedDoc->shd_bc }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Báo cáo số</th>
                                            <td class="fw-bold p-2">
                                                <div class="d-flex gap-2" x-data="inlineReportEdit({{ $selectedDoc->id }}, @js($selectedDoc->report_number))">
                                                    <input type="text"
                                                           class="form-control form-control-sm fw-bold text-primary bg-light border fs-85"

                                                           x-model="value"
                                                           placeholder="Nhập báo cáo số...">
                                                    <button class="btn btn-sm btn-success d-flex align-items-center gap-1"
                                                            @click="updateReportExplicit">
                                                        <i class="bi bi-check2"></i>Lưu
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nhà thầu phụ</th>
                                            <td class="fw-bold">{{ $selectedDoc->handler?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Khách hàng</th>
                                            <td class="fw-bold text-primary">
                                                <a href="{{ $selectedDoc->customer ? route('app.customers.contracts', $selectedDoc->customer->slug) : '#' }}" class="text-decoration-none">
                                                    {{ $selectedDoc->customer?->name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Người đại diện</th>
                                            <td>{{ $selectedDoc->customer?->representative }} —
                                                {{ $selectedDoc->customer?->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Địa chỉ</th>
                                            <td>{{ $selectedDoc->customer?->address }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nhân viên CS</th>
                                            <td>{{ $selectedDoc->staff?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Phòng ban</th>
                                            <td>{{ $selectedDoc->department?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Ngày ký HĐ</th>
                                            <td>{{ $selectedDoc->signed_at?->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Ngày xuất hóa đơn</th>
                                            <td>{{ $selectedDoc->submitted_at?->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Tỉnh thành</th>
                                            <td>{{ $selectedDoc->province }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Phương thức thanh toán</th>
                                            <td>{{ $selectedDoc->payment_method }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nguồn thông tin</th>
                                            <td>{{ $selectedDoc->info_source }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Loại dịch vụ</th>
                                            <td>{{ $selectedDoc->loai_dich_vu ?: '-' }}</td>
                                        </tr>
                                        @unless ($this->isRestrictedRole)
                                            <tr>
                                                <th class="bg-light">Giá trị hợp đồng</th>
                                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->value) }}đ
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Hoa hồng</th>
                                                <td class="text-danger fw-bold">
                                                    {{ number_format($selectedDoc->commission) }}đ</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Doanh số</th>
                                                <td class="text-danger fw-bold">
                                                    {{ number_format($selectedDoc->revenue) }}đ</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Chi nhà thầu phụ</th>
                                                <td class="text-danger fw-bold">
                                                    {{ number_format($selectedDoc->ncc_payment ?? 0) }}đ</td>
                                            </tr>
                                        @endunless
                                        <tr>
                                            <th class="bg-light">Tình trạng</th>
                                            <td><span
                                                    class="badge bg-success">{{ $selectedDoc->status ?: 'PTH đang kiểm tra' }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Tình trạng chứng từ</th>
                                            <td>{{ $selectedDoc->voucher_status ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Bù trừ / Quỹ phòng</th>
                                            <td>
                                                @if ($selectedDoc->is_offset)
                                                    <span class="badge bg-warning text-dark me-1">Có bù trừ</span>
                                                @endif
                                                @if ($selectedDoc->has_room_fund)
                                                    <span class="badge bg-info text-white me-1">Có quỹ phòng</span>
                                                @endif
                                                @if ($selectedDoc->is_overdue)
                                                    <span class="badge bg-danger">Trễ hạn</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Người được giao</th>
                                            <td>
                                                @if ($selectedDoc->assignments && $selectedDoc->assignments->count() > 0)
                                                    @foreach ($selectedDoc->assignments as $assign)
                                                        <div class="mb-1">
                                                            <span
                                                                class="badge {{ $assign->user_id ? 'bg-primary' : 'bg-warning text-dark' }} me-1">{{ $assign->user?->name ?? $assign->external_assignee }}{{ $assign->user_id ? '' : ' (Ngoài)' }}</span>
                                                            <small class="text-muted">— giao bởi
                                                                {{ $assign->assigner?->name }} lúc
                                                                {{ $assign->created_at?->format('d/m/Y H:i') }}</small>
                                                            @if($assign->deadline)
                                                                <br><small class="text-warning fw-semibold"><i class="bi bi-calendar-event me-1"></i>Hạn: {{ $assign->deadline->format('d/m/Y') }}</small>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted ">Chưa giao việc</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Ghi chú</th>
                                            <td>{{ $selectedDoc->notes }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light align-middle" colspan="2"><i
                                                    class="bi bi-journal-text me-1"></i> Ghi chú tiến độ</th>
                                        </tr>
                                        @if ($progressNotes && count($progressNotes) > 0)
                                            @foreach ($progressNotes as $pNote)
                                                <tr>
                                                    <td colspan="2" class="py-2 ps-4">
                                                        <div class="d-flex flex-column">
                                                            <span
                                                                class=" fw-bold text-primary">{{ $pNote->user?->name }}
                                                                <span class="text-muted fw-normal">—
                                                                    {{ $pNote->created_at?->format('d/m/Y H:i') }}</span></span>
                                                            <span class="mt-1">{{ $pNote->note }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="2" class="text-muted  ps-4 py-2">Chưa có ghi chú
                                                    tiến độ nào.</td>
                                            </tr>
                                        @endif
                                        @if ($this->isRestrictedRole)
                                            <tr>
                                                <td colspan="2" class="px-4 pb-3 pt-2">
                                                    <textarea class="form-control form-control-sm mb-2" rows="2" wire:model="progressNote"
                                                        placeholder="Nhập ghi chú tiến độ..."></textarea>
                                                    @error('progressNote')
                                                        <div class="text-danger  mb-1">{{ $message }}</div>
                                                    @enderror
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click="addProgressNote({{ $selectedDoc->id }})"
                                                        wire:loading.attr="disabled" wire:target="addProgressNote">
                                                        <span wire:loading wire:target="addProgressNote"
                                                            class="spinner-border spinner-border-sm me-1"></span>
                                                        <i class="bi bi-plus me-1"></i> Thêm ghi chú
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- Tab 2: Tiến độ --}}
                            <div class="tab-pane fade" id="tab-progress-commercial-{{ $selectedDoc->id }}"
                                role="tabpanel">
                                <livewire:admin.contracts.contract-workflow-progress :contractType="'commercial'" :contractId="$selectedDoc->id"
                                    :key="'progress-commercial-' . $selectedDoc->id" />
                            </div>
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal (Create/Edit) -->
    <div wire:ignore.self class="modal fade" id="formModalCommercial" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-primary py-3">
                    <h5 class="modal-title fw-bold modal-title-custom">
                        @if ($isEditing)
                            Chỉnh sửa
                        @elseif ($isDuplicating)
                            Nhân bản
                        @else
                            Thêm
                        @endif
                        HĐ {{ $contractTypeName }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="bi bi-file-earmark-text me-1"></i>Thông tin hợp đồng</span>
                                <hr class="flex-fill my-0 border-primary border-opacity-25">
                            </div>
                        </div>
                        @if ($isEditing && auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Số HĐ NTP</label>
                                <input type="text" class="form-control" wire:model="formData.shd_cxl">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Số HĐ BC</label>
                                <input type="text" class="form-control" wire:model="formData.shd_bc">
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Khách hàng <span class="text-danger">*</span></label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap select-full" type="button"
                                    @click.prevent="open = !open"
                                    >
                                    {{ $customers->find($formData['customer_id'] ?? '')?->name ?? '-- Chọn khách hàng --' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                    x-cloak >
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (empty($formData['customer_id'])) active @endif"
                                        type="button" x-show="!search.length"
                                        wire:click="$set('formData.customer_id', '')" @click="open = false">-- Chọn
                                        khách hàng --</button>
                                    @foreach ($customers as $c)
                                        <button
                                            class="dropdown-item text-wrap @if (($formData['customer_id'] ?? '') == $c->id) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($c->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            class="text-wrap"
                                            wire:click="$set('formData.customer_id', {{ $c->id }})"
                                            @click="open = false">
                                            {{ $c->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('formData.customer_id')
                                <div class="text-danger  mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nhà thầu phụ</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap select-full" type="button"
                                    @click.prevent="open = !open"
                                    >
                                    {{ $handlers->find($formData['handler_id'] ?? '')?->name ?? '-- Chọn nhà thầu phụ --' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open" @click.away="open = false"
                                    x-cloak >
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if (empty($formData['handler_id'])) active @endif"
                                        type="button" x-show="!search.length"
                                        wire:click="$set('formData.handler_id', '')" @click="open = false">-- Chọn nhà
                                        thầu phụ --</button>
                                    @foreach ($handlers as $h)
                                        <button
                                            class="dropdown-item text-wrap @if (($formData['handler_id'] ?? '') == $h->id) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($h->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            class="text-wrap"
                                            wire:click="$set('formData.handler_id', {{ $h->id }})"
                                            @click="open = false">
                                            {{ $h->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @if (auth()->user()->hasAnyRole([\App\Enums\Role::TP_KINH_DOANH->value, \App\Enums\Role::GIAM_DOC->value]))
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nhân viên CS <span
                                        class="text-danger">*</span></label>
                                <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                    <button class="form-select text-start text-wrap select-full" type="button"
                                        @click.prevent="open = !open"
                                        >
                                        {{ $staffs->find($formData['staff_id'] ?? '')?->name ?? '-- Chọn nhân viên --' }}
                                    </button>
                                    <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open"
                                        @click.away="open = false" x-cloak
                                        >
                                        <input type="text" x-model="search"
                                            class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..."
                                            @click.stop>
                                        <button class="dropdown-item @if (empty($formData['staff_id'])) active @endif"
                                            type="button" x-show="!search.length"
                                            wire:click="$set('formData.staff_id', '')" @click="open = false">-- Chọn
                                            nhân
                                            viên --</button>
                                        @foreach ($staffs as $s)
                                            <button
                                                class="dropdown-item text-wrap @if (($formData['staff_id'] ?? '') == $s->id) active @endif"
                                                type="button"
                                                x-show="{{ json_encode(mb_strtolower($s->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                                class="text-wrap"
                                                wire:click="$set('formData.staff_id', {{ $s->id }})"
                                                @click="open = false">
                                                {{ $s->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @error('formData.staff_id')
                                    <div class="text-danger  mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                        <div class="col-md-6 d-none">
                            <label class="form-label small fw-semibold">Phòng ban</label>
                            <select class="form-select" wire:model="formData.department_id">
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="bi bi-calendar3 me-1"></i>Thời hạn</span>
                                <hr class="flex-fill my-0 border-primary border-opacity-25">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Ngày ký HĐ</label>
                            <input type="date" class="form-control" wire:model="formData.signed_at">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Ngày xuất hóa đơn</label>
                            <input type="date" class="form-control" wire:model="formData.submitted_at">
                        </div>
                        <div class="col-12 mt-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="bi bi-cash-coin me-1"></i>Tài chính</span>
                                <hr class="flex-fill my-0 border-primary border-opacity-25">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Giá trị HĐ <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control money-input" wire:model="formData.value">
                                <span class="input-group-text">đ</span>
                            </div>
                            @error('formData.value')
                                <div class="text-danger  mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Hoa hồng</label>
                            <div class="input-group">
                                <input type="text" class="form-control money-input" wire:model="formData.commission">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Doanh số</label>
                            <div class="input-group">
                                <input type="text" class="form-control money-input" wire:model="formData.revenue">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        @if ($isEditing && auth()->user()->hasRole(\App\Enums\Role::KE_TOAN->value))
                        <div class="col-md-3"
                             x-data="{
                                 ncc: {{ (int)($formData['ncc_payment'] ?? 0) }},
                                 revenue: {{ (int)($formData['revenue'] ?? 0) }},
                                 commission: {{ (int)($formData['commission'] ?? 0) }},
                                 get net() { return Math.max(0, this.revenue - this.commission - this.ncc); },
                                 fmt(n) { return new Intl.NumberFormat('vi-VN').format(n); }
                             }">
                            <label class="form-label small fw-semibold">Chi Nhà Cung Cấp</label>
                            <div class="input-group">
                                <input type="text" class="form-control money-input"
                                       wire:model.defer="formData.ncc_payment"
                                       x-on:input="ncc = parseInt($event.target.value.replace(/\D/g, '')) || 0"
                                       placeholder="0">
                                <span class="input-group-text">đ</span>
                            </div>
                            <small class="text-muted">Thực nhận: <strong class="text-success" x-text="fmt(net) + 'đ'"></strong></small>
                        </div>
                        @elseif($selectedDoc && $selectedDoc->ncc_payment > 0)
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Chi Nhà Cung Cấp</label>
                            <p class="mb-0 text-danger fw-bold">{{ number_format($selectedDoc->ncc_payment) }}đ</p>
                            <small class="text-muted">Thực nhận: <strong class="text-success">{{ number_format($selectedDoc->revenue - $selectedDoc->commission - $selectedDoc->ncc_payment) }}đ</strong></small>
                        </div>
                        @endif
                        <div class="col-12 mt-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-primary small text-uppercase" style="white-space:nowrap"><i class="bi bi-tags me-1"></i>Phân loại & Trạng thái</span>
                                <hr class="flex-fill my-0 border-primary border-opacity-25">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tỉnh thành</label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start select-full" type="button"
                                    @click.prevent="open = !open">
                                    {{ $formData['province'] ?? 'Chọn tỉnh thành' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2 mh-300-scroll" x-show="open"
                                    @click.away="open = false" x-cloak>
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2"
                                        placeholder="Tìm tỉnh thành..." @click.stop>
                                    <button class="dropdown-item @if(empty($formData['province'])) active @endif"
                                        type="button" x-show="!search.length"
                                        wire:click="$set('formData.province', '')" @click="open = false">-- Chọn tỉnh thành --</button>
                                    @foreach ($provinces as $p)
                                        <button
                                            class="dropdown-item @if(($formData['province'] ?? '') == $p) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($p)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            wire:click="$set('formData.province', '{{ $p }}')"
                                            @click="open = false">
                                            {{ $p }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Loại dịch vụ</label>
                            <select class="form-select" wire:model="formData.loai_dich_vu">
                                <option value="">-- Chọn loại dịch vụ --</option>
                                @foreach ($loai_dich_vu_options as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nguồn thông tin</label>
                            <input type="text" class="form-control" wire:model="formData.info_source"
                                list="info-source-list-commercial" placeholder="Nhập hoặc chọn nguồn...">
                            <datalist id="info-source-list-commercial">
                                @foreach ($info_sources as $src)
                                    <option value="{{ $src }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">PT thanh toán</label>
                            <input class="form-control" wire:model="formData.payment_method" list="pm-options"
                                placeholder="VD: Sau ký, Trước ký...">
                            <datalist id="pm-options">
                                @foreach ($payment_methods as $pm)
                                    <option value="{{ $pm }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tình trạng</label>
                            <select class="form-select" wire:model="formData.status">
                                @foreach ($all_statuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tình trạng chứng từ</label>
                            <select class="form-select" wire:model="formData.voucher_status">
                                <option value="">Chọn tình trạng</option>
                                @foreach ($voucher_status_options as $voucherStatus)
                                    <option value="{{ $voucherStatus }}">{{ $voucherStatus }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center gap-3 bg-light rounded-2 px-3 py-2">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="form_cm_offset"
                                        wire:model="formData.is_offset">
                                    <label class="form-check-label" for="form_cm_offset">Có bù trừ</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="form_cm_roomfund"
                                        wire:model="formData.has_room_fund">
                                    <label class="form-check-label" for="form_cm_roomfund">Có quỹ phòng</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="form_cm_overdue"
                                        wire:model="formData.is_overdue">
                                    <label class="form-check-label" for="form_cm_overdue">Trễ hạn</label>
                                </div>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="form_cm_renewal"
                                        wire:model="formData.is_renewal">
                                    <label class="form-check-label" for="form_cm_renewal">Tái ký</label>
                                </div>
                            </div>
                        </div>
                        @if ($formData['is_renewal'])
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">HĐ gốc (tái ký từ)</label>
                                <select class="form-select" wire:model="formData.parent_contract_id">
                                    <option value="">-- Chọn HĐ gốc --</option>
                                    @foreach ($parentContracts as $pc)
                                        <option value="{{ $pc->id }}">{{ $pc->so_hop_dong }} -
                                            {{ $pc->customer->name ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Ghi chú</label>
                            <textarea class="form-control" rows="3" wire:model="formData.notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-floppy me-1"></i>Lưu
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment Modal --}}
    <div wire:ignore.self class="modal fade" id="assignModalCommercial" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success py-3">
                    <h5 class="modal-title fw-bold modal-title-custom"><i class="bi bi-person-check me-1"></i> Giao
                        việc hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted  mb-3">Chọn nhân viên để giao việc (có thể chọn nhiều):</p>
                    <div class="list-group mh-320-scroll" >
                        @foreach ($assignable_users as $u)
                            <label class="list-group-item list-group-item-action d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 mt-1" type="checkbox"
                                    value="{{ $u->id }}" wire:model="assignUserIds">
                                <span>{{ $u->name }}<small
                                        class="text-muted d-block">{{ $u->roles->first()?->name }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Người ngoài công ty</label>
                        <input type="text" class="form-control" wire:model="assignExternal"
                            placeholder="Tên người ngoài (nếu có)">
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold">Hạn chót</label>
                        <input type="date" class="form-control" wire:model="assignDeadline">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" wire:click="saveAssign"
                        wire:loading.attr="disabled" wire:target="saveAssign">
                        <span wire:loading wire:target="saveAssign"
                            class="spinner-border spinner-border-sm me-1"></span>
                        Lưu giao việc
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Workflow Modal --}}
    <div wire:ignore.self class="modal fade" id="workflowModalCommercial" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3 me-2"></i>Cập nhật tiến độ hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        wire:click="closeWorkflow"></button>
                </div>
                <div class="modal-body p-0">
                    @if ($workflowContractId)
                        <livewire:admin.contracts.contract-workflow-panel :contractType="'commercial'" :contractId="$workflowContractId"
                            :key="'wf-modal-commercial-' . $workflowContractId" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.addEventListener('openDetailModal', () => {
                new bootstrap.Modal(document.getElementById('detailModalCommercial')).show();
            });
            Livewire.on('openFormModal', () => {
                new bootstrap.Modal(document.getElementById('formModalCommercial')).show();
            });
            Livewire.on('closeFormModal', () => {
                bootstrap.Modal.getInstance(document.getElementById('formModalCommercial'))?.hide();
            });
            window.addEventListener('openAssignModal', () => {
                new bootstrap.Modal(document.getElementById('assignModalCommercial')).show();
            });
            Livewire.on('closeAssignModal', () => {
                bootstrap.Modal.getInstance(document.getElementById('assignModalCommercial'))?.hide();
            });
            window.addEventListener('openWorkflowModal', () => {
                new bootstrap.Modal(document.getElementById('workflowModalCommercial')).show();
            });

            document.addEventListener('alpine:init', () => {
                Alpine.data('inlineReportEdit', (docId, initialValue) => ({
                    value: initialValue,
                    updateReport(event) {
                        let newVal = event.target.value;
                        if (newVal === this.value) return;

                        Swal.fire({
                            title: 'Xác nhận lưu?',
                            text: 'Cập nhật Báo cáo số thành: ' + newVal + ' ?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Đồng ý',
                            cancelButtonText: 'Hủy'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.value = newVal;
                                this.$wire.updateInlineReportNumber(docId, newVal);
                            } else {
                                event.target.value = this.value;
                            }
                        });
                    },
                    updateReportExplicit() {
                        let newVal = this.value;
                        Swal.fire({
                            title: 'Xác nhận lưu?',
                            text: 'Cập nhật Báo cáo số thành: ' + newVal + ' ?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Đồng ý',
                            cancelButtonText: 'Hủy'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.$wire.updateInlineReportNumber(docId, newVal);
                            }
                        });
                    }
                }));
            });
        </script>
    @endpush
</div>
