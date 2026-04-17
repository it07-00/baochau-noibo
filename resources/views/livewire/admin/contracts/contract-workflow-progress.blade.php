<div class="px-4 py-3 border-top" style="background: #f8f9fa;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-bold text-muted ">
            <i class="bi bi-diagram-3 me-1 text-primary"></i> Tiến độ xử lý
        </span>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"
            style="font-size: 0.75rem;">
            {{ count($completedSteps) }}/{{ count($stepKeys) }} bước
        </span>
    </div>

    <div class="d-flex align-items-start" style="gap: 0;">
        @foreach ($stepKeys as $i => $key)
            @php
                $isDone = in_array($key, $completedSteps);
                $isCurrent = !$isDone && ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps));
            @endphp
            <div class="d-flex align-items-center flex-grow-1">
                <div class="d-flex flex-column align-items-center flex-shrink-0" style="width: 62px;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1
                    {{ $isDone ? 'bg-success text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-white text-muted border border-secondary') }}"
                        style="width: 34px; height: 34px; font-size: 0.85rem;">
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
                background: {{ $isDone ? '#198754' : '#dee2e6' }};">
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- File đính kèm theo bước --}}
    @if ($filesByStep->count() > 0)
        <div class="mt-3 pt-3 border-top">
            <div class="fw-bold text-muted mb-2" style="font-size: 0.8rem;">
                <i class="bi bi-paperclip me-1"></i> File đính kèm theo bước
            </div>
            @foreach ($stepKeys as $key)
                @if (isset($filesByStep[$key]))
                    <div class="mb-3">
                        <span class="badge text-white mb-2 px-2 py-1"
                            style="background:#0d6efd; font-size: 0.78rem; border-radius: 6px;">
                            <i class="bi bi-check-circle me-1"></i>{{ $steps[$key] }}
                        </span>
                        @foreach ($filesByStep[$key] as $f)
                            <div class="d-flex align-items-center gap-2 ps-2 mb-1">
                                <i class="bi bi-file-earmark-arrow-down text-success" style="font-size: 1rem;"></i>
                                <a href="{{ $f->file_url }}" target="_blank"
                                    class="fw-semibold text-decoration-none text-primary" style="font-size: 0.85rem;">
                                    {{ $f->original_name ?: 'Xem tệp đính kèm' }}
                                </a>
                                <span class="text-muted" style="font-size: 0.75rem;">
                                    — {{ $f->uploader?->name }} — {{ $f->created_at?->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
