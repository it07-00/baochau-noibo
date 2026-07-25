@if($errors->any())
    <div class="alert alert-danger border shadow-sm" role="alert" data-validation-summary>
        <div class="fw-bold mb-2">
            <i class="fa-solid fa-circle-exclamation me-1"></i>
            Chưa thể lưu dữ liệu. Vui lòng kiểm tra:
        </div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
