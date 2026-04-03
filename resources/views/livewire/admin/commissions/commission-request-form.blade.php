<div>
    <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">{{ $requestId ? 'Chỉnh sửa Yêu cầu chi hoa hồng' : 'Thêm mới Yêu cầu chi hoa hồng' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('app.commissions.index') }}">Quản lý Yêu cầu chi hoa hồng</a></li>
                    <li class="breadcrumb-item active">{{ $requestId ? 'Chỉnh sửa' : 'Thêm mới' }} Yêu cầu chi hoa hồng</li>
                </ol>
            </nav>
        </div>
        <div class="page-title-right d-flex gap-2">
            <button wire:click="save" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-save"></i> Lưu
            </button>
            <button wire:click="save(true)" class="btn btn-success d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-check"></i> Lưu tại trang
            </button>
            <button wire:click="$refresh" class="btn btn-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-clockwise"></i> Làm lại
            </button>
            <a href="{{ route('app.commissions.index') }}" class="btn btn-danger d-flex align-items-center gap-1">
                <i class="bi bi-x-lg"></i> Thoát
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Thông tin Yêu cầu chi hoa hồng</h5>
            <button class="btn btn-link p-0 text-muted"><i class="bi bi-dash"></i></button>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Loại hợp đồng: <span class="text-danger">*</span></label>
                    <select wire:model.live="contract_type" class="form-select @error('contract_type') is-invalid @enderror">
                        <option value="">Chọn loại hợp đồng</option>
                        @foreach($contractTypes as $class => $label)
                            <option value="{{ $class }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('contract_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Số hợp đồng BC: <span class="text-danger">*</span></label>
                    <select wire:model="contract_id" class="form-select @error('contract_id') is-invalid @enderror" @if(!$contract_type) disabled @endif>
                        <option value="">{{ $contract_type ? 'Chọn số hợp đồng' : 'Vui lòng chọn loại HĐ trước' }}</option>
                        @foreach($contracts as $contract)
                            <option value="{{ $contract->id }}">BC {{ $contract->shd_bc }} - {{ $contract->customer->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                    @error('contract_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Người nhận hoa hồng:</label>
                    <input type="text" wire:model="receiver_name" class="form-control @error('receiver_name') is-invalid @enderror" placeholder="Họ tên">
                    @error('receiver_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Số điện thoại</label>
                    <input type="text" wire:model="receiver_phone" class="form-control" placeholder="Số điện thoại">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Số tài khoản - Ngân hàng - Chi nhánh</label>
                    <input type="text" wire:model="bank_account" class="form-control" placeholder="Số tài khoản - Ngân hàng - Chi nhánh">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Số tiền hoa hồng</label>
                    <div class="input-group">
                        <input type="text" wire:model="amount" class="form-control money-input @error('amount') is-invalid @enderror" placeholder="Số tiền hoa hồng">
                        <span class="input-group-text bg-light fw-bold text-muted">VNĐ</span>
                    </div>
                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Khách hàng hoặc giới thiệu</label>
                    <div class="input-group">
                        <input type="text" wire:model="referrer_info" class="form-control" placeholder="Khách hàng hoặc giới thiệu">
                        <button class="btn btn-success"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Yêu cầu và lưu ý:</label>
                    <textarea wire:model="notes" class="form-control" rows="8" placeholder="Tình hình làm việc"></textarea>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light border-top p-3 d-flex gap-2">
            <button wire:click="save" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-save"></i> Lưu
            </button>
            <button wire:click="save(true)" class="btn btn-success d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark-check"></i> Lưu tại trang
            </button>
            <button wire:click="$refresh" class="btn btn-secondary d-flex align-items-center gap-1">
                <i class="bi bi-arrow-clockwise"></i> Làm lại
            </button>
            <a href="{{ route('app.commissions.index') }}" class="btn btn-danger d-flex align-items-center gap-1">
                <i class="bi bi-x-lg"></i> Thoát
            </a>
        </div>
    </div>
</div>
