@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/flow-map.css') }}?v={{ config('app.version') }}">
@endpush

<div class="flow-map-page">
    <div class="flow-map-toolbar">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('app.dashboard') }}">Bảng điều khiển</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sơ đồ luồng</li>
                </ol>
            </nav>
            <h1 class="flow-map-title">Sơ đồ luồng nghiệp vụ</h1>
            <p class="flow-map-subtitle">Nhìn nhanh các module đang nối với nhau như thế nào, từ đầu vào đến báo cáo cuối cùng.</p>
        </div>
        <div class="flow-map-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <span class="flow-map-btn-icon">↧</span>
                In / lưu PDF
            </button>
        </div>
    </div>

    <div class="flow-map-tabs" role="tablist" aria-label="Nhóm luồng nghiệp vụ">
        @foreach($maps as $key => $map)
            <button
                type="button"
                wire:click="setMap('{{ $key }}')"
                class="flow-map-tab {{ $activeMap === $key ? 'is-active' : '' }}"
            >
                <span class="flow-map-tab-icon">{{ strtoupper(mb_substr($map['label'], 0, 1)) }}</span>
                {{ $map['label'] }}
            </button>
        @endforeach
    </div>

    <div class="flow-map-legend">
        <span><i class="flow-line flow-line-primary"></i> Luồng chính</span>
        <span><i class="flow-line flow-line-muted"></i> Nhánh phụ / tham chiếu</span>
        <span><i class="flow-dot flow-dot-ledger"></i> Điểm ra báo cáo hoặc tài chính</span>
        <span>Click node để mở module tương ứng</span>
    </div>

    <section class="flow-map-board" wire:key="flow-map-{{ $activeMap }}">
        <header class="flow-map-board-header">
            <div class="flow-map-heading">
                <span class="flow-map-heading-icon">{{ strtoupper(mb_substr($current['label'], 0, 1)) }}</span>
                <div>
                    <h2>{{ $current['title'] }}</h2>
                    <p>{{ $current['summary'] }}</p>
                </div>
            </div>
            <div class="flow-map-metrics">
                @foreach($current['metrics'] as $metric)
                    <div class="flow-map-metric">
                        <span>{{ $metric['label'] }}</span>
                        <strong>{{ $metric['value'] }}</strong>
                    </div>
                @endforeach
            </div>
        </header>

        <div class="flow-map-scroll">
            <div class="flow-map-steps" style="--flow-step-count: {{ count($current['steps']) }}">
                @foreach($current['steps'] as $index => $step)
                    <div class="flow-map-step">
                        <div class="flow-map-phase">
                            <span>{{ $step['phase'] }}</span>
                            <strong>{{ $step['label'] }}</strong>
                        </div>

                        <div class="flow-map-node-list">
                            @foreach($step['nodes'] as $node)
                                @php
                                    $tag = $node['href'] ? 'a' : 'div';
                                @endphp
                                <{{ $tag }}
                                    @if($node['href']) href="{{ $node['href'] }}" wire:navigate @endif
                                    class="flow-map-node is-{{ $node['tone'] }} {{ $node['optional'] ? 'is-optional' : '' }}"
                                >
                                    <span class="flow-map-node-icon">{{ $node['optional'] ? '↳' : '◆' }}</span>
                                    <span class="flow-map-node-copy">
                                        <strong>{{ $node['title'] }}</strong>
                                        <small>{{ $node['subtitle'] }}</small>
                                    </span>
                                    @if($node['optional'])
                                        <em>Phụ</em>
                                    @endif
                                </{{ $tag }}>
                            @endforeach
                        </div>

                        @if(!$loop->last)
                            <span class="flow-map-connector" aria-hidden="true"></span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flow-map-ledger">
            <span class="flow-map-ledger-label">Chuỗi dữ liệu</span>
            @foreach($current['ledger'] as $item)
                <span class="flow-map-ledger-chip">{{ $item }}</span>
            @endforeach
        </div>

        <div class="flow-map-notes">
            @foreach($current['notes'] as $note)
                <div class="flow-map-note">
                    <span>i</span>
                    <p>{{ $note }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
