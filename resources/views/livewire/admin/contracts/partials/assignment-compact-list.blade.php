@foreach ($assignments->groupBy('assigned_by') as $assignerAssignments)
    @php
        $firstAssignment = $assignerAssignments->first();
    @endphp
    <div class="d-flex flex-column align-items-center">
        @foreach ($assignerAssignments as $assign)
            <span class="badge {{ $assign->user_id ? 'bg-primary' : 'bg-warning text-dark' }} {{ $badgeClass ?? 'fs-72' }}">
                {{ $assign->user?->name ?? $assign->external_assignee ?? '?' }}
            </span>
        @endforeach
        <small class="text-muted {{ $metaClass ?? 'fs-60' }}">
            bởi {{ Str::limit($firstAssignment->assigner?->name ?? '—', 15) }}
        </small>
    </div>
@endforeach
