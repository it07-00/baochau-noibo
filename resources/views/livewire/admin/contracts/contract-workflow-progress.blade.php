<div class="px-3 px-md-4 py-3 border-top bg-body-tertiary">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-bold text-body-secondary">
            <i class="fa-solid fa-sitemap me-1 text-primary"></i> Tiến độ xử lý
        </span>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle fs-75 text-nowrap">
            {{ count($completedSteps) }}/{{ count($stepKeys) }} bước
        </span>
    </div>

    {{-- Stepper ngang — desktop (md+) --}}
    <div class="d-none d-md-flex align-items-start gap-0">
        @foreach ($stepKeys as $i => $key)
            <div class="d-flex align-items-center flex-grow-1">
                <div class="d-flex flex-column align-items-center flex-shrink-0 w-62px">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-1
                        {{ in_array($key, $completedSteps) ? 'bg-success text-white' : (($i === 0 || in_array($stepKeys[$i - 1], $completedSteps)) ? 'bg-primary text-white' : 'bg-body-tertiary text-body-secondary border border-secondary-subtle') }} wh-34 fs-85">
                        @if (in_array($key, $completedSteps))
                            <i class="fa-solid fa-check"></i>
                        @else
                            <span class="fw-bold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <span class="text-center {{ in_array($key, $completedSteps) ? 'text-success' : (($i === 0 || in_array($stepKeys[$i - 1], $completedSteps)) ? 'text-primary' : 'text-body-secondary') }}"
                        style="font-size: 0.62rem; line-height: 1.2; width: 62px; word-break: break-word; font-weight: 600;">
                        {{ $steps[$key] }}
                    </span>
                </div>
                @if (!$loop->last)
                    <div class="flex-grow-1 {{ in_array($key, $completedSteps) ? 'bg-success' : 'bg-secondary-subtle' }}"
                        style="height: 2px; margin-top: -18px; min-width: 6px;"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Stepper dọc — mobile (< md) --}}
    <div class="d-md-none">
        @foreach ($stepKeys as $i => $key)
            <div class="d-flex align-items-stretch gap-3">
                <div class="d-flex flex-column align-items-center flex-shrink-0 w-36px">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                        {{ in_array($key, $completedSteps) ? 'bg-success text-white' : (($i === 0 || in_array($stepKeys[$i - 1], $completedSteps)) ? 'bg-primary text-white' : 'bg-body-tertiary text-body-secondary border border-secondary-subtle') }} wh-36 fs-82">
                        @if (in_array($key, $completedSteps))
                            <i class="fa-solid fa-check"></i>
                        @else
                            <span class="fw-bold">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    @if (!$loop->last)
                        <div class="flex-grow-1 my-1 {{ in_array($key, $completedSteps) ? 'bg-success' : 'bg-secondary-subtle' }}"
                            style="width: 2px; min-height: 16px;"></div>
                    @endif
                </div>
                <div class="pb-3 pt-1 flex-grow-1">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span class="{{ in_array($key, $completedSteps) ? 'text-success' : (($i === 0 || in_array($stepKeys[$i - 1], $completedSteps)) ? 'text-primary' : 'text-body-secondary') }}"
                            style="font-size: 0.85rem; font-weight: 600;">
                            {{ $steps[$key] }}
                        </span>
                        @if (in_array($key, $completedSteps))
                            <span class="badge bg-success-subtle text-success fs-68">Hoàn thành</span>
                        @elseif ($i === 0 || in_array($stepKeys[$i - 1], $completedSteps))
                            <span class="badge bg-primary-subtle text-primary fs-68">Hiện tại</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- File đính kèm theo bước --}}
    @if ($filesByStep->count() > 0)
        <div class="mt-3 pt-3 border-top">
            <div class="fw-bold text-body-secondary mb-2 fs-85">
                <i class="fa-solid fa-paperclip me-1"></i> File đính kèm theo bước
            </div>
            @foreach ($stepKeys as $key)
                @if (isset($filesByStep[$key]))
                    <div class="mb-3">
                        <span class="badge text-white mb-2 px-2 py-1 bg-primary fs-75 rounded-2">
                            <i class="fa-solid fa-circle-check me-1"></i>{{ $steps[$key] }}
                        </span>
                        @foreach ($filesByStep[$key] as $f)
                            <div class="ps-2 mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-file-arrow-down text-success flex-shrink-0"></i>
                                    <a href="{{ $f->file_url }}" target="_blank"
                                        class="fw-semibold text-decoration-none text-primary text-truncate fs-83">
                                        {{ $f->original_name ?: 'Xem tệp đính kèm' }}
                                    </a>
                                </div>
                                <div class="text-body-secondary ps-4 fs-75">
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
