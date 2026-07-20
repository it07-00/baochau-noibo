<div class="container-fluid py-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-0 fw-bold">Quản lý Chuyển phát thư</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}" class="text-decoration-none">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Chuyển phát thư</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm rounded-10px"  data-bs-toggle="modal" data-bs-target="#deliveryModal" wire:click="resetFields">
                <i class="fa-solid fa-plus-lg"></i>
                <span class="fw-bold">Thêm mới</span>
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border border-light-subtle shadow-sm mb-4 rounded-3 bg-body">
        <div class="card-header bg-body-tertiary border-bottom border-light-subtle py-3 px-4">
            <h6 class="fw-bold mb-0"><i class="fa-solid fa-filter me-2 text-primary"></i>Bộ lọc Chuyển phát thư</h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold  text-muted mb-2">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control bg-body-tertiary border-start-0" placeholder="Khách hàng, số bill, người gửi..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold  text-muted mb-2">Phòng ban</label>
                    <select class="form-select bg-body-tertiary border-light-subtle shadow-none" wire:model.live="departmentIdFilter">
                        <option value="">Tất cả phòng ban</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
                    <button class="btn btn-primary px-4 shadow-sm rounded-3" wire:click="$refresh" >
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Lọc
                    </button>
                    <button class="btn btn-outline-primary px-4 shadow-sm fw-bold d-flex align-items-center gap-2 rounded-3" >
                        <i class="fa-solid fa-file-text"></i> Quy trình
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- List Table -->
    <div class="card border border-light-subtle shadow-sm rounded-3 overflow-hidden bg-body">
        <div class="card-header bg-body-tertiary border-bottom border-light-subtle py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0">Danh sách Chuyển phát thư</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary border-bottom border-light-subtle">
                    <tr>
                        <th class="text-center w-45px" >STT</th>
                        <th class="ps-4 w-28pct" >Khách hàng</th>
                        <th class="text-center w-13pct" >Số bill Viettel Post</th>
                        <th class="text-center w-13pct" >Số bill 247</th>
                        <th class="text-center w-12pct" >Trạng thái VTP</th>
                        <th class="text-center">Ngày tạo đơn</th>
                        <th class="w-15pct">Nội dung</th>
                        @canany(['mail-delivery.edit', 'mail-delivery.delete'])
                        <th class="text-end pe-4">Thao tác</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td class="text-center text-muted  fw-semibold">{{ ($deliveries->currentPage() - 1) * $deliveries->perPage() + $loop->iteration }}</td>
                        <td class="ps-4 py-3">
                            <div class="d-flex flex-column">
                                <span class="fw-bold mb-1">{{ $delivery->customer_name }}</span>
                                @if($delivery->customer_phone)
                                    <span class="text-muted  mb-1"><i class="fa-solid fa-phone me-1"></i> {{ $delivery->customer_phone }}</span>
                                @endif
                                @if($delivery->address)
                                    <span class="text-muted  mb-1"><i class="fa-solid fa-location-dot me-1"></i> {{ $delivery->address }}</span>
                                @endif
                                <span><i class="fa-solid fa-user me-1"></i> Người gửi: <strong>{{ $delivery->sender_name }}</strong></span>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($delivery->bill_viettel)
                                <a href="https://viettelpost.com.vn/tra-cuu-hanh-trinh-don-hang?billCode={{ $delivery->bill_viettel }}" target="_blank" class="text-primary text-decoration-none fw-bold">
                                    {{ $delivery->bill_viettel }}
                                </a>
                            @else
                                <span class="text-muted ">--</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($delivery->bill_247)
                                <a href="https://247post.vn/track?bill={{ $delivery->bill_247 }}" target="_blank" class="text-primary text-decoration-none fw-bold">
                                    {{ $delivery->bill_247 }}
                                </a>
                            @else
                                <span class="text-muted ">--</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($delivery->vtp_status_name)
                                <span class="badge bg-{{ str_contains($delivery->vtp_status ?? '', '5') ? 'success' : (str_contains($delivery->vtp_status ?? '', '4') ? 'info' : (str_contains($delivery->vtp_status ?? '', '1') ? 'warning' : (($delivery->vtp_status ?? '') === 'created' ? 'primary' : 'secondary'))) }}-subtle text-{{ str_contains($delivery->vtp_status ?? '', '5') ? 'success' : (str_contains($delivery->vtp_status ?? '', '4') ? 'info' : (str_contains($delivery->vtp_status ?? '', '1') ? 'warning' : (($delivery->vtp_status ?? '') === 'created' ? 'primary' : 'secondary'))) }} px-3 py-2 rounded-pill ">
                                    {{ $delivery->vtp_status_name }}
                                </span>
                                @if($delivery->vtp_last_tracked_at)
                                    <div class="text-muted mt-1 fs-10px" >
                                        Cập nhật: {{ $delivery->vtp_last_tracked_at->format('H:i d/m') }}
                                    </div>
                                @endif
                            @else
                                <span class="text-muted ">--</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="text-muted ">{{ $delivery->created_at->format('d/m/Y') }}</span>
                        </td>
                        <td>
                            <div class="line-clamp-2 lh-base" >
                                {{ $delivery->content }}
                            </div>
                        </td>
                        @canany(['mail-delivery.edit', 'mail-delivery.delete'])
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1">
                                @can('mail-delivery.edit')
                                {{-- Nút tạo đơn VTP (nếu chưa có mã) --}}
                                @if(!$delivery->vtp_order_code && $delivery->bill_viettel == null)
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle text-warning shadow-none border"
                                        wire:click="createVtpForExisting({{ $delivery->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="createVtpForExisting({{ $delivery->id }})"
                                        title="Tạo đơn Viettel Post">
                                        <i class="fa-solid fa-truck"></i>
                                    </button>
                                @endif

                                {{-- Nút tracking VTP --}}
                                @if($delivery->vtp_order_code || $delivery->bill_viettel)
                                    <button class="btn btn-sm btn-icon btn-light rounded-circle text-info shadow-none border"
                                        wire:click="trackVtp({{ $delivery->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="trackVtp({{ $delivery->id }})"
                                        title="Tra cứu trạng thái VTP">
                                        <span wire:loading wire:target="trackVtp({{ $delivery->id }})">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                        </span>
                                        <span wire:loading.remove wire:target="trackVtp({{ $delivery->id }})">
                                            <i class="fa-solid fa-location-dot-fill"></i>
                                        </span>
                                    </button>
                                @endif
                                @endcan

                                <button class="btn btn-sm btn-icon btn-light rounded-circle text-success shadow-none border"
                                    onclick="copyAddress('{{ addslashes($delivery->address) }}')" title="Copy địa chỉ">
                                    <i class="fa-solid fa-clipboard"></i>
                                </button>

                                @can('mail-delivery.edit')
                                <button class="btn btn-sm btn-icon btn-light rounded-circle text-primary shadow-none border"
                                    wire:click="edit({{ $delivery->id }})" title="Sửa">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @endcan

                                @can('mail-delivery.delete')
                                <button class="btn btn-sm btn-icon btn-light rounded-circle text-danger shadow-none border"
                                    onclick="confirmDelete({{ $delivery->id }})" title="Xóa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                        @endcanany
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted italic">Không tìm thấy dữ liệu chuyển phát thư nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
        <div class="card-footer border-top py-3 px-4">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Thêm/Sửa -->
    <div wire:ignore.self class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form wire:submit.prevent="save">
                <div class="modal-content border-0 shadow-lg rounded-pill" >
                    <div class="modal-header border-0 pt-4 px-4 pb-0">
                        <h5 class="modal-title fw-bold">{{ $deliveryId ? 'Cập nhật Chuyển phát' : 'Thêm mới Chuyển phát' }}</h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold  text-muted mb-2">Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0 shadow-none @error('customer_name') is-invalid @enderror" wire:model="customer_name" placeholder="Ví dụ: Công ty TNHH Giải pháp SunTech">
                                @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold  text-muted mb-2">Số điện thoại @if($create_vtp_order)<span class="text-danger">*</span>@endif</label>
                                <input type="text" class="form-control bg-light border-0 shadow-none @error('customer_phone') is-invalid @enderror" wire:model="customer_phone" placeholder="Số điện thoại khách">
                                @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold  text-muted mb-2">Địa chỉ nhận @if($create_vtp_order)<span class="text-danger">*</span>@endif</label>
                                <input type="text" class="form-control bg-light border-0 shadow-none @error('address') is-invalid @enderror" wire:model="address" placeholder="Địa chỉ chi tiết nhận thư">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Tỉnh / Quận / Phường cho VTP --}}
                            <div class="col-md-4">
                                <label class="form-label fw-bold  text-muted mb-2">Tỉnh/Thành phố @if($create_vtp_order)<span class="text-danger">*</span>@endif</label>
                                <select class="form-select bg-light border-0 shadow-none @error('receiver_province') is-invalid @enderror" wire:model.live="receiver_province">
                                    <option value="">Chọn tỉnh/TP</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province['PROVINCE_ID'] }}">{{ $province['PROVINCE_NAME'] }}</option>
                                    @endforeach
                                </select>
                                @error('receiver_province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold  text-muted mb-2">Quận/Huyện @if($create_vtp_order)<span class="text-danger">*</span>@endif</label>
                                <select class="form-select bg-light border-0 shadow-none @error('receiver_district') is-invalid @enderror" wire:model.live="receiver_district">
                                    <option value="">Chọn quận/huyện</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district['DISTRICT_ID'] }}">{{ $district['DISTRICT_NAME'] }}</option>
                                    @endforeach
                                </select>
                                @error('receiver_district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold  text-muted mb-2">Phường/Xã @if($create_vtp_order)<span class="text-danger">*</span>@endif</label>
                                <select class="form-select bg-light border-0 shadow-none @error('receiver_ward') is-invalid @enderror" wire:model="receiver_ward">
                                    <option value="">Chọn phường/xã</option>
                                    @foreach($wards as $ward)
                                        <option value="{{ $ward['WARDS_ID'] }}">{{ $ward['WARDS_NAME'] }}</option>
                                    @endforeach
                                </select>
                                @error('receiver_ward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold  text-muted mb-2">Người gửi thực tế <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light border-0 shadow-none @error('sender_name') is-invalid @enderror" wire:model="sender_name" placeholder="Ví dụ: Hồ Thị Thanh Thảo">
                                @error('sender_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold  text-muted mb-2">Phòng ban liên quan <span class="text-danger">*</span></label>
                                <select class="form-select bg-light border-0 shadow-none @error('department_id') is-invalid @enderror" wire:model="department_id">
                                    <option value="">Chọn phòng ban</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold  text-muted mb-2">Số bill Viettel Post</label>
                                <input type="text" class="form-control bg-light border-0 shadow-none" wire:model="bill_viettel" placeholder="WQNHG..." {{ $create_vtp_order ? 'disabled' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold  text-muted mb-2">Số bill 247 Post</label>
                                <input type="text" class="form-control bg-light border-0 shadow-none" wire:model="bill_247" placeholder="Số bill 247 (nếu có)">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold  text-muted mb-2">Nội dung thư/bưu phẩm</label>
                                <textarea class="form-control bg-light border-0 shadow-none" rows="3" wire:model="content" placeholder="Ví dụ: 1 Hợp đồng chất thải, 05 chứng từ..."></textarea>
                            </div>

                            {{-- Viettel Post Options --}}
                            @if(!$deliveryId)
                            <div class="col-12">
                                <hr class="my-2">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="createVtpOrder" wire:model.live="create_vtp_order">
                                    <label class="form-check-label fw-bold text-danger" for="createVtpOrder">
                                        <i class="fa-solid fa-truck me-1"></i> Tạo đơn Viettel Post tự động
                                    </label>
                                </div>
                            </div>
                            @endif

                            @if($create_vtp_order)
                            <div class="col-md-4">
                                <label class="form-label fw-bold  text-muted mb-2">Dịch vụ VTP</label>
                                <select class="form-select bg-light border-0 shadow-none" wire:model="vtp_service">
                                    <option value="VCN">Chuyển phát nhanh (VCN)</option>
                                    <option value="VTK">Tiết kiệm (VTK)</option>
                                    <option value="V60">Hỏa tốc 60h (V60)</option>
                                    <option value="VVT">Vận chuyển (VVT)</option>
                                    <option value="PTN">Phát trong ngày (PTN)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold  text-muted mb-2">Trọng lượng (gram)</label>
                                <input type="number" class="form-control bg-light border-0 shadow-none" wire:model="vtp_weight" min="1" placeholder="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold  text-muted mb-2">Thu hộ (COD) VNĐ</label>
                                <input type="text" class="form-control bg-light border-0 shadow-none money-input" wire:model="vtp_money_collection" placeholder="0">
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 justify-content-end gap-2">
                        <button type="button" class="btn btn-light px-4 py-2 rounded-10px" data-bs-dismiss="modal" >Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm fw-bold rounded-10px"  wire:loading.attr="disabled">
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            </span>
                            <i class="fa-solid fa-floppy-disk me-2" wire:loading.remove wire:target="save"></i>
                            {{ $deliveryId ? 'Cập nhật ngay' : ($create_vtp_order ? 'Thêm & Tạo đơn VTP' : 'Thêm mới') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tracking VTP -->
    <div wire:ignore.self class="modal fade" id="trackingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-pill" >
                <div class="modal-header border-0 pt-4 px-4 pb-2">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-location-dot-fill text-danger me-2"></i>Tracking Viettel Post
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($trackingDelivery)
                    <div class="bg-light rounded-3 p-3 mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Mã vận đơn</small>
                                <div class="fw-bold text-primary">{{ $trackingDelivery->vtp_order_code ?? $trackingDelivery->bill_viettel }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Người nhận</small>
                                <div class="fw-bold">{{ $trackingDelivery->customer_name }}</div>
                            </div>
                        </div>
                        @if($trackingDelivery->vtp_total_fee)
                        <div class="mt-2">
                            <small class="text-muted">Phí vận chuyển:</small>
                            <span class="fw-bold text-danger">{{ number_format($trackingDelivery->vtp_total_fee) }} VNĐ</span>
                        </div>
                        @endif
                    </div>

                    @if(!empty($trackingData))
                    <div class="tracking-timeline">
                        @foreach($trackingData as $index => $item)
                        <div class="d-flex mb-3">
                            <div class="me-3 text-center mnw-40px" >
                                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $index === 0 ? 'bg-primary text-white' : 'bg-light text-muted' }} wh-32 fs-14px" >
                                    @if($index === 0)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        <i class="fa-solid fa-circle"></i>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                <div class="vr-30 {{ $index === 0 ? 'bg-primary' : 'bg-secondary' }} opacity-25" ></div>
                                @endif
                            </div>
                            <div class="flex-grow-1 pb-2">
                                <div class="fw-bold  {{ $index === 0 ? 'text-primary' : '' }}">
                                    {{ $item['STATUS_NAME'] ?? 'N/A' }}
                                </div>
                                <div class="text-muted ">
                                    {{ $item['NOTE'] ?? '' }}
                                </div>
                                <div class="text-muted fs-11px" >
                                    {{ $item['TIME'] ?? '' }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                        Chưa có dữ liệu tracking.
                    </div>
                    @endif
                    @endif
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-10px" data-bs-dismiss="modal" >Đóng</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openModal', () => {
            new bootstrap.Modal(document.getElementById('deliveryModal')).show();
        });

        window.addEventListener('closeModal', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deliveryModal'));
            if (modal) modal.hide();
        });

        window.addEventListener('openTrackingModal', () => {
            new bootstrap.Modal(document.getElementById('trackingModal')).show();
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Dữ liệu chuyển phát này sẽ bị xóa vĩnh viễn!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete', id);
                }
            })
        }

        function copyAddress(address) {
            if (!address) return;
            navigator.clipboard.writeText(address).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    icon: 'success',
                    title: 'Đã copy địa chỉ!'
                });
            });
        }
    </script>
    @endpush
</div>
