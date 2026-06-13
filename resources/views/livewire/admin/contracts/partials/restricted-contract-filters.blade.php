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
@if (auth()->user()->hasAnyRole([
    \App\Enums\Role::TU_VAN->value,
    \App\Enums\Role::KY_THUAT->value,
]))
    <div class="col-md-3 d-flex align-items-end pb-1">
        <label
            class="d-flex align-items-center gap-2 px-3 py-1 rounded border mb-0 {{ $filter['hide_completed_workflow'] ? 'border-primary text-primary bg-primary bg-opacity-10' : 'border-secondary text-muted' }} contract-text-12px cursor-pointer">
            <input class="form-check-input m-0" type="checkbox"
                wire:model.live="filter.hide_completed_workflow">
            Chưa hoàn thành
        </label>
    </div>
@endif
