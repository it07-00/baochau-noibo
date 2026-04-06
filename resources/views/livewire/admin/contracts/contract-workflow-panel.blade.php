<div>
    {{-- Workflow 6 Bước --}}
    <div class="px-4 py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-diagram-3 me-1 text-primary"></i> Tiến độ xử lý hợp đồng
            </h5>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle" style="font-size: 0.85rem; padding: 6px 10px;">
                {{ count($completedSteps) }}/{{ count($stepKeys) }} bước
            </span>
        </div>

        {{-- Step indicator --}}
        <div class="d-flex align-items-start mb-4" style="gap: 0;">
            @foreach($stepKeys as $i => $key)
            @php
                $label     = $steps[$key];
                $isDone    = in_array($key, $completedSteps);
                $isFirst   = $i === 0;
                $canDo     = $canEdit && ($isFirst ? true : in_array($stepKeys[$i - 1], $completedSteps));
            @endphp
            <div class="d-flex align-items-center flex-grow-1">
                <div class="d-flex flex-column align-items-center flex-shrink-0" style="width: 76px;">
                    {{-- Circle --}}
                    @if($canDo && !$isDone)
                    <button
                        wire:click="openStep('{{ $key }}')"
                        class="btn rounded-circle d-flex align-items-center justify-content-center mb-2 btn-primary"
                        style="width: 48px; height: 48px; font-size: 1rem; padding: 0;"
                        title="{{ $label }}"
                    >
                        <span class="fw-bold">{{ $i + 1 }}</span>
                    </button>
                    @else
                    <div
                        class="rounded-circle d-flex align-items-center justify-content-center mb-2
                            {{ $isDone ? 'bg-success text-white' : 'bg-light text-muted border border-secondary' }}"
                        style="width: 48px; height: 48px; font-size: 1rem;"
                        title="{{ $label }}{{ $isDone ? ' ✓' : '' }}"
                    >
                        @if($isDone)
                            <i class="bi bi-check-lg fs-5"></i>
                        @else
                            <span class="fw-bold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    @endif
                    {{-- Label --}}
                    <span class="text-center" style="font-size: 0.72rem; line-height: 1.3; width: 76px; word-break: break-word;
                        color: {{ $isDone ? '#198754' : ($canDo ? '#0d6efd' : '#aaa') }}; font-weight: {{ $isDone || $canDo ? '600' : '400' }};">
                        {{ $label }}
                    </span>
                </div>
                {{-- Connector line --}}
                @if(!$loop->last)
                <div class="flex-grow-1" style="height: 2px; margin-top: -26px; min-width: 8px;
                    background: {{ $isDone ? '#198754' : '#dee2e6' }};"></div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Thông báo nếu không có quyền edit --}}
        @if(!$canEdit)
        <div class="text-muted px-1 pb-2">
            <i class="bi bi-info-circle me-1"></i>
            Chỉ nhân viên tư vấn và kỹ thuật được cập nhật tiến độ.
        </div>
        @endif

        {{-- Confirm panel khi bấm vào bước --}}
        @if($activeStep)
        <div wire:key="confirm-{{ $activeStep }}" class="card border border-primary shadow-sm mt-3">
            <div class="card-header bg-primary bg-opacity-10 py-2">
                <span class="fw-bold text-primary">
                    <i class="bi bi-upload me-1"></i>
                    Xác nhận bước: {{ $steps[$activeStep] }}
                </span>
            </div>
            <div class="card-body p-3">
                {{-- File upload --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        File đính kèm
                        @if($activeStep === 'receiving')
                            <small class="text-muted fw-normal">(Tùy chọn)</small>
                        @else
                            <span class="text-danger">*</span>
                        @endif
                        <small class="text-muted fw-normal">(PDF, Word, Excel, JPG, PNG — tối đa 20MB/file)</small>
                    </label>
                    <input wire:model="uploadFiles" type="file" class="form-control" multiple
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    @error('uploadFiles')
                        <div class="text-danger mt-1"><i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}</div>
                    @enderror
                    @error('uploadFiles.*')
                        <div class="text-danger mt-1"><i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}</div>
                    @enderror

                    {{-- Upload progress --}}
                    <div wire:loading wire:target="uploadFiles" class="text-muted mt-1">
                        <div class="spinner-border spinner-border-sm me-1"></div> Đang tải lên...
                    </div>

                    {{-- Preview danh sách file đã chọn --}}
                    @if(!empty($uploadFiles))
                    <div class="mt-2">
                        @foreach($uploadFiles as $f)
                        <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.8rem;">
                            <i class="bi bi-file-earmark text-primary"></i>
                            {{ $f->getClientOriginalName() }}
                            <span class="text-muted">({{ round($f->getSize() / 1024) }} KB)</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Comment --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Ghi chú (tùy chọn)</label>
                    <textarea wire:model="comment" class="form-control" rows="2"
                        placeholder="Nhập ghi chú cho bước này..."></textarea>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2">
                    <button wire:click="completeStep" wire:loading.attr="disabled" wire:target="completeStep"
                            class="btn btn-success">
                        <span wire:loading wire:target="completeStep" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-check-circle me-1"></i> Xác nhận hoàn thành
                    </button>
                    <button wire:click="cancelStep" class="btn btn-secondary">
                        <i class="bi bi-x me-1"></i> Hủy
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Danh sách file đã đính kèm --}}
        @if($filesByStep->count() > 0)
        <div class="mt-4">
            <h6 class="fw-bold text-muted mb-2"><i class="bi bi-paperclip me-1"></i> File đã đính kèm theo bước</h6>
            @foreach($stepKeys as $key)
                @if(isset($filesByStep[$key]))
                <div class="mb-3">
                    <span class="badge text-white mb-2 px-2 py-1" style="background:#0d6efd; font-size: 0.78rem; border-radius: 6px;">
                        <i class="bi bi-check-circle me-1"></i>{{ $steps[$key] }}
                    </span>
                    @foreach($filesByStep[$key] as $f)
                    <div class="d-flex align-items-center gap-2 ps-2 mb-1">
                        <i class="bi bi-file-earmark-arrow-down text-success" style="font-size: 1rem;"></i>
                                <a href="{{ $f->file_url }}" target="_blank"
                           class="fw-semibold text-decoration-none text-primary" style="font-size: 0.85rem;">
                            {{ $f->original_name ?: 'Xem tệp đính kèm' }}
                        </a>
                        <span class="text-muted" style="font-size: 0.8rem;">
                            — {{ $f->uploader?->name }} — {{ $f->created_at?->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>
</div>
