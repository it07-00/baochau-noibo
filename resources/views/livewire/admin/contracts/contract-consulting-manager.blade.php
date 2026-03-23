<div>

    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Quản lý Hợp đồng tư vấn</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Hợp đồng tư vấn</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success d-flex align-items-center gap-2" wire:click="create">
                <i class="bi bi-plus-lg"></i> Thêm Hợp Đồng
            </button>
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
            <h6 class="mb-0 fw-bold">Bộ lọc Hợp đồng tư vấn</h6>
            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#filterBodyConsulting">−</button>
        </div>
        <div class="collapse show" id="filterBodyConsulting">
            <div class="card-body p-4">
                <div class="row g-3">
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
                            <input class="form-check-input" type="checkbox" id="ct_offset" wire:model.live="filter.is_offset">
                            <label class="form-check-label small" for="ct_offset">Có bù trừ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ct_roomfund" wire:model.live="filter.has_room_fund">
                            <label class="form-check-label small" for="ct_roomfund">Có quỹ phòng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ct_overdue" wire:model.live="filter.is_overdue">
                            <label class="form-check-label small" for="ct_overdue">Trễ hạn</label>
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
                            <option value="MỚI">MỚI</option>
                            <option value="TÁI KÝ">TÁI KÝ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Phương thức thanh toán</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.payment_method">
                            <option value="">Chọn phương thức...</option>
                            <option value="Sau ký">Sau ký</option>
                            <option value="Trước ký">Trước ký</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.status">
                            <option value="">Chọn tình trạng</option>
                            @foreach($all_statuses as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold custom-filter-label">Tình trạng tái ký</label>
                        <select class="form-select form-control-xs" wire:model.live="filter.renewal_status">
                            <option value="">Chọn tình trạng</option>
                            @foreach($renewal_statuses as $rs)
                                <option value="{{ $rs }}">{{ $rs }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 d-flex gap-2 mt-2">
                        <button class="btn btn-info text-white px-4 btn-filter" wire:click="$refresh">
                            <i class="bi bi-search me-1"></i>Lọc
                        </button>
                        <button class="btn btn-success px-4 btn-filter">
                            <i class="bi bi-file-earmark-excel me-1"></i>Xuất Excel
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
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="mb-0 fw-bold">Danh sách Hợp đồng tư vấn</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-xs">
                <thead class="bg-light bg-opacity-50">
                    <tr class="small text-muted text-uppercase fw-bold">
                        <th class="ps-4">Thông tin hợp đồng</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Giá trị hợp đồng</th>
                        <th class="text-center">Hoa hồng</th>
                        <th class="text-center">Doanh số</th>
                        <th class="text-center">Tình trạng tái ký</th>
                        <th class="text-center">Tình trạng</th>
                        <th class="text-center pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                    <tr class="border-bottom border-light">
                        <td class="ps-4 py-4">
                            <div class="d-flex flex-column">
                                <span class="small">SHD AD: <span class="fw-bold">{{ $doc->shd_ad }}</span></span>
                                <span class="small">Ngày ký hợp đồng: <span class="fw-bold">{{ $doc->signed_at ? $doc->signed_at->format('d/m/Y') : '-' }}</span></span>
                                <span class="small">NVCS: <span class="fw-bold">{{ $doc->staff?->name }}</span></span>
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-uppercase text-primary">{{ $doc->customer?->name }}</span>
                                <span class="small">{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }}</span>
                                <span class="small text-muted">{{ $doc->customer?->email }}</span>
                                <span class="small text-muted">{{ $doc->customer?->address }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->value) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->commission) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-danger">{{ number_format($doc->revenue) }}đ</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $doc->renewal_status ?: 'Chưa tái ký' }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center">
                                <span class="badge bg-{{ $doc->status_color }} py-2 px-3">{{ $doc->status_label }}</span>
                                <span class="small text-muted mt-1">{{ $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-primary btn-xs text-white px-2" wire:click="viewDetail({{ $doc->id }})" style="font-size: 10px; min-width: 35px;">XEM</button>
                                <button class="btn btn-info btn-xs text-white px-2" wire:click="edit({{ $doc->id }})" style="font-size: 10px; min-width: 35px;">SỬA</button>
                                <button class="btn btn-danger btn-xs text-white px-2" onclick="confirm('Xóa?') || event.stopImmediatePropagation()" wire:click="delete({{ $doc->id }})" style="font-size: 10px; min-width: 35px;">XÓA</button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Không tìm thấy hợp đồng nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($docs->hasPages())
        <div class="px-4 py-3 border-top">
            {{ $docs->links() }}
        </div>
        @endif
    </div>

    <!-- Detail Modal -->
    <div wire:ignore.self class="modal fade" id="detailModalConsulting" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content overflow-hidden border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title fw-bold modal-title-custom">Chi tiết Hợp Đồng Tư Vấn</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @if($selectedDoc)
                    <!-- Stepper -->
                    <div class="p-4 bg-light bg-opacity-50 border-bottom">
                        <div class="workflow-stepper">
                            @php
                                $status = $selectedDoc->workflow_status;
                                $steps = [
                                    ['label' => 'Kinh doanh', 'statuses' => ['draft', 'rejected_accounting', 'rejected_director']],
                                    ['label' => 'Kế toán', 'statuses' => ['pending_accounting']],
                                    ['label' => 'GĐ ký', 'statuses' => ['pending_director']],
                                    ['label' => 'TPKD Gán', 'statuses' => ['approved_director', 'rejected_final_review']],
                                    ['label' => 'Tư vấn', 'statuses' => ['consultant_assigned', 'consulting_receiving', 'consulting_survey', 'consulting_processing', 'waiting_client', 'client_confirmed', 'incident']],
                                    ['label' => 'Hoàn thành', 'statuses' => ['pending_final_review', 'finished']]
                                ];
                                
                                $currentIndex = 0;
                                foreach($steps as $index => $step) {
                                    if(in_array($status, $step['statuses'])) {
                                        $currentIndex = $index;
                                        break;
                                    }
                                }
                                if($status == 'finished') $currentIndex = 5;
                            @endphp
                            
                            @foreach($steps as $index => $step)
                                <div class="stepper-item {{ $index < $currentIndex ? 'completed' : ($index == $currentIndex ? 'active' : '') }} {{ ($index == $currentIndex && str_contains($status, 'rejected')) ? 'rejected' : '' }}">
                                    <div class="stepper-circle">
                                        @if($index < $currentIndex) <i class="bi bi-check-lg"></i> @else {{ $index + 1 }} @endif
                                    </div>
                                    <div class="stepper-label">{{ $step['label'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Panel -->
                    <div class="p-3 border-bottom bg-white">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold pe-3 border-end">THAO TÁC:</span>
                            
                            {{-- Sales Staff Actions --}}
                            @if(in_array($status, ['draft', 'rejected_accounting', 'rejected_director']) && ($selectedDoc->staff_id == auth()->id() || auth()->user()->hasRole('it')))
                                 <button class="btn btn-primary btn-sm px-3" wire:click="submitToAccounting">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Nộp duyệt kế toán
                                </button>
                            @endif

                            {{-- Accounting Actions --}}
                            @can('contracts-consulting.verify')
                                @if($status == 'pending_accounting')
                                    <div class="input-group input-group-sm w-auto">
                                        <input type="text" class="form-control" placeholder="Ghi chú nếu từ chối..." wire:model="workflow_comment">
                                        <button class="btn btn-success" wire:click="verifyAccounting(true)">Duyệt (Chuyển GĐ)</button>
                                        <button class="btn btn-danger" wire:click="verifyAccounting(false)">Từ chối</button>
                                    </div>
                                @endif
                            @endcan

                            {{-- Director Actions --}}
                            @can('contracts-consulting.approve')
                                @if($status == 'pending_director')
                                    <div class="input-group input-group-sm w-auto">
                                        <input type="text" class="form-control" placeholder="Ghi chú nếu từ chối..." wire:model="workflow_comment">
                                        <button class="btn btn-success" wire:click="approveDirector(true)">Ký phê duyệt</button>
                                        <button class="btn btn-danger" wire:click="approveDirector(false)">Không duyệt</button>
                                    </div>
                                @endif
                            @endcan

                            {{-- TPKD Actions - Assign --}}
                            @can('contracts-consulting.assign')
                                @if($status == 'approved_director' || $status == 'rejected_final_review')
                                    <div class="input-group input-group-sm w-auto">
                                        <select class="form-select" wire:model="workflow_consultant_id">
                                            <option value="">-- Chọn NV Tư vấn --</option>
                                            @foreach(\App\Models\User::all() as $u) {{-- Should filter by department consulting later --}}
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-primary" wire:click="assignConsultant">Gán NV & Bắt đầu</button>
                                    </div>
                                @endif
                            @endcan

                            {{-- Consultant Actions --}}
                            @if(($selectedDoc->consultant_id == auth()->id() || auth()->user()->hasRole('it')))
                                @php
                                    $nextMilestone = match($status) {
                                        'consultant_assigned' => ['receiving', 'Xác nhận tiếp nhận'],
                                        'consulting_receiving' => ['survey', 'Cập nhật: Đã khảo sát'],
                                        'consulting_survey' => ['processing', 'Cập nhật: Đang thực hiện'],
                                        'consulting_processing' => ['waiting_client', 'Cập nhật: Chờ KH duyệt'],
                                        'waiting_client' => ['confirmed', 'Cập nhật: KH đã xác nhận'],
                                        default => null
                                    };
                                @endphp

                                @if($nextMilestone)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info text-white" onclick="document.getElementById('workflowFile').click()">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg> Chọn file
                                            </button>
                                            <button class="btn btn-primary" wire:click="updateMilestone('{{ $nextMilestone[0] }}')" @if(!$workflow_file) disabled @endif>
                                                {{ $nextMilestone[1] }}
                                            </button>
                                        </div>
                                        <button class="btn btn-outline-danger btn-sm" wire:click="$set('workflow_milestone', 'incident')" data-bs-toggle="collapse" data-bs-target="#incidentPanel">Báo sự cố</button>
                                        <input type="file" id="workflowFile" class="d-none" wire:model="workflow_file">
                                    </div>
                                @endif

                                @if($status == 'client_confirmed')
                                    <button class="btn btn-success btn-sm" wire:click="finalReview(true)">Nộp hoàn thành (Chờ Duyệt)</button>
                                @endif
                            @endif

                            {{-- TPKD Final Review --}}
                            @can('contracts-consulting.final-review')
                                @if($status == 'pending_final_review')
                                    <div class="input-group input-group-sm w-auto">
                                        <input type="text" class="form-control" placeholder="Lý do yêu cầu sửa..." wire:model="workflow_comment">
                                        <button class="btn btn-success" wire:click="finalReview(true)">Duyệt Hoàn Thành</button>
                                        <button class="btn btn-warning" wire:click="finalReview(false)">Yêu cầu cập nhật</button>
                                    </div>
                                @endif
                            @endcan
                        </div>
                        
                        <div wire:loading wire:target="workflow_file">
                            <div class="small text-muted mt-1"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M5 22h14"></path><path d="M5 2h14"></path><path d="M17 22v-4.17c0-1.3-.52-2.55-1.44-3.47L12 11l-3.56 3.36c-.92.92-1.44 2.17-1.44 3.47V22"></path><path d="M7 2v4.17c0 1.3.52 2.55 1.44 3.47L12 13l3.56-3.36c.92-.92 1.44-2.17 1.44-3.47V2"></path></svg> Đang tải tệp...</div>
                        </div>
                        @if($workflow_file)
                            <div class="small text-success mt-1"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Đã chọn: {{ $workflow_file->getClientOriginalName() }}</div>
                        @endif

                        <div class="collapse mt-2" id="incidentPanel" wire:ignore.self>
                            <div class="p-3 border rounded bg-danger bg-opacity-10">
                                <label class="small fw-bold">Báo cáo Sự cố / Khó khăn:</label>
                                <textarea class="form-control form-control-sm mb-2" rows="2" wire:model="workflow_comment" placeholder="Mô tả sự cố..."></textarea>
                                <button class="btn btn-danger btn-sm" wire:click="updateMilestone('incident')">Gửi báo cáo Sự cố</button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-0">
                        <div class="col-md-7 border-end">
                            <div class="p-3 bg-light fw-bold border-bottom">Thông tin chung</div>
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light w-40 small">SHD AD</th>
                                        <td class="fw-bold small">{{ $selectedDoc->shd_ad }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light small">Khách hàng</th>
                                        <td class="text-uppercase fw-bold text-primary small">{{ $selectedDoc->customer?->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light small">NVCS / Phòng ban</th>
                                        <td class="small">{{ $selectedDoc->staff?->name }} - {{ $selectedDoc->department?->name }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light small">Tỉnh / Nguồn / PTTT</th>
                                        <td class="small">{{ $selectedDoc->province }} / {{ $selectedDoc->info_source }} / {{ $selectedDoc->payment_method }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light small">Giá trị HĐ / Doanh số</th>
                                        <td class="small"><span class="text-danger fw-bold">{{ number_format($selectedDoc->value) }}đ</span> / <span class="text-success fw-bold">{{ number_format($selectedDoc->revenue) }}đ</span></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light small">NV Tư vấn phụ trách</th>
                                        <td class="small fw-bold text-info">
                                            @if($selectedDoc->consultant)
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>{{ $selectedDoc->consultant->name }}
                                            @else
                                                <span class="text-muted italic">Chưa gán</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light small">Ghi chú</th>
                                        <td class="small italic">{{ $selectedDoc->notes }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-5">
                            <div class="p-3 bg-light fw-bold border-bottom d-flex justify-content-between">
                                <span>Lịch sử luồng & File</span>
                                <span class="badge bg-{{ $selectedDoc->status_color }}">{{ $selectedDoc->status_label }}</span>
                            </div>
                            <div class="p-0 overflow-auto" style="max-height: 400px;">
                                <div class="list-group list-group-flush">
                                    @forelse($selectedDoc->workflowSteps()->latest()->get() as $step)
                                        <div class="list-group-item border-0 border-bottom">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="small fw-bold">{{ $step->step_name }}</span>
                                                <span class="small text-muted">{{ $step->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-{{ $step->action == 'approve' ? 'success' : ($step->action == 'reject' ? 'danger' : 'info') }} xsmall">
                                                    {{ $step->action }}
                                                </span>
                                                <span class="small fw-bold">{{ $step->user?->name }}</span>
                                            </div>
                                            @if($step->comment)
                                                <div class="small bg-light p-1 rounded italic text-muted border-start border-3 border-secondary">
                                                    "{{ $step->comment }}"
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="p-3 text-center text-muted small">Chưa có lịch sử</div>
                                    @endforelse
                                </div>
                                
                                <div class="p-3 bg-light border-top small fw-bold">Tệp đính kèm theo mốc:</div>
                                <div class="list-group list-group-flush">
                                    @foreach($selectedDoc->milestoneFiles as $file)
                                        <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="list-group-item list-group-item-action py-1 small d-flex align-items-center">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger me-2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $file->milestone }}</div>
                                                <div class="xsmall text-muted">{{ $file->uploader?->name }} - {{ $file->created_at->format('d/m/Y') }}</div>
                                            </div>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div wire:ignore.self class="modal fade" id="formModalConsulting" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success py-3">
                    <h5 class="modal-title fw-bold text-white">{{ $isEditing ? 'Cập Nhật' : 'Thêm Mới' }} Hợp Đồng Tư Vấn</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Số hợp đồng (SHD AD)</label>
                                <input type="text" class="form-control form-control-sm" wire:model="formData.shd_ad">
                                @error('formData.shd_ad') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Khách hàng</label>
                                <select class="form-select form-select-sm" wire:model="formData.customer_id">
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach(\App\Models\Customer::all() as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('formData.customer_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Ngày ký HĐ</label>
                                <input type="date" class="form-control form-control-sm" wire:model="formData.signed_at">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Ngày HĐ về</label>
                                <input type="date" class="form-control form-control-sm" wire:model="formData.submitted_at">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Giá trị HĐ (VNĐ)</label>
                                <input type="number" class="form-control form-control-sm text-danger fw-bold" wire:model="formData.value">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Hoa hồng (VNĐ)</label>
                                <input type="number" class="form-control form-control-sm text-danger" wire:model="formData.commission">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Doanh số (VNĐ)</label>
                                <input type="number" class="form-control form-control-sm text-success fw-bold" wire:model="formData.revenue">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Tỉnh thành</label>
                                <input type="text" class="form-control form-control-sm" wire:model="formData.province" list="provinceList">
                                <datalist id="provinceList">
                                    @foreach($provinces as $p) <option value="{{ $p }}"> @endforeach
                                </datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Nguồn thông tin</label>
                                <select class="form-select form-select-sm" wire:model="formData.info_source">
                                    <option value="MỚI">MỚI</option>
                                    <option value="TÁI KÝ">TÁI KÝ</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">PT thanh toán</label>
                                <select class="form-select form-select-sm" wire:model="formData.payment_method">
                                    <option value="Sau ký">Sau ký</option>
                                    <option value="Trước ký">Trước ký</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Ghi chú</label>
                                <textarea class="form-control form-control-sm" rows="3" wire:model="formData.notes"></textarea>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-success btn-sm px-4">Lưu Lại</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openDetailModal', () => {
            let modal = new bootstrap.Modal(document.getElementById('detailModalConsulting'));
            modal.show();
        });
        window.addEventListener('openFormModal', () => {
            let modal = new bootstrap.Modal(document.getElementById('formModalConsulting'));
            modal.show();
        });
        window.addEventListener('closeFormModal', () => {
            let modalElement = document.getElementById('formModalConsulting');
            let modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) modal.hide();
        });
    </script>
    @endpush
</div>
