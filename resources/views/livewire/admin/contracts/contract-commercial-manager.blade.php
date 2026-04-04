<div>

    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">HĐ NC & CĐ Công nghệ</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng thống kê</a></li>
                    <li class="breadcrumb-item active">HĐ NC & CĐ Công nghệ</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
            <button wire:click="create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Thêm Hợp Đồng
            </button>
            @endunless
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tìm kiếm theo SHD hoặc Tên KH" wire:model.live.debounce.300ms="search">
                <button class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                        <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
            <h6 class="mb-0 fw-bold">Bộ lọc Hợp đồng thương mại</h6>
            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#filterBody">−</button>
        </div>
        <div class="collapse show" id="filterBody">
            <div class="card-body p-4">
                <div class="row g-3">
                    @unless(auth()->user()->hasAnyRole(['tu-van', 'kinh-doanh']))
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày ký hợp đồng</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.signed_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.signed_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Ngày hợp đồng về</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.submitted_from">
                            <input type="date" class="form-control form-control-xs" wire:model.live="filter.submitted_to">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold custom-filter-label">Tỉnh thành</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.province">
                            <option value="">Chọn tỉnh thành</option>
                            @foreach($provinces as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-3 pb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cm_offset" wire:model.live="filter.is_offset">
                            <label class="form-check-label small" for="cm_offset">Có bù trừ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cm_roomfund" wire:model.live="filter.has_room_fund">
                            <label class="form-check-label small" for="cm_roomfund">Có quỹ phòng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cm_overdue" wire:model.live="filter.is_overdue">
                            <label class="form-check-label small" for="cm_overdue">Trễ hạn</label>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Phòng ban</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.department_id">
                            <option value="">Chọn phòng ban</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Nguồn thông tin</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.info_source">
                            <option value="">Chọn Nguồn thông...</option>
                            @foreach($info_sources as $src)
                                <option value="{{ $src }}">{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Phương thức thanh toán</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.payment_method">
                            <option value="">Chọn phương thức...</option>
                            @foreach($payment_methods as $pm)
                                <option value="{{ $pm }}">{{ $pm }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endunless
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.status">
                            <option value="">Chọn tình trạng</option>
                            @foreach($all_statuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    @unless(auth()->user()->hasAnyRole(['tu-van', 'kinh-doanh']))
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Loại dịch vụ</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.loai_dich_vu">
                            <option value="">Chọn loại dịch vụ</option>
                            @foreach($loai_dich_vu_options as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endunless

                    <div class="col-md-12 d-flex gap-2 mt-2">
                        <button class="btn btn-info text-white px-4 btn-filter" wire:click="$refresh">
                            <i class="bi bi-search me-1"></i>Lọc
                        </button>
                        <button class="btn btn-secondary px-4 btn-filter" wire:click="resetFilters">
                            <i class="bi bi-x-circle me-1"></i>Xóa lọc
                        </button>
                        <button wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="btn btn-success px-4 btn-filter">
                            <span wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm me-1"></span>
                            <i wire:loading.remove wire:target="exportExcel" class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
                        </button>
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
        <div class="table-responsive" style="overflow:visible; min-height:350px;">
            <table class="table table-hover align-middle mb-0 table-xs">
                <thead class="bg-light bg-opacity-50">
                    <tr class="small text-muted fw-bold">
                        <th class="ps-4">Thông tin hợp đồng</th>
                        <th>Khách hàng</th>
                        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                        <th class="text-center">Giá trị hợp đồng</th>
                        <th class="text-center">Hoa hồng</th>
                        <th class="text-center">Doanh số</th>
                        @endunless
                        <th class="text-center">Được giao</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-4">
                            <div class="d-flex flex-column">
                                <span class="small">Số HĐ BC:<span class="fw-bold">{{ $doc->shd_bc }}</span></span>
                                <span class="small">Ngày ký hợp đồng: <span class="fw-bold">{{ $doc->signed_at ? $doc->signed_at->format('d/m/Y') : '-' }}</span></span>
                                <span class="small">Nhân viên CS:<span class="fw-bold">{{ $doc->staff?->name }}</span></span>
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-primary">{{ $doc->customer?->name }}</span>
                                <span class="small">{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }}</span>
                                <span class="small text-muted">{{ $doc->customer?->email }}</span>
                                <span class="small text-muted">{{ $doc->customer?->address }}</span>
                            </div>
                        </td>
                        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                        </td>
                        @endunless
                        <td class="text-center">
                            @if($doc->assignments->count() > 0)
                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                @foreach($doc->assignments->take(3) as $assign)
                                <span class="badge bg-secondary" style="font-size:0.65rem;" title="{{ $assign->user?->name }}">{{ Str::limit($assign->user?->name ?? '?', 8) }}</span>
                                @endforeach
                                @if($doc->assignments->count() > 3)
                                <span class="badge bg-light text-dark" style="font-size:0.65rem;">+{{ $doc->assignments->count() - 3 }}</span>
                                @endif
                            </div>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                @php
                                    $statusColor = match($doc->status) {
                                        'HOÀN THÀNH' => ['bg' => '#d1e7dd', 'text' => '#198754'],
                                        'ĐÃ HỦY' => ['bg' => '#f8d7da', 'text' => '#dc3545'],
                                        default => ['bg' => '#fff3cd', 'text' => '#b45309'],
                                    };
                                @endphp
                                <div class="position-relative" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" class="btn btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1 fw-semibold border-0"
                                        style="font-size:0.75rem; background:{{ $statusColor['bg'] }}; color:{{ $statusColor['text'] }};">
                                        {{ $doc->status ?: 'ĐANG THỰC HIỆN' }}
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak
                                        class="position-absolute bg-white rounded-3 shadow-lg py-1 mt-1"
                                        style="z-index:1050; min-width:160px; right:50%; transform:translateX(50%);">
                                        @foreach(['ĐANG THỰC HIỆN', 'HOÀN THÀNH', 'ĐÃ HỦY'] as $opt)
                                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between px-3 py-2 {{ $doc->status === $opt ? 'fw-bold' : '' }}"
                                            style="font-size:0.8rem;" wire:click="updateStatus({{ $doc->id }}, '{{ $opt }}')" @click="open = false">
                                            {{ $opt }}
                                            @if($doc->status === $opt) <i class="bi bi-check2 ms-2"></i> @endif
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="small text-muted mt-1">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm p-0 text-primary" wire:click="viewDetail({{ $doc->id }})" title="Xem chi tiết">
                                    <i class="bi bi-eye fs-5"></i>
                                </button>
                                @if(auth()->user()->hasAnyRole(['giam-doc', 'quan-ly', 'tp-kinh-doanh', 'it']))
                                <button class="btn btn-sm p-0 text-success" wire:click="openAssign({{ $doc->id }})" title="Giao việc">
                                    <i class="bi bi-person-check fs-5"></i>
                                </button>
                                @endif
                                @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                                <button class="btn btn-sm p-0 text-warning" wire:click="edit({{ $doc->id }})" title="Chỉnh sửa">
                                    <i class="bi bi-pencil fs-5"></i>
                                </button>
                                <button class="btn btn-sm p-0 text-danger" wire:click="delete({{ $doc->id }})" onclick="return confirm('Xóa hợp đồng này?')" title="Xóa">
                                    <i class="bi bi-trash fs-5"></i>
                                </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($docs->hasPages())
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
                    <h5 class="modal-title fw-bold modal-title-custom">Chi tiết Hợp Đồng Thương Mại</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedDoc)
                    {{-- Tabs --}}
                    <ul class="nav nav-tabs px-4 pt-3 bg-white" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-info-commercial-{{ $selectedDoc->id }}" type="button">
                                <i class="bi bi-info-circle me-1"></i>Thông tin HĐ
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-progress-commercial-{{ $selectedDoc->id }}" type="button">
                                <i class="bi bi-diagram-3 me-1"></i>Tiến độ hoàn thành
                            </button>
                        </li>
                        @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-payment-commercial-{{ $selectedDoc->id }}" type="button">
                                <i class="bi bi-cash-stack me-1"></i>Lịch thanh toán
                            </button>
                        </li>
                        @endunless
                    </ul>
                    <div class="tab-content">
                    {{-- Tab 1: Thông tin HĐ --}}
                    <div class="tab-pane fade show active" id="tab-info-commercial-{{ $selectedDoc->id }}" role="tabpanel">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-30">Số HĐ BC</th>
                                <td class="fw-bold">{{ $selectedDoc->shd_bc }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Khách hàng</th>
                                <td class="fw-bold text-primary">{{ $selectedDoc->customer?->name }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Người đại diện</th>
                                <td>{{ $selectedDoc->customer?->representative }} — {{ $selectedDoc->customer?->phone }}</td>
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
                                <th class="bg-light">Ngày HĐ về</th>
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
                            @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                            <tr>
                                <th class="bg-light">Giá trị hợp đồng</th>
                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->value) }}đ</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Hoa hồng</th>
                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->commission) }}đ</td>
                            </tr>
                            <tr>
                                <th class="bg-light">Doanh số</th>
                                <td class="text-danger fw-bold">{{ number_format($selectedDoc->revenue) }}đ</td>
                            </tr>
                            @endunless
                            <tr>
                                <th class="bg-light">Tình trạng</th>
                                <td><span class="badge bg-success">{{ $selectedDoc->status ?: 'Đang thực hiện' }}</span></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Bù trừ / Quỹ phòng</th>
                                <td>
                                    @if($selectedDoc->is_offset) <span class="badge bg-warning text-dark me-1">Có bù trừ</span> @endif
                                    @if($selectedDoc->has_room_fund) <span class="badge bg-info text-white me-1">Có quỹ phòng</span> @endif
                                    @if($selectedDoc->is_overdue) <span class="badge bg-danger">Trễ hạn</span> @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Người được giao</th>
                                <td>
                                    @if($selectedDoc->assignments && $selectedDoc->assignments->count() > 0)
                                        @foreach($selectedDoc->assignments as $assign)
                                        <div class="mb-1">
                                            <span class="badge bg-primary me-1">{{ $assign->user?->name }}</span>
                                            <small class="text-muted">— giao bởi {{ $assign->assigner?->name }} lúc {{ $assign->created_at?->format('d/m/Y H:i') }}</small>
                                        </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">Chưa giao việc</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Ghi chú</th>
                                <td>{{ $selectedDoc->notes }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light align-middle" colspan="2"><i class="bi bi-journal-text me-1"></i> Ghi chú tiến độ</th>
                            </tr>
                            @if($progressNotes && count($progressNotes) > 0)
                                @foreach($progressNotes as $pNote)
                                <tr>
                                    <td colspan="2" class="py-2 ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="small fw-bold text-primary">{{ $pNote->user?->name }} <span class="text-muted fw-normal">— {{ $pNote->created_at?->format('d/m/Y H:i') }}</span></span>
                                            <span class="mt-1">{{ $pNote->note }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                            <tr><td colspan="2" class="text-muted small ps-4 py-2">Chưa có ghi chú tiến độ nào.</td></tr>
                            @endif
                            @if(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                            <tr>
                                <td colspan="2" class="px-4 pb-3 pt-2">
                                    <textarea class="form-control form-control-sm mb-2" rows="2" wire:model="progressNote" placeholder="Nhập ghi chú tiến độ..."></textarea>
                                    @error('progressNote') <div class="text-danger small mb-1">{{ $message }}</div> @enderror
                                    <button class="btn btn-sm btn-primary" wire:click="addProgressNote({{ $selectedDoc->id }})" wire:loading.attr="disabled" wire:target="addProgressNote">
                                        <span wire:loading wire:target="addProgressNote" class="spinner-border spinner-border-sm me-1"></span>
                                        <i class="bi bi-plus me-1"></i> Thêm ghi chú
                                    </button>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    </div>

                    {{-- Tab 2: Tiến độ --}}
                    <div class="tab-pane fade" id="tab-progress-commercial-{{ $selectedDoc->id }}" role="tabpanel">
                        <livewire:admin.contracts.contract-workflow-progress
                            :contractType="'commercial'"
                            :contractId="$selectedDoc->id"
                            :key="'progress-commercial-' . $selectedDoc->id"
                        />
                    </div>

                    @unless(auth()->user()->hasAnyRole(['tu-van', 'ky-thuat']))
                    {{-- Tab 3: Lịch thanh toán --}}
                    <div class="tab-pane fade" id="tab-payment-commercial-{{ $selectedDoc->id }}" role="tabpanel">
                        <livewire:admin.contracts.contract-payment-schedule-manager
                            :contractType="'commercial'"
                            :contractId="$selectedDoc->id"
                            :key="'payment-commercial-' . $selectedDoc->id"
                        />
                    </div>
                    @endunless
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
                    <h5 class="modal-title fw-bold modal-title-custom">{{ $isEditing ? 'Chỉnh sửa' : 'Thêm' }} Hợp Đồng Thương Mại</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số HĐ BC</label>
                            <input type="text" class="form-control" wire:model="formData.shd_bc">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Khách hàng <span class="text-danger">*</span></label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap"
                                        type="button" @click.prevent="open = !open"
                                        style="width: 100%; white-space: normal !important; height: auto !important; min-height: 38px;">
                                    {{ $customers->find($formData['customer_id'] ?? '')?->name ?? '-- Chọn khách hàng --' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if(empty($formData['customer_id'])) active @endif"
                                            type="button"
                                            x-show="!search.length"
                                            wire:click="$set('formData.customer_id', '')" @click="open = false">-- Chọn khách hàng --</button>
                                    @foreach($customers as $c)
                                    <button class="dropdown-item text-wrap @if(($formData['customer_id'] ?? '') == $c->id) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($c->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            style="white-space: normal !important;"
                                            wire:click="$set('formData.customer_id', {{ $c->id }})" @click="open = false">
                                        {{ $c->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('formData.customer_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nhân viên CS <span class="text-danger">*</span></label>
                            <div class="dropdown-custom w-100" x-data="{ open: false, search: '' }">
                                <button class="form-select text-start text-wrap"
                                        type="button" @click.prevent="open = !open"
                                        style="width: 100%; white-space: normal !important; height: auto !important; min-height: 38px;">
                                    {{ $staffs->find($formData['staff_id'] ?? '')?->name ?? '-- Chọn nhân viên --' }}
                                </button>
                                <div class="dropdown-menu-custom w-100 p-2" x-show="open" @click.away="open = false" x-cloak style="max-height: 300px; overflow-y: auto;">
                                    <input type="text" x-model="search" class="form-control form-control-sm mb-2" placeholder="Tìm kiếm..." @click.stop>
                                    <button class="dropdown-item @if(empty($formData['staff_id'])) active @endif"
                                            type="button"
                                            x-show="!search.length"
                                            wire:click="$set('formData.staff_id', '')" @click="open = false">-- Chọn nhân viên --</button>
                                    @foreach($staffs as $s)
                                    <button class="dropdown-item text-wrap @if(($formData['staff_id'] ?? '') == $s->id) active @endif"
                                            type="button"
                                            x-show="{{ json_encode(mb_strtolower($s->name)) }}.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''))"
                                            style="white-space: normal !important;"
                                            wire:click="$set('formData.staff_id', {{ $s->id }})" @click="open = false">
                                        {{ $s->name }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('formData.staff_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 d-none">
                            <label class="form-label fw-bold">Phòng ban</label>
                            <select class="form-select" wire:model="formData.department_id">
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày ký HĐ</label>
                            <input type="date" class="form-control" wire:model="formData.signed_at">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày HĐ về</label>
                            <input type="date" class="form-control" wire:model="formData.submitted_at">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Giá trị HĐ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control money-input" wire:model="formData.value">
                            @error('formData.value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hoa hồng</label>
                            <input type="text" class="form-control money-input" wire:model="formData.commission">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Doanh số</label>
                            <input type="text" class="form-control money-input" wire:model="formData.revenue">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tỉnh thành</label>
                            <input type="text" class="form-control" wire:model="formData.province" list="province-list-commercial">
                            <datalist id="province-list-commercial">
                                @foreach($provinces as $p) <option value="{{ $p }}"> @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loại dịch vụ</label>
                            <select class="form-select" wire:model="formData.loai_dich_vu">
                                <option value="">-- Chọn loại dịch vụ --</option>
                                @foreach($loai_dich_vu_options as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nguồn thông tin</label>
                            <input type="text" class="form-control" wire:model="formData.info_source" list="info-source-list-commercial" placeholder="Nhập hoặc chọn nguồn...">
                            <datalist id="info-source-list-commercial">
                                @foreach($info_sources as $src)
                                    <option value="{{ $src }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">PT thanh toán</label>
                            <input class="form-control" wire:model="formData.payment_method" list="pm-options" placeholder="VD: Sau ký, Trước ký...">
                            <datalist id="pm-options">
                                @foreach($payment_methods as $pm)
                                    <option value="{{ $pm }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tình trạng</label>
                            <select class="form-select" wire:model="formData.status">
                                <option value="ĐANG THỰC HIỆN">Đang thực hiện</option>
                                <option value="HOÀN THÀNH">Hoàn thành</option>
                                <option value="ĐÃ HỦY">Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-12 d-flex gap-4 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form_cm_offset" wire:model="formData.is_offset">
                                <label class="form-check-label" for="form_cm_offset">Có bù trừ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form_cm_roomfund" wire:model="formData.has_room_fund">
                                <label class="form-check-label" for="form_cm_roomfund">Có quỹ phòng</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form_cm_overdue" wire:model="formData.is_overdue">
                                <label class="form-check-label" for="form_cm_overdue">Trễ hạn</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="form_cm_renewal" wire:model="formData.is_renewal">
                                <label class="form-check-label" for="form_cm_renewal">Tái ký</label>
                            </div>
                        </div>
                        @if($formData['is_renewal'])
                        <div class="col-md-6">
                            <label class="form-label fw-bold">HĐ gốc (tái ký từ)</label>
                            <select class="form-select" wire:model="formData.parent_contract_id">
                                <option value="">-- Chọn HĐ gốc --</option>
                                @foreach($parentContracts as $pc)
                                    <option value="{{ $pc->id }}">{{ $pc->so_hop_dong }} - {{ $pc->customer->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Ghi chú</label>
                            <textarea class="form-control" rows="3" wire:model="formData.notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Lưu Lại
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
                    <h5 class="modal-title fw-bold modal-title-custom"><i class="bi bi-person-check me-1"></i> Giao việc hợp đồng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Chọn nhân viên để giao việc (có thể chọn nhiều):</p>
                    <div class="list-group" style="max-height: 320px; overflow-y: auto;">
                        @foreach($assignable_users as $u)
                        <label class="list-group-item list-group-item-action d-flex gap-2">
                            <input class="form-check-input flex-shrink-0 mt-1" type="checkbox" value="{{ $u->id }}" wire:model="assignUserIds">
                            <span>{{ $u->name }}<small class="text-muted d-block">{{ $u->roles->first()?->name }}</small></span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" wire:click="saveAssign" wire:loading.attr="disabled" wire:target="saveAssign">
                        <span wire:loading wire:target="saveAssign" class="spinner-border spinner-border-sm me-1"></span>
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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="closeWorkflow"></button>
                </div>
                <div class="modal-body p-0">
                    @if($workflowContractId)
                    <livewire:admin.contracts.contract-workflow-panel
                        :contractType="'commercial'"
                        :contractId="$workflowContractId"
                        :key="'wf-modal-commercial-' . $workflowContractId"
                    />
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
    </script>
    @endpush
</div>
