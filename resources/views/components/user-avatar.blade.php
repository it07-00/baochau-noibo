@props([
    'user'  => null,
    'size'  => 36,
    'class' => '',
])
@php
    use Illuminate\Support\Str;
    $name     = $user?->name ?? '?';
    $initials = collect(explode(' ', $name))
        ->filter()
        ->map(fn($w) => Str::upper($w[0]))
        ->take(2)
        ->join('');
    $palette  = ['4f46e5','0891b2','059669','b45309','dc2626','7c3aed','0284c7','c026d3'];
    $bg       = $palette[abs(crc32($name)) % count($palette)];
    $fontSize = (int) round($size * 0.38);
@endphp

@if($user?->avatar)
    <img src="{{ asset('storage/' . $user->avatar) }}"
         alt="{{ $name }}"
         width="{{ $size }}" height="{{ $size }}"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
         class="rounded-circle {{ $class }}"
         style="object-fit:cover; width:{{ $size }}px; height:{{ $size }}px; flex-shrink:0;">
    <div class="rounded-circle d-none align-items-center justify-content-center fw-semibold text-white {{ $class }}"
         style="background:#{{ $bg }}; width:{{ $size }}px; height:{{ $size }}px; font-size:{{ $fontSize }}px; flex-shrink:0; letter-spacing:0.5px;">
        {{ $initials }}
    </div>
@else
    <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold text-white {{ $class }}"
         style="background:#{{ $bg }}; width:{{ $size }}px; height:{{ $size }}px; font-size:{{ $fontSize }}px; flex-shrink:0; letter-spacing:0.5px;">
        {{ $initials }}
    </div>
@endif
