import glob, re

files = [
    'resources/views/livewire/admin/contracts/contract-commercial-manager.blade.php',
    'resources/views/livewire/admin/contracts/contract-energy-manager.blade.php',
    'resources/views/livewire/admin/contracts/contract-project-manager.blade.php',
    'resources/views/livewire/admin/contracts/contract-sustainability-manager.blade.php',
    'resources/views/livewire/admin/contracts/contract-waste-manager.blade.php',
]

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. Compact contract info column
    content = content.replace(
        '<td class="ps-4 py-4" style="min-width: 180px; max-width: 220px;">',
        '<td class="ps-3 py-2" style="min-width: 160px; max-width: 200px; font-size: 0.82rem;">'
    )

    # Replace "Số HĐ NTP:" with compact "NTP:"
    content = content.replace(
        '<span class="">Số HĐ NTP:<span\n                                            class="fw-bold">{{ $doc->shd_cxl }}</span></span>',
        '<span>NTP: <span class="fw-bold">{{ $doc->shd_cxl ?: \'-\' }}</span></span>'
    )
    content = content.replace(
        '<span class="">Số HĐ BC:<span\n                                            class="fw-bold">{{ $doc->shd_bc ?: \'-\' }}</span></span>',
        '<span>BC: <span class="fw-bold">{{ $doc->shd_bc ?: \'-\' }}</span></span>'
    )
    content = content.replace(
        '<span class="">Ngày ký hợp đồng:</span>\n                                    <span\n                                        class=" fw-bold">{{ $doc->signed_at ? $doc->signed_at->format(\'d/m/Y\') : \'-\' }}</span>',
        '<span>Ký: <span class="fw-bold">{{ $doc->signed_at ? $doc->signed_at->format(\'d/m/Y\') : \'-\' }}</span></span>'
    )
    content = content.replace(
        '<span class="">Nhân viên CS: <span\n                                            class="fw-bold">{{ $doc->staff?->name }}</span></span>',
        '<span>CS: <span class="fw-bold">{{ $doc->staff?->name }}</span></span>'
    )
    # Also handle variant with extra indentation (waste/project)
    content = content.replace(
        '<span class="">Số HĐ NTP:<span\n                                                class="fw-bold">{{ $doc->shd_cxl }}</span></span>',
        '<span>NTP: <span class="fw-bold">{{ $doc->shd_cxl ?: \'-\' }}</span></span>'
    )
    content = content.replace(
        '<span class="">Số HĐ BC:<span\n                                                class="fw-bold">{{ $doc->shd_bc ?: \'-\' }}</span></span>',
        '<span>BC: <span class="fw-bold">{{ $doc->shd_bc ?: \'-\' }}</span></span>'
    )
    content = content.replace(
        '<span class="">Ngày ký hợp đồng:</span>\n                                        <span\n                                            class=" fw-bold">{{ $doc->signed_at ? $doc->signed_at->format(\'d/m/Y\') : \'-\' }}</span>',
        '<span>Ký: <span class="fw-bold">{{ $doc->signed_at ? $doc->signed_at->format(\'d/m/Y\') : \'-\' }}</span></span>'
    )
    content = content.replace(
        '<span class="">Nhân viên CS: <span\n                                                class="fw-bold">{{ $doc->staff?->name }}</span></span>',
        '<span>CS: <span class="fw-bold">{{ $doc->staff?->name }}</span></span>'
    )
    
    # Replace d-flex flex-column with gap-0 variant
    content = content.replace(
        'py-4" style="min-width: 180px; max-width: 220px;">',
        'py-2" style="min-width: 160px; max-width: 200px; font-size: 0.82rem;">'
    )

    # 2. Compact customer column
    content = content.replace(
        '<td class="py-4" style="min-width: 180px; max-width: 260px;">',
        '<td class="py-2" style="min-width: 160px; max-width: 230px; font-size: 0.82rem;">'
    )
    # Simplify customer info - remove email, limit address
    content = content.replace(
        '{{ $doc->customer?->representative }} -\n                                        {{ $doc->customer?->phone }} - {{ $doc->customer?->email }}',
        '{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }}'
    )
    content = content.replace(
        '{{ $doc->customer?->representative }} -\n                                            {{ $doc->customer?->phone }} - {{ $doc->customer?->email }}',
        '{{ $doc->customer?->representative }} - {{ $doc->customer?->phone }}'
    )
    content = content.replace(
        '<span class=" text-muted">{{ $doc->customer?->address }}</span>',
        '<span class="text-muted" style="font-size: 0.78rem;">{{ Str::limit($doc->customer?->address, 50) }}</span>'
    )

    # 3. Assignment display - full names with assigner + progress bar
    # Replace truncated badge with full name + assigner
    old_assign = '''<div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @foreach ($doc->assignments->take(3) as $assign)
                                            <span class="badge bg-secondary" style="font-size:0.65rem;"
                                                title="{{ $assign->user?->name ?? $assign->external_assignee }}">{{ Str::limit($assign->user?->name ?? $assign->external_assignee ?? '?', 8) }}</span>
                                        @endforeach
                                        @if ($doc->assignments->count() > 3)
                                            <span class="badge bg-light text-dark"
                                                style="font-size:0.65rem;">+{{ $doc->assignments->count() - 3 }}</span>
                                        @endif
                                    </div>'''
    new_assign = '''<div class="d-flex flex-column gap-1 align-items-center">
                                        @foreach ($doc->assignments as $assign)
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge {{ $assign->user_id ? 'bg-primary' : 'bg-warning text-dark' }}" style="font-size:0.72rem;">
                                                    {{ $assign->user?->name ?? $assign->external_assignee ?? '?' }}
                                                </span>
                                                <small class="text-muted" style="font-size:0.6rem;">
                                                    bởi {{ Str::limit($assign->assigner?->name ?? '—', 15) }}
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>'''
    content = content.replace(old_assign, new_assign)
    
    # Also handle variant with different assignee display (waste uses slightly different pattern)
    old_assign2 = '''<div class="d-flex flex-wrap gap-1 justify-content-center">
                                            @foreach ($doc->assignments->take(3) as $assign)
                                                <span class="badge bg-secondary" style="font-size:0.65rem;"
                                                    title="{{ $assign->user?->name ?? $assign->external_assignee }}">{{ Str::limit($assign->user?->name ?? $assign->external_assignee ?? '?', 8) }}</span>
                                            @endforeach
                                            @if ($doc->assignments->count() > 3)
                                                <span class="badge bg-light text-dark"
                                                    style="font-size:0.65rem;">+{{ $doc->assignments->count() - 3 }}</span>
                                            @endif
                                        </div>'''
    new_assign2 = '''<div class="d-flex flex-column gap-1 align-items-center">
                                            @foreach ($doc->assignments as $assign)
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge {{ $assign->user_id ? 'bg-primary' : 'bg-warning text-dark' }}" style="font-size:0.72rem;">
                                                        {{ $assign->user?->name ?? $assign->external_assignee ?? '?' }}
                                                    </span>
                                                    <small class="text-muted" style="font-size:0.6rem;">
                                                        bởi {{ Str::limit($assign->assigner?->name ?? '—', 15) }}
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>'''
    content = content.replace(old_assign2, new_assign2)

    # 4. Replace progress text with progress bar
    old_progress = '''<div class="text-muted mt-1" style="font-size:0.7rem;">
                                    <span title="Tiến độ">{{ $completedSteps }}/{{ $totalSteps }}</span>
                                </div>'''
    new_progress = '''<div class="mt-2">
                                    <div class="progress" style="height: 6px; width: 80px; margin: 0 auto;">
                                        <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <span class="fw-semibold text-{{ $progressColor }}" style="font-size:0.72rem;">{{ $completedSteps }}/{{ $totalSteps }}</span>
                                </div>'''
    content = content.replace(old_progress, new_progress)
    
    old_progress2 = '''<div class="text-muted mt-1" style="font-size:0.7rem;">
                                        <span title="Tiến độ">{{ $completedSteps }}/{{ $totalSteps }}</span>
                                    </div>'''
    new_progress2 = '''<div class="mt-2">
                                        <div class="progress" style="height: 6px; width: 80px; margin: 0 auto;">
                                            <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                        <span class="fw-semibold text-{{ $progressColor }}" style="font-size:0.72rem;">{{ $completedSteps }}/{{ $totalSteps }}</span>
                                    </div>'''
    content = content.replace(old_progress2, new_progress2)

    # Add progress variables after $totalSteps = 6;
    content = content.replace(
        '$totalSteps = 6;\n                                @endphp',
        '$totalSteps = 6;\n                                    $progressPercent = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;\n                                    $progressColor = $progressPercent >= 100 ? \'success\' : ($progressPercent >= 50 ? \'primary\' : \'warning\');\n                                @endphp'
    )
    content = content.replace(
        '$totalSteps = 6;\n                                    @endphp',
        '$totalSteps = 6;\n                                        $progressPercent = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;\n                                        $progressColor = $progressPercent >= 100 ? \'success\' : ($progressPercent >= 50 ? \'primary\' : \'warning\');\n                                    @endphp'
    )

    # 5. Deadline color-coding
    old_deadline = '''$isOverdue = $deadline && $deadline->isPast() && !in_array($doc->status ?? '', ['Đã hoàn thành', 'Hợp đồng hủy', 'HOÀN THÀNH']);'''
    new_deadline = '''$isFinished = in_array($doc->status ?? '', ['Đã hoàn thành', 'Hợp đồng hủy', 'HOÀN THÀNH']);
                                    $isOverdue = $deadline && $deadline->isPast() && !$isFinished;
                                    $daysLeft = $deadline ? (int) now()->startOfDay()->diffInDays($deadline->startOfDay(), false) : null;
                                    $isNearDue = $deadline && !$isOverdue && !$isFinished && $daysLeft !== null && $daysLeft <= 3;'''
    content = content.replace(old_deadline, new_deadline)
    
    # Same but with different indentation
    old_deadline2 = '''$isOverdue = $deadline && $deadline->isPast() && !in_array($doc->status ?? '', ['Đã hoàn thành', 'Hợp đồng hủy', 'HOÀN THÀNH']);'''
    # Already replaced above

    # Replace simple deadline display with color-coded version
    old_deadline_display = '''@if($deadline)
                                    <span class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-body' }}" style="font-size:0.8rem;">
                                        {{ $deadline->format('d/m/Y') }}
                                    </span>
                                    @if($isOverdue)
                                        <br><span class="badge bg-danger" style="font-size:0.65rem;">Quá hạn</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif'''
    new_deadline_display = '''@if($deadline)
                                    @if($isFinished)
                                        <span class="fw-semibold text-success" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                        <br><span class="badge bg-success" style="font-size:0.6rem;"><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>
                                    @elseif($isOverdue)
                                        <span class="fw-bold text-danger" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                        <br><span class="badge bg-danger" style="font-size:0.6rem;"><i class="bi bi-exclamation-triangle me-1"></i>Quá hạn {{ abs($daysLeft) }} ngày</span>
                                    @elseif($isNearDue)
                                        <span class="fw-semibold text-warning" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                        <br><span class="badge bg-warning text-dark" style="font-size:0.6rem;"><i class="bi bi-clock me-1"></i>Còn {{ $daysLeft }} ngày</span>
                                    @else
                                        <span class="fw-semibold text-success" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                        <br><span class="badge bg-success bg-opacity-75" style="font-size:0.6rem;">Còn {{ $daysLeft }} ngày</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif'''
    content = content.replace(old_deadline_display, new_deadline_display)
    
    # Waste/project variant with different indentation
    old_deadline_display2 = '''@if($deadline)
                                        <span class="fw-semibold {{ $isOverdue ? 'text-danger' : 'text-body' }}" style="font-size:0.8rem;">
                                            {{ $deadline->format('d/m/Y') }}
                                        </span>
                                        @if($isOverdue)
                                            <br><span class="badge bg-danger" style="font-size:0.65rem;">Quá hạn</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif'''
    new_deadline_display2 = '''@if($deadline)
                                        @if($isFinished)
                                            <span class="fw-semibold text-success" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-success" style="font-size:0.6rem;"><i class="bi bi-check-circle me-1"></i>Hoàn thành</span>
                                        @elseif($isOverdue)
                                            <span class="fw-bold text-danger" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-danger" style="font-size:0.6rem;"><i class="bi bi-exclamation-triangle me-1"></i>Quá hạn {{ abs($daysLeft) }} ngày</span>
                                        @elseif($isNearDue)
                                            <span class="fw-semibold text-warning" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-warning text-dark" style="font-size:0.6rem;"><i class="bi bi-clock me-1"></i>Còn {{ $daysLeft }} ngày</span>
                                        @else
                                            <span class="fw-semibold text-success" style="font-size:0.8rem;">{{ $deadline->format('d/m/Y') }}</span>
                                            <br><span class="badge bg-success bg-opacity-75" style="font-size:0.6rem;">Còn {{ $daysLeft }} ngày</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif'''
    content = content.replace(old_deadline_display2, new_deadline_display2)

    with open(file, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f'Updated: {file}')
