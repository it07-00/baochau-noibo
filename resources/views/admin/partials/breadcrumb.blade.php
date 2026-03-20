@php
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

@if (!empty($breadcrumbs))
    <nav aria-label="breadcrumb" class="pt-2">
        <ol class="breadcrumb mb-0">
            @foreach ($breadcrumbs as $breadcrumb)
                @if (isset($breadcrumb['url']))
                    <li class="breadcrumb-item">
                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
                    </li>
                @else
                    <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['label'] }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
