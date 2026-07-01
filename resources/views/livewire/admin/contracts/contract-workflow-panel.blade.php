<div>
    <div class="px-3 px-md-4 py-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="fw-bold mb-0 fs-6">
                <i class="fa-solid fa-sitemap me-1 text-primary"></i> Tiến độ xử lý hợp đồng
            </h5>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fs-82 px-2 py-1 text-nowrap"
                >
                {{ count($completedSteps) }}/{{ count($stepKeys) }} bước
            </span>
        </div>

        {{-- Stepper ngang — desktop (lg+) --}}
        <div class="d-none d-lg-block mb-4">
            <div class="d-flex align-items-start gap-0" >
                @foreach ($stepKeys as $i => $key)
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="d-flex flex-column align-items-center flex-shrink-0 w-80px" >
                            @if (($canEdit && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps))) && !in_array($key, $completedSteps))
                                <button wire:click="openStep('{{ $key }}')"
                                    class="btn rounded-circle d-flex align-items-center justify-content-center mb-2 btn-primary wh-48 fs-6 p-0"

                                    title="{{ $steps[$key] }}">
                                    <span class="fw-bold">{{ $i + 1 }}</span>
                                </button>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2
                                    {{ in_array($key, $completedSteps) ? 'bg-success text-white' : 'bg-light text-muted border border-secondary' }} wh-48 fs-6"

                                    title="{{ $steps[$key] }}{{ in_array($key, $completedSteps) ? ' ✓' : '' }}">
                                    @if (in_array($key, $completedSteps))
                                        <i class="fa-solid fa-check fs-5"></i>
                                    @else
                                        <span class="fw-bold">{{ $i + 1 }}</span>
                                    @endif
                                </div>
                            @endif
                            <span class="text-center"
                                style="font-size: 0.7rem; line-height: 1.2; width: 80px; word-break: break-word;
                                @if (in_array($key, $completedSteps)) color: #198754;
                                @elseif ($canEdit && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps))) color: #0d6efd;
                                @endif
                                font-weight: 600;">
                                {{ $steps[$key] }}
                            </span>
                        </div>
                        @if (!$loop->last)
                            <div class="flex-grow-1"
                                style="height: 2px; margin-top: -30px; min-width: 20px;
                                background: {{ in_array($key, $completedSteps) ? '#198754' : '#dee2e6' }};"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Stepper dọc — tablet/mobile (< lg) --}}
        <div class="d-lg-none mb-4">
            @foreach ($stepKeys as $i => $key)
                <div class="d-flex align-items-stretch gap-3">
                    {{-- Cột trái: circle + đường dọc --}}
                    <div class="d-flex flex-column align-items-center flex-shrink-0 w-44px" >
                        @if (($canEdit && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps))) && !in_array($key, $completedSteps))
                            <button wire:click="openStep('{{ $key }}')"
                                class="btn rounded-circle d-flex align-items-center justify-content-center btn-primary flex-shrink-0 wh-44 fs-90 p-0"

                                title="{{ $steps[$key] }}">
                                <span class="fw-bold">{{ $i + 1 }}</span>
                            </button>
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                                {{ in_array($key, $completedSteps) ? 'bg-success text-white' : 'bg-light text-muted border border-secondary' }} wh-44 fs-90"
                                >
                                @if (in_array($key, $completedSteps))
                                    <i class="fa-solid fa-check"></i>
                                @else
                                    <span class="fw-bold">{{ $i + 1 }}</span>
                                @endif
                            </div>
                        @endif
                        @if (!$loop->last)
                            <div class="flex-grow-1 my-1"
                                style="width: 2px; min-height: 20px;
                                background: {{ in_array($key, $completedSteps) ? '#198754' : '#dee2e6' }};"></div>
                        @endif
                    </div>
                    {{-- Cột phải: tên bước + badge trạng thái --}}
                    <div class="pb-3 pt-2 flex-grow-1">
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            <span style="font-size: 0.875rem;
                                font-weight: 600;
                                @if (in_array($key, $completedSteps)) color: #198754;
                                @elseif ($canEdit && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps))) color: #0d6efd;
                                @endif">
                                {{ $steps[$key] }}
                            </span>
                            @if (in_array($key, $completedSteps))
                                <span class="badge bg-success-subtle text-success fs-68" >Hoàn thành</span>
                            @elseif ($canEdit && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps)))
                                <span class="badge bg-primary-subtle text-primary fs-68" >Có thể làm</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Thông báo nếu không có quyền edit --}}
        @if (!$canEdit)
            <div class="alert alert-light border d-flex align-items-start gap-2 py-2 px-3 mb-3 fs-85" >
                <i class="fa-solid fa-circle-info text-muted mt-1 flex-shrink-0"></i>
                <span class="text-muted">Chỉ nhân viên tư vấn và kỹ thuật được cập nhật tiến độ.</span>
            </div>
        @endif

        {{-- Confirm panel khi bấm vào bước --}}
        @if ($activeStep)
            <div wire:key="confirm-{{ $activeStep }}" class="card border border-primary shadow-sm mt-3">
                <div class="card-header bg-primary bg-opacity-10 py-2 px-3">
                    <span class="fw-bold text-primary fs-90" >
                        <i class="fa-solid fa-upload me-1"></i>
                        Xác nhận bước: {{ $steps[$activeStep] }}
                    </span>
                </div>
                <div class="card-body p-3">
                    {{-- File upload --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1">
                            File đính kèm
                            @if ($activeStep === 'receiving' || (auth()->user()->hasRole('ky-thuat') && in_array($activeStep, ['survey', 'waiting_client', 'client_confirmed'])))
                                <small class="text-muted fw-normal">(Tùy chọn)</small>
                            @else
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <small class="d-block text-muted mb-2 fs-78" >PDF, Word, Excel, JPG, PNG — tối đa 200MB/file</small>
                        <input wire:model="uploadFiles" type="file" class="form-control form-control-sm" multiple
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        @error('uploadFiles')
                            <div class="text-danger mt-1 fs-82" ><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                        @enderror
                        @error('uploadFiles.*')
                            <div class="text-danger mt-1 fs-82" ><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $message }}</div>
                        @enderror
                        <div wire:loading wire:target="uploadFiles" class="text-muted mt-1 fs-82" >
                            <div class="spinner-border spinner-border-sm me-1"></div> Đang tải lên...
                        </div>
                        @if (!empty($uploadFiles))
                            <div class="mt-2">
                                @foreach ($uploadFiles as $f)
                                    <div class="d-flex align-items-center gap-1 text-muted fs-78" >
                                        <i class="fa-solid fa-file text-primary flex-shrink-0"></i>
                                        <span class="text-truncate">{{ $f->getClientOriginalName() }}</span>
                                        <span class="text-muted flex-shrink-0">({{ round($f->getSize() / 1024) }} KB)</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Comment --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold mb-1">Ghi chú <small class="text-muted fw-normal">(tùy chọn)</small></label>
                        <textarea wire:model="comment" class="form-control form-control-sm" rows="2"
                            placeholder="Nhập ghi chú cho bước này..."></textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button wire:click="completeStep" wire:loading.attr="disabled" wire:target="completeStep"
                            class="btn btn-success btn-sm">
                            <span wire:loading wire:target="completeStep"
                                class="spinner-border spinner-border-sm me-1"></span>
                            <i class="fa-solid fa-circle-check me-1"></i> Xác nhận hoàn thành
                        </button>
                        <button wire:click="cancelStep" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i> Hủy
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Danh sách file đã đính kèm --}}
        @if ($filesByStep->count() > 0)
            <div class="mt-4">
                <h6 class="fw-semibold text-muted mb-3 fs-85" >
                    <i class="fa-solid fa-paperclip me-1"></i> File đã đính kèm theo bước
                </h6>
                @foreach ($stepKeys as $key)
                    @if (isset($filesByStep[$key]))
                        <div class="mb-3">
                            <span class="badge text-white mb-2 px-2 py-1 bg-primary fs-75 rounded-2"
                                >
                                <i class="fa-solid fa-circle-check me-1"></i>{{ $steps[$key] }}
                            </span>
                            @foreach ($filesByStep[$key] as $f)
                                <div class="ps-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-file-arrow-down text-success flex-shrink-0"></i>
                                        <a href="{{ $f->file_url }}" target="_blank"
                                            class="fw-semibold text-decoration-none text-primary text-truncate fs-83"
                                            >
                                            {{ $f->original_name ?: 'Xem tệp đính kèm' }}
                                        </a>
                                    </div>
                                    <div class="text-muted ps-4 fs-75" >
                                        {{ $f->uploader?->name }} &mdash; {{ $f->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
