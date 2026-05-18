    @if(count($existingContractFiles) > 0)
        <div class="d-flex flex-column gap-2 mb-3">
            @foreach($existingContractFiles as $file)
                <div class="d-flex align-items-center gap-2 border rounded px-3 py-2">
                    <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                    <a href="{{ $file->file_url }}" target="_blank" class="text-truncate flex-grow-1 small text-danger fw-semibold text-decoration-none">
                        {{ $file->original_name }}
                    </a>
                    @if($this->canManageContractFiles)
                        <button type="button" class="btn btn-outline-danger py-0 px-2" wire:click="deleteContractFile({{ $file->id }})" wire:confirm="Xóa file này?">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif(count($newContractFiles) === 0)
        <p class="text-muted small mb-3">Chưa có file nào.</p>
    @endif

    @if(count($newContractFiles) > 0)
        <div class="d-flex flex-column gap-1 mb-3">
            <p class="small fw-semibold text-secondary mb-1">Sắp lưu ({{ count($newContractFiles) }} file):</p>
            @foreach($newContractFiles as $file)
                <div class="d-flex align-items-center gap-2 border border-primary rounded px-3 py-1 bg-light">
                    <i class="bi bi-file-earmark-pdf text-primary"></i>
                    <span class="small text-truncate flex-grow-1">{{ $file->getClientOriginalName() }}</span>
                </div>
            @endforeach
        </div>
    @endif

@if($this->canManageContractFiles)
    <label class="form-label fw-semibold small">Thêm file PDF</label>
    <input type="file" class="form-control" wire:model="newContractFiles" accept=".pdf" multiple>
    <div wire:loading wire:target="newContractFiles" class="text-primary mt-1 small">
        <span class="spinner-border spinner-border-sm me-1"></span> Đang tải...
    </div>
    <div class="form-text">Tối đa 50MB mỗi file. Có thể chọn nhiều file cùng lúc.</div>
    @error('newContractFiles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    @error('newContractFiles.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary" wire:click="uploadContractFile" wire:loading.attr="disabled" wire:target="uploadContractFile">
            <span wire:loading wire:target="uploadContractFile" class="spinner-border spinner-border-sm me-2"></span>
            Lưu file
        </button>
    </div>
@endif
