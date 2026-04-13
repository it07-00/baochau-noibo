<div class="internal-doc-manager">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0">Danh sách Quy định</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active">Quy định</li>
                </ol>
            </nav>
        </div>
        @can('internal-docs.create')
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#docModal" wire:click="resetFields">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Thêm Quy định
        </button>
        @endcan
    </div>

    <div class="card border-0 shadow-sm internal-doc-card">
        <div class="card-body p-0">
            <div class="p-4 border-bottom">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group internal-doc-search">
                            <span class="input-group-text border-end-0">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0 internal-doc-search-input" placeholder="Tìm kiếm quy định..." wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 internal-doc-table">
                    <thead class="internal-doc-table-head">
                        <tr>
                            <th class="ps-4" style="width: 50%;">Thông tin quy định</th>
                            <th style="width: 35%;">Tập tin</th>
                            @canany(['internal-docs.edit', 'internal-docs.delete'])
                            <th class="text-end pe-4">Thao tác</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docs as $doc)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">{{ $doc->title }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($doc->files)
                                        @foreach($doc->files as $file)
                                        <a href="{{ $file['resolved_url'] ?? ($file['url'] ?? '#') }}" target="_blank" class="text-decoration-none d-flex align-items-center gap-2 text-primary internal-doc-file-link">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 15V3M12 15L8 11M12 15L16 11M2 17V19C2 20.1046 2.89543 21 4 21H20C21.1046 21 22 20.1046 22 19V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <small>{{ $file['name'] }}</small>
                                        </a>
                                        @endforeach
                                    @else
                                        <span class="text-muted small internal-doc-empty-file">Không có tập tin</span>
                                    @endif
                                </div>
                            </td>
                            @canany(['internal-docs.edit', 'internal-docs.delete'])
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    @can('internal-docs.edit')
                                    <button class="btn btn-sm btn-primary" wire:click="edit({{ $doc->id }})">
                                        Sửa
                                    </button>
                                    @endcan
                                    @can('internal-docs.delete')
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete({{ $doc->id }})">
                                        Xóa
                                    </button>
                                    @endcan
                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted internal-doc-empty-row">Không tìm thấy quy định nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-top">
                {{ $docs->links('livewire.admin.users.pagination') }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="docModal" tabindex="-1" aria-labelledby="docModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form wire:submit.prevent="save">
                <div class="modal-content border-0 internal-doc-modal-content">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title" id="docModalLabel">{{ $docId ? 'Cập nhật Quy định' : 'Thêm Quy định mới' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề quy định <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="Nhập tiêu đề...">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tập tin hiện tại</label>
                            <div class="list-group internal-doc-file-list">
                                @foreach($existingFiles as $index => $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center internal-doc-file-item">
                                    <span class="small text-truncate" style="max-width: 80%;">{{ $file['name'] }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" wire:click="removeExistingFile({{ $index }})">
                                        &times;
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold">Tải lên tập tin mới</label>
                            <input type="file" class="form-control @error('newFiles.*') is-invalid @enderror @error('newFiles') is-invalid @enderror" wire:model.live="newFiles" multiple>
                            <div class="form-text">Có thể chọn nhiều tệp (PDF, Word, Excel...).</div>
                            @error('newFiles.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @error('newFiles') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div wire:loading wire:target="newFiles" class="mt-2 text-primary small internal-doc-uploading">
                            Đang chuẩn bị tệp...
                        </div>
                    </div>
                    <div class="modal-footer border-top p-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">Lưu thay đổi</span>
                            <span wire:loading wire:target="save">Đang lưu...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        window.addEventListener('openModal', () => {
            new bootstrap.Modal(document.getElementById('docModal')).show();
        });

        window.addEventListener('closeModal', () => {
            bootstrap.Modal.getInstance(document.getElementById('docModal')).hide();
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn không thể hoàn tác sau khi xóa quy định này!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fe595c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete', id);
                }
            })
        }
    </script>
    @endpush
</div>
