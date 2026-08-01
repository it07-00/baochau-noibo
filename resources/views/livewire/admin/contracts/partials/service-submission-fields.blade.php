<div class="col-12">
    <label class="form-label small fw-semibold mb-2">Nội dung dịch vụ</label>
    <div class="p-3 bg-body-tertiary border rounded-3">
        <div class="row g-2">
            @foreach($this::PRESET_SERVICES as $presetService)
                <div class="col-md-4 col-sm-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               value="{{ $presetService }}"
                               id="contract_service_preset_{{ $loop->index }}"
                               wire:model.live="selectedServices">
                        <label class="form-check-label small user-select-none" for="contract_service_preset_{{ $loop->index }}">
                            {{ $presetService }}
                        </label>
                    </div>
                </div>
            @endforeach
            <div class="col-md-4 col-sm-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           id="contract_service_preset_custom"
                           wire:model.live="hasCustomService">
                    <label class="form-check-label small user-select-none fw-semibold text-primary" for="contract_service_preset_custom">
                        <i class="fa-solid fa-pen me-1" aria-hidden="true"></i> Tự nhập dịch vụ khác
                    </label>
                </div>
            </div>
        </div>

        @if($hasCustomService)
            <div class="mt-2 pt-2 border-top">
                <label class="form-label small text-muted mb-1" for="contract_custom_service_input">Nội dung dịch vụ khác / bổ sung</label>
                <input type="text" id="contract_custom_service_input" class="form-control form-control-sm"
                       wire:model.defer="customServiceText"
                       placeholder="Nhập tên dịch vụ tùy chỉnh (phân cách bằng dấu phẩy nếu chọn nhiều)...">
            </div>
        @endif
    </div>
</div>
<div class="col-12">
    <label class="form-label small fw-semibold">Nơi nộp</label>
    <textarea class="form-control" rows="2" wire:model="formData.submission_place" placeholder="Nơi nộp hồ sơ, cơ quan tiếp nhận..."></textarea>
</div>
