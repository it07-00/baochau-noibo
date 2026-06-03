@extends('admin.layouts.app')

@section('title', 'Thêm phòng ban')
@section('page_title', 'Tạo phòng ban mới')

@section('content')
    <div class="row g-3 mt-1">
        <div class="col-12 col-md-8 col-xl-6 mx-auto">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header border-bottom">
                    <h5 class="pure-card-title m-0">Thông tin phòng ban</h5>
                </div>
                <div class="pure-card-body p-4">
                    <form action="{{ route('app.departments.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-medium">Tên phòng ban <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Ví dụ: Phòng Kinh Doanh">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Mã nhận diện (Slug) <span class="text-danger">*</span></label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" required placeholder="Ví dụ: phong-kinh-doanh">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text mt-2">Định danh trên hệ thống. Dùng chữ thường không dấu, phân cách bằng dấu gạch ngang.</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-medium">Trạng thái</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Hoạt động</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
                            <a href="{{ route('app.departments.index') }}" class="btn btn-light px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary px-4">Tạo mới</button>
                        </div>
                    </form>
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
