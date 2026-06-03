@props([
    'user'  => null,
    'size'  => 36,
    'class' => '',
])
@inject('avatarData', 'App\Support\AvatarViewData')

@if($user?->avatar_url)
    <img src="{{ $user->avatar_url }}"
         alt="{{ $avatarData->name($user?->name) }}"
         width="{{ $size }}" height="{{ $size }}"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
         class="rounded-circle user-avatar-img {{ $class }}"
         style="--av-size:{{ $size }}px;">
    <div class="rounded-circle d-none align-items-center justify-content-center fw-semibold text-white user-avatar-init {{ $class }}"
         style="--av-size:{{ $size }}px; --av-bg:#{{ $avatarData->backgroundColor($user?->name) }}; --av-fs:{{ $avatarData->fontSize((int) $size) }}px;">
        {{ $avatarData->initials($user?->name) }}
    </div>
@else
    <div class="rounded-circle d-flex align-items-center justify-content-center fw-semibold text-white user-avatar-init {{ $class }}"
         style="--av-size:{{ $size }}px; --av-bg:#{{ $avatarData->backgroundColor($user?->name) }}; --av-fs:{{ $avatarData->fontSize((int) $size) }}px;">
        {{ $avatarData->initials($user?->name) }}
    </div>
@endif
