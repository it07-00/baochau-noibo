<tr>
    <th class="bg-light">Phương thức thanh toán</th>
    <td>
        <div class="d-flex flex-wrap gap-1">
            @forelse(preg_split('/\s*\|\s*/', (string) $selectedDoc->payment_method, -1, PREG_SPLIT_NO_EMPTY) as $method)
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">{{ $method }}</span>
            @empty
                <span class="text-muted">—</span>
            @endforelse
        </div>
    </td>
</tr>
<tr>
    <th class="bg-light">% thanh toán</th>
    <td class="fw-semibold">{{ number_format((float) ($selectedDoc->payment_percentage ?? 100), 2, ',', '.') }}%</td>
</tr>
<tr>
    <th class="bg-light">Nội dung dịch vụ</th>
    <td class="text-pre-wrap">{{ $selectedDoc->service_content ?: '—' }}</td>
</tr>
<tr>
    <th class="bg-light">Nơi nộp</th>
    <td>{{ $selectedDoc->submission_place ?: '—' }}</td>
</tr>
