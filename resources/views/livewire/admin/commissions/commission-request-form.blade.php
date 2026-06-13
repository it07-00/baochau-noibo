<div>
    <div class="row g-4">
        <!-- Left Side: Forms (col-lg-8) -->
        <div class="col-lg-8">
            <div class="d-flex flex-column gap-4">
                
                <!-- Card 1: Recipient and Payment Details -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-primary fw-bold">
                            <i class="bi bi-person-fill"></i> Thông tin người nhận & tài khoản
                        </h5>
                        @if($savedAccounts->isNotEmpty())
                            <div class="d-flex align-items-center gap-2" style="max-width: 480px;">
                                <span class="text-muted text-nowrap d-inline-flex align-items-center gap-1 fw-bold" style="font-size: 0.95rem;">
                                    <i class="bi bi-clock-history text-primary"></i> Chọn nhanh:
                                </span>
                                <select wire:model.live="selectedSavedAccountId" class="form-select border-primary-subtle shadow-sm bg-white" style="border-radius: 6px; font-weight: 500; font-size: 0.95rem; padding-top: 0.35rem; padding-bottom: 0.35rem;">
                                    <option value="">-- Chọn tài khoản đã lưu --</option>
                                    @foreach($savedAccounts as $account)
                                        <option value="{{ $account->id }}">
                                            {{ $account->receiver_name }} ({{ $account->bank_number ?: Str::limit($account->bank_account, 10) }} - {{ $account->bank_code ?: 'Khác' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Recipient Name -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Người nhận hoa hồng <span class="text-danger">*</span></label>
                                <input type="text" 
                                       wire:model.live.debounce.500ms="receiver_name" 
                                       x-on:blur="$el.value = $el.value.toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/Đ/g, 'D').replace(/đ/g, 'd'); $wire.set('receiver_name', $el.value);"
                                       class="form-control @error('receiver_name') is-invalid @enderror" 
                                       placeholder="HỌ VÀ TÊN NGƯỜI NHẬN (KHÔNG DẤU)">
                                @error('receiver_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" wire:model="receiver_phone" class="form-control" placeholder="Số điện thoại">
                            </div>

                            <!-- Legacy Bank Account Info (Free Text) -->
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Ngân hàng khác (Không tạo QR)</label>
                                <input type="text" wire:model="bank_account" class="form-control" placeholder="Ví dụ: Techcombank - HN">
                            </div>

                            <!-- Bank Dropdown -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Ngân hàng</label>
                                <select wire:model.live="bank_code" class="form-select @error('bank_code') is-invalid @enderror">
                                    <option value="">-- Chọn ngân hàng --</option>
                                    @foreach($banks as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('bank_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Bank Account Number -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold">Số tài khoản nhận</label>
                                <input type="text" wire:model.live="bank_number" class="form-control @error('bank_number') is-invalid @enderror" placeholder="Số tài khoản">
                                @error('bank_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Contract, Amount, Notes -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-primary fw-bold">
                            <i class="bi bi-file-earmark-text-fill"></i> Thông tin Yêu cầu chi hoa hồng
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <!-- Contract Type -->
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

                            <!-- Contract BC Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Số hợp đồng BC: <span class="text-danger">*</span></label>
                                <select wire:model.live="contract_id"
                                        wire:key="contract-select-{{ $contract_type ?: 'none' }}"
                                        class="form-select @error('contract_id') is-invalid @enderror"
                                        @if(!$contract_type) disabled @endif>
                                    <option value="">
                                        @if(!$contract_type)
                                            Vui lòng chọn loại HĐ trước
                                        @elseif($contracts->isEmpty())
                                            Không có số HĐ BC trong loại đã chọn
                                        @else
                                            Chọn số hợp đồng
                                        @endif
                                    </option>
                                    @foreach($contracts as $contract)
                                        <option value="{{ $contract->id }}">BC {{ $contract->shd_bc }} - {{ $contract->customer->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                                @error('contract_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Số tiền hoa hồng</label>
                                <div class="input-group">
                                    <input type="text" wire:model.live.debounce.500ms="amount" class="form-control money-input @error('amount') is-invalid @enderror" placeholder="Số tiền hoa hồng">
                                    <span class="input-group-text bg-light fw-bold text-muted">VNĐ</span>
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Referrer info -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Khách hàng hoặc giới thiệu</label>
                                <div class="input-group">
                                    <input type="text" wire:model="referrer_info" class="form-control" placeholder="Khách hàng hoặc giới thiệu">
                                    <button class="btn btn-success"><i class="bi bi-plus-lg"></i></button>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label fw-bold">Yêu cầu và lưu ý:</label>
                                <textarea wire:model="notes" class="form-control" rows="4" placeholder="Tình hình làm việc"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form actions footer -->
                    <div class="card-footer bg-light border-top p-3 d-flex gap-2">
                        <button wire:click="save" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-save"></i> Lưu
                        </button>
                        <button wire:click="save(true)" class="btn btn-success d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-file-earmark-check"></i> Lưu tại trang
                        </button>
                        <button wire:click="$refresh" class="btn btn-secondary d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-arrow-clockwise"></i> Làm lại
                        </button>
                        <a href="{{ route('app.commissions.index') }}" class="btn btn-danger d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-x-lg"></i> Thoát
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Side: Sticky VietQR Preview Summary (col-lg-4) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm position-sticky" style="top: 24px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 d-flex align-items-center gap-2 text-primary fw-bold">
                        <i class="bi bi-qr-code-scan"></i> Thanh toán & QR Code
                    </h5>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    
                    <!-- QR Preview Box -->
                    <div class="w-100 p-3 bg-light rounded-3 border d-flex flex-column align-items-center justify-content-center text-center mb-4 shadow-inner" style="min-height: 420px;">
                        @if($bank_code && $bank_number)
                            <img src="{{ $this->getVietQrUrl() }}" class="img-thumbnail rounded border shadow-sm" style="width: 100%; max-width: 380px; height: auto; aspect-ratio: 1/1; object-fit: contain;" alt="QR Code">
                        @else
                            <div class="text-muted d-flex flex-column align-items-center py-4">
                                <i class="bi bi-qr-code text-secondary mb-3" style="font-size: 3.5rem; opacity: 0.4;"></i>
                                <span class="fw-semibold text-secondary">Chưa tạo mã QR</span>
                                <span class="text-muted small px-3 mt-1" style="font-size: 0.75rem;">Nhập Ngân hàng và Số tài khoản nhận ở cột bên để tự động tạo QR.</span>
                            </div>
                        @endif
                    </div>

                    <!-- Summary Recap Details -->
                    <div class="w-100 bg-light p-3 rounded-3 border border-light-subtle text-start">
                        <h6 class="text-secondary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-receipt-cutoff"></i> Tóm tắt thông tin</h6>
                        
                        <div class="d-flex flex-column gap-2" style="font-size: 1.1rem;">
                            <div class="row align-items-center">
                                <div class="col-5 text-muted">Người nhận:</div>
                                <div class="col-7 fw-bold text-dark text-truncate">{{ $receiver_name ?: '(Chưa nhập)' }}</div>
                            </div>
                            
                            <div class="row align-items-center">
                                <div class="col-5 text-muted">Ngân hàng:</div>
                                <div class="col-7 fw-bold text-dark font-monospace">{{ $bank_code ?: '(Chưa chọn)' }}</div>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-5 text-muted">Số tài khoản:</div>
                                <div class="col-7 fw-bold text-dark font-monospace">{{ $bank_number ?: '(Chưa nhập)' }}</div>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-5 text-muted">Hợp đồng BC:</div>
                                <div class="col-7 fw-bold text-primary">
                                    @if($contract_id && $contract_type)
                                        @php
                                            $selectedContract = $contracts->firstWhere('id', $contract_id);
                                        @endphp
                                        BC {{ $selectedContract->shd_bc ?? 'N/A' }}
                                    @else
                                        (Chưa chọn)
                                    @endif
                                </div>
                            </div>

                            <div class="row border-top pt-2 mt-2 align-items-center">
                                <div class="col-5 text-muted fw-bold">Số tiền:</div>
                                <div class="col-7 fw-bold text-danger fs-5">
                                    {{ $amount ? number_format((float) preg_replace('/\D+/', '', (string) $amount), 0, ',', '.') . ' đ' : '0 đ' }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
