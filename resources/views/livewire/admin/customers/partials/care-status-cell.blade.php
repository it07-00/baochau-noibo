@if(auth()->user()->can('customers.edit'))
    <select class="form-select form-select-sm border-secondary-subtle py-1 px-2"
            wire:change="updateCareStatus({{ $customer->id }}, $event.target.value)"
            style="font-size: 0.82rem; max-width: 170px;"
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
    <span class="badge px-2 py-1 {{ $csBadgeClass }}" style="font-size: 0.75rem;">{{ $csLabel }}</span>
@endif
