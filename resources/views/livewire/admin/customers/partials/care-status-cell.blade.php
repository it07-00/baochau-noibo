@if($canEdit)
    <select class="form-select form-select-sm border-secondary-subtle py-1.5 px-2 text-truncate w-100"
            wire:change="updateCareStatus({{ $customer->id }}, $event.target.value)"
            title="Cập nhật trạng thái chăm sóc">
        @foreach($careStatusOptions as $opt)
            <option value="{{ $opt['value'] }}" @selected($customer->care_status?->value === $opt['value'])>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>
@else
    @php
        $cs = $customer->care_status;
        $csBadgeClass = $cs ? $cs->badgeClass() : 'bg-secondary bg-opacity-10 text-secondary';
        $csLabel = $cs ? $cs->label() : 'Chưa liên hệ';
    @endphp
    <span class="badge px-2.5 py-1.5 {{ $csBadgeClass }}" style="font-size: 0.75rem;">{{ $csLabel }}</span>
@endif

