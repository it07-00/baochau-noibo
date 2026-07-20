<div class="internal-doc-manager">
    <header class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-start gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white p-3 shadow-sm" aria-hidden="true"><i class="fa-solid fa-file-shield fs-4"></i></span>
            <div>
            <h1 class="h4 fw-bold text-body mb-1">Công văn nội bộ</h1>
            <p class="text-secondary-emphasis mb-2">Tra cứu quy định và tài liệu dùng chung theo phòng ban.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Công văn nội bộ</li>
                </ol>
            </nav>
            </div>
        </div>
        @can('internal-docs.create')
        <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#docModal" wire:click="resetFields" wire:loading.attr="disabled" wire:target="resetFields">
            <i class="fa-solid fa-plus me-1"></i> Thêm công văn
        </button>
        @endcan
    </header>

    <section class="card border shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="card-header bg-body p-3 p-lg-4 border-bottom">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-7">
                        <label for="internal-doc-search" class="form-label fw-semibold small">Tìm kiếm</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input id="internal-doc-search" type="search" class="form-control" placeholder="Tên công văn hoặc quy định..." wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label for="internal-doc-department" class="form-label fw-semibold small">Phòng ban</label>
                        <select id="internal-doc-department" class="form-select" wire:model.live="departmentFilter">
                            <option value="">Tất cả phòng ban</option>
                            <option value="company">Toàn công ty</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div wire:loading.flex wire:target="search,departmentFilter" class="align-items-center gap-2 px-4 py-2 border-bottom small text-primary" role="status"><span class="spinner-border spinner-border-sm"></span>Đang cập nhật...</div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center w-45px" >STT</th>
                            <th class="ps-4 w-50" >Thông tin quy định</th>
                            <th>Phòng ban</th>
                            <th class="w-35pct">Tập tin</th>
                            @canany(['internal-docs.edit', 'internal-docs.delete'])
                            <th class="text-end pe-4">Thao tác</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docs as $doc)
                        <tr>
                            <td class="text-center text-muted  fw-semibold w-45px" >{{ ($docs->currentPage() - 1) * $docs->perPage() + $loop->iteration }}</td>
                            <td class="ps-4">
                                <span class="fw-bold">{{ $doc->title }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-info">{{ $doc->department?->name ?? 'Toàn công ty' }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($doc->files)
                                        @foreach($doc->files as $file)
                                        <a href="{{ $file['resolved_url'] ?? ($file['url'] ?? '#') }}" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center gap-2 text-primary">
                                            <i class="fa-solid fa-download"></i>
                                            <small>{{ $file['name'] }}</small>
                                        </a>
                                        @endforeach
                                    @else
                                        <span class="text-secondary small">Không có tập tin</span>
                                    @endif
                                </div>
                            </td>
                            @canany(['internal-docs.edit', 'internal-docs.delete'])
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Thao tác công văn">
                                    @can('internal-docs.edit')
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $doc->id }})">
                                        <i class="fa-solid fa-pen me-1"></i>Sửa
                                    </button>
                                    @endcan
                                    @can('internal-docs.delete')
                                    <button class="btn btn-outline-danger" onclick="confirmDelete({{ $doc->id }})">
                                        <i class="fa-solid fa-trash me-1"></i>Xóa
                                    </button>
                                    @endcan
                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary"><i class="fa-solid fa-folder-open d-block fs-2 mb-2"></i>Không tìm thấy công văn phù hợp</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-top">
                {{ $docs->links('livewire.admin.users.pagination') }}
            </div>
        </div>
    </section>

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
                            <label class="form-label fw-bold">Phòng ban</label>
                            <select class="form-select @error('departmentId') is-invalid @enderror" wire:model="departmentId">
                                <option value="">Toàn công ty</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('departmentId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tập tin hiện tại</label>
                            <div class="list-group internal-doc-file-list">
                                @foreach($existingFiles as $index => $file)
                                <div class="list-group-item d-flex justify-content-between align-items-center internal-doc-file-item">
                                    <span class=" text-truncate mxw-80pct" >{{ $file['name'] }}</span>
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

                        <div wire:loading wire:target="newFiles" class="mt-2 text-primary  internal-doc-uploading">
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
