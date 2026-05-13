<div class="col-md-3">
    <label class="{{ $labelClass ?? 'form-label fw-bold custom-filter-label' }}">Tỉnh thành</label>
    <select class="form-select form-control-xs" wire:model.live="filter.province">
        <option value="">{{ $provincePlaceholder ?? 'Chọn tỉnh thành' }}</option>
        @foreach ($provinces as $p)
            <option value="{{ $p }}">{{ $p }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3">
    <label class="{{ $labelClass ?? 'form-label fw-bold custom-filter-label' }}">Loại dịch vụ</label>
    <select class="form-select form-control-xs" wire:model.live="filter.loai_dich_vu">
        <option value="">{{ $servicePlaceholder ?? 'Chọn loại dịch vụ' }}</option>
        @foreach ($loai_dich_vu_options as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-3">
    <label class="{{ $labelClass ?? 'form-label fw-bold custom-filter-label' }}">Sắp xếp</label>
    <select class="form-select form-control-xs" wire:model.live="sortDirection">
        @if (($supports_report_number_sorting ?? false) && auth()->user()->hasRole(\App\Enums\Role::KY_THUAT->value))
            <option value="asc">Báo cáo số: tăng dần</option>
            <option value="desc">Báo cáo số: giảm dần</option>
        @else
            <option value="desc">Mới nhất trước</option>
            <option value="asc">Cũ nhất trước</option>
        @endif
    </select>
</div>
