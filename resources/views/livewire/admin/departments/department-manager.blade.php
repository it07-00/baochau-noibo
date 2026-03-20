<div>
    @section('title', 'Quản lý Phòng ban')
    @section('page_title', 'Danh sách Phòng ban')

    @php
        $breadcrumbs = [
            ['label' => 'Quản trị', 'url' => route('app.dashboard')],
            ['label' => 'Phòng ban']
        ];
    @endphp

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="pure-card rounded-custom card-bg shadow-custom">
                <div class="pure-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h3 class="pure-card-title m-0">Danh sách phòng ban</h3>
                    
                    <div class="d-flex align-items-center gap-2">
                        <!-- Ô tìm kiếm realtime -->
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-transparent border-end-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0 ps-0" placeholder="Tìm kiếm phòng ban...">
                        </div>
                        
                        <a href="{{ route('app.departments.create') }}" class="btn btn-primary btn-sm" wire:navigate>Tạo mới</a>
                    </div>
                </div>
                
                <div class="pure-card-body pb-3 position-relative">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Tên phòng ban</th>
                                    <th>Slug</th>
                                    <th>Số nhân viên</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $dept)
                                <tr wire:key="dept-{{ $dept->id }}">
                                    <td>{{ $dept->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light-info text-info rounded me-3 d-flex align-items-center justify-content-center" style="width:36px; height:36px">
                                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $dept->name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code>{{ $dept->slug }}</code></td>
                                    <td>
                                        <span class="badge bg-label-primary px-2 py-1"><i class="fs-7 me-1 fas fa-users"></i> {{ $dept->users_count }} nhân viên</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch m-0 border-0">
                                            <input class="form-check-input" type="checkbox" wire:click="toggleActive({{ $dept->id }})" {{ $dept->is_active ? 'checked' : '' }} style="cursor:pointer">
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('app.departments.edit', $dept) }}" class="btn btn-sm btn-icon btn-light text-primary rounded-pill me-1" title="Sửa" wire:navigate>
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        
                                        <button 
                                            @if($dept->users_count > 0)
                                                onclick="Swal.fire('Không thể xóa', 'Phòng ban này đang có {{ $dept->users_count }} nhân viên.', 'error')"
                                            @else
                                                x-on:click="window.dispatchEvent(new CustomEvent('swal:confirm', { 
                                                    detail: [{ 
                                                        title: 'Xóa phòng ban?', 
                                                        message: 'Bạn có chắc chắn muốn xóa phòng ban {{ $dept->name }}?', 
                                                        component: '{{ $_instance->getId() }}', 
                                                        method: 'deleteDepartment', 
                                                        id: {{ $dept->id }} 
                                                    }] 
                                                }))"
                                            @endif
                                            class="btn btn-sm btn-icon btn-light text-danger rounded-pill" title="Xóa" {{ $dept->users_count > 0 ? 'disabled' : '' }}>
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Không tìm thấy phòng ban nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($departments->hasPages())
                <div class="pure-card-footer border-top px-4 py-3">
                    {{ $departments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
