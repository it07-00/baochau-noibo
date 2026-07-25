@extends('admin.layouts.app')

@section('title', 'Sửa phòng ban')
@section('page_title', 'Chỉnh sửa: ' . $department->name)

@section('content')
    <div class="px-3 pt-3">
        <!-- Nút quay lại -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('app.departments.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Danh sách phòng ban
            </a>
        </div>

        <div class="row">
            <div class="col-12 col-md-8 col-xl-6 mx-auto">
                <div class="card bg-body border border-light-subtle shadow-sm rounded-3">
                    <div class="card-header bg-body-tertiary border-bottom border-light-subtle py-3 px-4">
                        <h5 class="fw-bold text-body m-0">
                            <i class="fa-solid fa-building me-2 text-primary"></i>Chỉnh sửa phòng ban: {{ $department->name }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('app.departments.update', $department) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tên phòng ban <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control form-control-sm rounded-3 @error('name') is-invalid @enderror" value="{{ old('name', $department->name) }}" required placeholder="Ví dụ: Phòng Tổng hợp">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Mã nhận diện (Slug) <span class="text-danger">*</span></label>
                                <input type="text" name="slug" id="slug" class="form-control form-control-sm rounded-3 @error('slug') is-invalid @enderror" value="{{ old('slug', $department->slug) }}" required placeholder="Ví dụ: tong-hop">
                                @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text small mt-1 text-muted">Định danh trên hệ thống. Dùng chữ thường không dấu, phân cách bằng dấu gạch ngang.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold d-block">Trạng thái hoạt động</label>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="is_active">Cho phép hoạt động</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light-subtle">
                                <a href="{{ route('app.departments.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary btn-sm rounded-3 px-4 shadow-sm">
                                    <i class="fa-solid fa-check me-1"></i> Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('name').addEventListener('input', function() {
            let slug = this.value.toLowerCase();
            slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
            slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
            slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
            slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
            slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
            slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
            slug = slug.replace(/đ/gi, 'd');
            slug = slug.replace(/\s+/g, '-');
            slug = slug.replace(/[^a-z0-9\-]/g, '');
            slug = slug.replace(/\-\-+/g, '-');
            slug = slug.replace(/^-+/, '');
            slug = slug.replace(/-+$/, '');
            document.getElementById('slug').value = slug;
        });
    </script>
    @endpush
@endsection
