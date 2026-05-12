<div class="px-3 px-md-4 py-3 border-top bg-light" >
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-bold text-muted">
            <i class="bi bi-diagram-3 me-1 text-primary"></i> Tiến độ xử lý
        </span>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fs-75 text-nowrap"
            >
            {{ count($completedSteps) }}/{{ count($stepKeys) }} bước
        </span>
    </div>

    {{-- Stepper ngang — desktop (md+) --}}
    <div class="d-none d-md-flex align-items-start gap-0" >
        @foreach ($stepKeys as $i => $key)
            @php
                $isDone = in_array($key, $completedSteps);
                $isCurrent = !$isDone && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps));
            @endphp
            <div class="d-flex align-items-center flex-grow-1">
                <div class="d-flex flex-column align-items-center flex-shrink-0 w-62px" >
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1
                        {{ $isDone ? 'bg-success text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-white text-muted border border-secondary') }} wh-34 fs-85"
                        >
                        @if ($isDone)
                            <i class="bi bi-check-lg"></i>
                        @else
                            <span class="fw-bold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <span class="text-center"
                        style="font-size: 0.62rem; line-height: 1.2; width: 62px; word-break: break-word;
                        color: {{ $isDone ? '#198754' : ($isCurrent ? '#0d6efd' : '#aaa') }};
                        font-weight: {{ $isDone || $isCurrent ? '600' : '400' }};">
                        {{ $steps[$key] }}
                    </span>
                </div>
                @if (!$loop->last)
                    <div class="flex-grow-1"
                        style="height: 2px; margin-top: -18px; min-width: 6px;
                        background: {{ $isDone ? '#198754' : '#dee2e6' }};"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Stepper dọc — mobile (< md) --}}
    <div class="d-md-none">
        @foreach ($stepKeys as $i => $key)
            @php
                $isDone = in_array($key, $completedSteps);
                $isCurrent = !$isDone && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps));
            @endphp
            <div class="d-flex align-items-stretch gap-3">
                <div class="d-flex flex-column align-items-center flex-shrink-0 w-36px" >
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                        {{ $isDone ? 'bg-success text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-white text-muted border border-secondary') }} wh-36 fs-82"
                        >
                        @if ($isDone)
                            <i class="bi bi-check-lg"></i>
                        @else
                            <span class="fw-bold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    @if (!$loop->last)
                        <div class="flex-grow-1 my-1"
                            style="width: 2px; min-height: 16px;
                            background: {{ $isDone ? '#198754' : '#dee2e6' }};"></div>
                    @endif
                </div>
                <div class="pb-3 pt-1 flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span style="font-size: 0.85rem;
                            font-weight: {{ $isDone || $isCurrent ? '600' : '400' }};
                            color: {{ $isDone ? '#198754' : ($isCurrent ? '#0d6efd' : '#aaa') }};">
                            {{ $steps[$key] }}
                        </span>
                        @if ($isDone)
                            <span class="badge bg-success-subtle text-success fs-68" >Hoàn thành</span>
                        @elseif ($isCurrent)
                            <span class="badge bg-primary-subtle text-primary fs-68" >Hiện tại</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- File đính kèm theo bước --}}
    @if ($filesByStep->count() > 0)
        <div class="mt-3 pt-3 border-top">
            <div class="fw-bold text-muted mb-2 fs-85" >
                <i class="bi bi-paperclip me-1"></i> File đính kèm theo bước
            </div>
            @foreach ($stepKeys as $key)
                @if (isset($filesByStep[$key]))
                    <div class="mb-3">
                        <span class="badge text-white mb-2 px-2 py-1 bg-primary fs-75 rounded-2"
                            >
                            <i class="bi bi-check-circle me-1"></i>{{ $steps[$key] }}
                        </span>
                        @foreach ($filesByStep[$key] as $f)
                            <div class="ps-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-arrow-down text-success flex-shrink-0"></i>
                                    <a href="{{ $f->file_url }}" target="_blank"
                                        class="fw-semibold text-decoration-none text-primary text-truncate fs-83"
                                        >
                                        {{ $f->original_name ?: 'Xem tệp đính kèm' }}
                                    </a>
                                </div>
                                <div class="text-muted ps-4 fs-75" >
                                    {{ $f->uploader?->name }} &mdash; {{ $f->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
