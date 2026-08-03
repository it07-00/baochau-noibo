<div class="customer-directory">
    <?php $__env->startSection('title', 'Quản lý khách hàng'); ?>
    <?php $__env->startSection('page_title', 'Danh sách khách hàng'); ?>

    <?php $__env->startPush('styles'); ?>
    <style>
        .more-services summary {
            width: fit-content;
            color: var(--bs-primary);
            font-size: .74rem;
            cursor: pointer;
            list-style: none;
        }
        .more-services summary::-webkit-details-marker { display: none; }
        .customer-loading {
            height: 3px;
            overflow: hidden;
            background: var(--bs-primary-bg-subtle);
        }
        .customer-loading::after {
            content: "";
            display: block;
            width: 45%;
            height: 100%;
            background: var(--bs-primary);
            animation: customer-loading 1s ease-in-out infinite;
        }
        @keyframes customer-loading {
            from { transform: translateX(-110%); }
            to { transform: translateX(245%); }
        }
        @media (prefers-reduced-motion: reduce) {
            .customer-loading::after { animation: none; width: 100%; }
        }
    </style>
    <?php $__env->stopPush(); ?>

    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-between gap-3 mt-2 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1 text-body" style="letter-spacing: -0.025em;">Danh sách khách hàng</h2>
            <p class="text-muted mb-0 small" style="max-width: 680px;">
                Theo dõi khách hàng theo tỉnh/thành, phường/xã, khu công nghiệp và hiệu suất báo giá – hợp đồng.
            </p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList === 'all'): ?>
        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.edit')): ?>
            <button type="button" class="btn btn-outline-secondary rounded-8px btn-mobile-touch"
                    wire:click="previewLegacyNormalization" wire:loading.attr="disabled"
                    wire:target="previewLegacyNormalization">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                Chuẩn hóa dữ liệu cũ
            </button>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.create')): ?>
            <button type="button" class="btn btn-primary rounded-8px btn-mobile-touch" wire:click="openCreate">
                <i class="fa-solid fa-plus me-1"></i>Thêm khách hàng
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <nav class="card border border-light-subtle shadow-sm rounded-3 bg-body mb-4" aria-label="Nhóm danh sách khách hàng">
        <div class="nav nav-pills flex-nowrap gap-2 overflow-x-auto p-2">
            <button type="button"
                    class="nav-link text-nowrap <?php echo e($customerList === 'all' ? 'active' : 'text-body'); ?>"
                    wire:click="selectCustomerList('all')">
                <i class="fa-solid fa-users me-1"></i>Khách hàng
            </button>
            <button type="button"
                    class="nav-link text-nowrap <?php echo e($customerList === 'ghg_inventory' ? 'active' : 'text-body'); ?>"
                    wire:click="selectCustomerList('ghg_inventory')">
                <i class="fa-solid fa-cloud me-1"></i>KH KKKNK
            </button>
            <button type="button"
                    class="nav-link text-nowrap <?php echo e($customerList === 'energy_audit' ? 'active' : 'text-body'); ?>"
                    wire:click="selectCustomerList('energy_audit')">
                <i class="fa-solid fa-bolt me-1"></i>KH KIỂM TOÁN NĂNG LƯỢNG
            </button>
        </div>
    </nav>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body"><?php echo e(number_format($summary['customers'])); ?></div>
                        <div class="small text-muted">Khách hàng</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body"><?php echo e(number_format($summary['groups'])); ?></div>
                        <div class="small text-muted">
                            <?php echo e(match($groupBy) { 'ward' => 'Phường/xã', 'industrial_park' => 'KCN', default => 'Tỉnh/thành' }); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body"><?php echo e(number_format($summary['quotations'])); ?></div>
                        <div class="small text-muted">Báo giá đã ra</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border border-light-subtle shadow-sm rounded-3 h-100 bg-body">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle" style="width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <div class="h4 fw-bold mb-0 text-body"><?php echo e(number_format($summary['contracts'])); ?></div>
                        <div class="small text-muted">Hợp đồng</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <div class="card border border-secondary-subtle bg-body shadow-sm rounded-3 mb-4"
         x-data="{ showAdvanced: <?php echo json_encode($hasAdvancedFilters, 15, 512) ?> }">
        <div class="card-body p-3 p-md-4">
            
            <div class="row g-2.5 g-md-3 align-items-end">
                <div class="col-12 col-md-4 col-xl-4">
                    <label for="customer-search" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary border-secondary-subtle">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="customer-search" type="search"
                               class="form-control border-start-0 ps-0 border-secondary-subtle"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Tên, MST, người đại diện, địa chỉ...">
                    </div>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <label for="province-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tỉnh / thành</label>
                    <select id="province-filter" class="form-select border-secondary-subtle" wire:model.live="provinceFilter">
                        <option value="">Tất cả tỉnh/thành</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $filterProvinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($province); ?>"><?php echo e($province); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-xl-3">
                    <label for="industrial-park-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Khu công nghiệp</label>
                    <select id="industrial-park-filter" class="form-select border-secondary-subtle" wire:model.live="industrialParkFilter">
                        <option value="">Tất cả KCN</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $industrialParks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $industrialPark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($industrialPark); ?>"><?php echo e($industrialPark); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                <div class="col-12 col-xl-2">
                    <div class="d-flex gap-2">
                        <button type="button"
                                class="btn btn-outline-secondary border-secondary-subtle d-inline-flex align-items-center justify-content-center gap-1 text-nowrap flex-grow-1"
                                :class="{ 'active bg-secondary bg-opacity-10 text-primary': showAdvanced }"
                                @click="showAdvanced = !showAdvanced"
                                title="Bộ lọc nâng cao">
                            <i class="fa-solid fa-filter"></i>
                            <span>Lọc</span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasAdvancedFilters): ?>
                                <span class="badge bg-primary rounded-circle p-1 ms-1"></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                        <button type="button" class="btn btn-outline-primary border-secondary-subtle text-nowrap px-3"
                                wire:click="resetFilters" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
            </div>

            
            <div x-show="showAdvanced" x-collapse x-cloak class="pt-3 mt-3 border-top border-light-subtle">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="caretaker-status-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Trạng thái phân công</label>
                        <select id="caretaker-status-filter" class="form-select border-secondary-subtle" wire:model.live="caretakerStatusFilter">
                            <option value="">Tất cả</option>
                            <option value="assigned">Đã phân công NVKD</option>
                            <option value="unassigned">Chưa phân công NVKD</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList === 'all'): ?>
                                <option value="has_quotation">Đã có báo giá</option>
                                <option value="has_contract">Đã có hợp đồng</option>
                                <option value="no_service">Chưa phát sinh dịch vụ</option>
                            <?php else: ?>
                                <option value="has_contact">Đã có thông tin liên hệ</option>
                                <option value="no_contact">Chưa có thông tin liên hệ</option>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList !== 'all'): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="care-status-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Trạng thái chăm sóc</label>
                        <select id="care-status-filter" class="form-select border-secondary-subtle" wire:model.live="careStatusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $careStatusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($opt['value']); ?>"><?php echo e($opt['label']); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="ward-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Phường / xã</label>
                        <select id="ward-filter" class="form-select border-secondary-subtle" wire:model.live="wardFilter">
                            <option value="">Tất cả phường/xã</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $wards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($ward); ?>"><?php echo e($ward); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="group-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nhóm danh sách theo</label>
                        <select id="group-filter" class="form-select border-secondary-subtle" wire:model.live="groupBy">
                            <option value="province">Tỉnh / thành</option>
                            <option value="ward">Phường / xã</option>
                            <option value="industrial_park">Khu công nghiệp</option>
                            <option value="none">Không nhóm</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="staff-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nhân viên phụ trách</label>
                        <select id="staff-filter" class="form-select border-secondary-subtle" wire:model.live="staffFilter">
                            <option value="">Tất cả nhân viên</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $staffOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($staff->id); ?>"><?php echo e($staff->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Dịch vụ báo giá</label>
                        <div class="dropdown" x-data="{ open: false }" @click.outside="open = false">
                            <button class="form-select text-start d-flex justify-content-between align-items-center dropdown-toggle border-secondary-subtle" type="button" @click="open = !open">
                                <span class="text-truncate me-2">
                                    <?php
                                        $selectedQuotationList = is_array($serviceQuotationFilter)
                                            ? $serviceQuotationFilter
                                            : (empty($serviceQuotationFilter) ? [] : [$serviceQuotationFilter]);
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($selectedQuotationList)): ?>
                                        Tất cả dịch vụ báo giá
                                    <?php elseif(count($selectedQuotationList) === 1): ?>
                                        <?php echo e($selectedQuotationList[0]); ?>

                                    <?php else: ?>
                                        <?php echo e(count($selectedQuotationList)); ?> dịch vụ được chọn
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </button>
                            <div class="dropdown-menu w-100 p-2 shadow-sm border border-secondary-subtle" :class="{ 'show': open }" style="max-height: 250px; overflow-y: auto; margin-top: 2px; z-index: 1050;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $serviceQuotationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="form-check py-1">
                                        <input class="form-check-input" type="checkbox" value="<?php echo e($service); ?>" id="service-quote-<?php echo e($index); ?>" wire:model.live="serviceQuotationFilter">
                                        <label class="form-check-label text-body w-100 cursor-pointer" for="service-quote-<?php echo e($index); ?>">
                                            <?php echo e($service); ?>

                                        </label>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="service-contract-filter" class="form-label text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Dịch vụ hợp đồng</label>
                        <select id="service-contract-filter" class="form-select border-secondary-subtle" wire:model.live="serviceContractFilter">
                            <option value="">Tất cả dịch vụ hợp đồng</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $serviceContractOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($service); ?>"><?php echo e($service); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div wire:loading class="customer-loading" wire:target="search,provinceFilter,wardFilter,industrialParkFilter,staffFilter,serviceQuotationFilter,serviceContractFilter,groupBy,resetFilters"></div>
    </div>

    <div class="card border border-light-subtle shadow-sm overflow-hidden rounded-3 bg-body">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-tertiary text-uppercase text-secondary" style="font-size: 0.75rem; border-bottom: 1px solid var(--bs-border-color-translucent);">
                    <tr>
                        <th class="text-center px-3 py-3 text-nowrap" style="width: 80px">STT</th>
                        <th class="px-4 py-3 text-nowrap">Khách hàng</th>
                        <th class="px-4 py-3 text-nowrap">Số điện thoại</th>
                        <th class="px-4 py-3 text-nowrap">Email</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList !== 'all'): ?>
                        <th class="px-4 py-3 text-nowrap">Người chăm sóc</th>
                        <th class="px-4 py-3 text-nowrap">Trạng thái CS</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <th class="px-4 py-3 text-nowrap">Khu vực</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList === 'all'): ?>
                        <th class="px-4 py-3 text-nowrap">Dịch vụ &amp; hiệu suất</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['customers.edit', 'customers.delete'])): ?>
                        <th class="text-end pe-4 py-3 text-nowrap" style="width: 120px;">Thao tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="border-0">
                    <?php
                        $currentGroup = null;
                        $hasActions = auth()->user()->canAny(['customers.edit', 'customers.delete']);
                        // all: STT, KH, phone, email, khu vực, dịch vụ, [actions] = 6+1 = 7(6)
                        // regulatory: STT, KH, phone, email, caretaker, care_status, khu vực, [actions] = 7+1 = 8(7)
                        $columnCount = ($customerList !== 'all' ? 8 : 7) - ($hasActions ? 0 : 1);
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupBy !== 'none' && $currentGroup !== $this->groupValue($customer)): ?>
                            <?php ($currentGroup = $this->groupValue($customer)); ?>
                            <tr class="bg-body-tertiary border-top border-bottom border-light-subtle">
                                <td colspan="<?php echo e($columnCount); ?>" class="px-4 py-2.5">
                                    <div class="d-flex align-items-center gap-2 fw-bold text-primary cursor-pointer"
                                         wire:click="filterByGroup('<?php echo e(addslashes($currentGroup)); ?>')"
                                         title="Lọc theo <?php echo e($currentGroup); ?>">
                                        <i class="fa-solid <?php echo e($groupBy === 'industrial_park' ? 'fa-industry' : 'fa-location-dot'); ?>"></i>
                                        <span><?php echo e($currentGroup); ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php ($breakdown = $customerList === 'all' ? $this->serviceBreakdown($customer) : []); ?>
                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'customer-'.e($customer->id).''; ?>wire:key="customer-<?php echo e($customer->id); ?>" style="border-bottom: 1px solid var(--bs-border-color-translucent);">
                            <td class="text-center text-muted fw-semibold ps-4">
                                <?php echo e(($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration); ?>

                            </td>
                            <td class="px-4">
                                <div style="min-width: 220px; max-width: 320px; white-space: normal; line-height: 1.4;">
                                    <a href="<?php echo e(route('app.customers.contracts', $customer)); ?>"
                                       class="fw-bold text-body text-decoration-none link-primary">
                                        <?php echo e($customer->name); ?>

                                    </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->tax_code): ?>
                                        <div class="small text-muted mt-1 cursor-pointer"
                                             wire:click="filterBySearch('<?php echo e(addslashes($customer->tax_code)); ?>')"
                                             title="Lọc theo MST: <?php echo e($customer->tax_code); ?>">
                                            MST: <span class="text-body"><?php echo e($customer->tax_code); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->representative): ?>
                                        <div class="small text-muted mt-1 cursor-pointer"
                                             wire:click="filterBySearch('<?php echo e(addslashes($customer->representative)); ?>')"
                                             title="Lọc theo đại diện: <?php echo e($customer->representative); ?>">
                                            Đại diện: <span class="text-body"><?php echo e($customer->representative); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->contact_person && $customer->contact_person !== $customer->representative): ?>
                                        <div class="small text-muted mt-1 cursor-pointer"
                                             wire:click="filterBySearch('<?php echo e(addslashes($customer->contact_person)); ?>')"
                                             title="Lọc theo người liên hệ: <?php echo e($customer->contact_person); ?>">
                                            Liên hệ: <span class="text-body"><?php echo e($customer->contact_person); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 text-nowrap"><?php echo e($customer->phone ?: '—'); ?></td>
                            <td class="px-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->email): ?>
                                    <a href="mailto:<?php echo e($customer->email); ?>" class="text-body text-decoration-none"><?php echo e($customer->email); ?></a>
                                <?php else: ?>
                                    —
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList !== 'all'): ?>
                            <td class="px-3" style="min-width: 175px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('customers.edit')): ?>
                                    <select class="form-select form-select-sm border-secondary-subtle py-1 px-2 text-truncate"
                                            wire:change="updateCaretaker(<?php echo e($customer->id); ?>, $event.target.value)"
                                            style="font-size: 0.82rem; max-width: 190px;"
                                            title="Phân công người chăm sóc">
                                        <option value="">Chưa phân công</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $caretakerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caretaker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($caretaker->id); ?>" <?php if((int) $customer->caretaker_id === (int) $caretaker->id): echo 'selected'; endif; ?>>
                                                <?php echo e($caretaker->name); ?>

                                            </option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                <?php else: ?>
                                    <span class="text-body"><?php echo e($customer->caretaker?->name ?: '—'); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-3" style="min-width: 155px;">
                                <?php echo $__env->make('livewire.admin.customers.partials.care-status-cell', ['customer' => $customer, 'careStatusOptions' => $careStatusOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <td class="px-4">
                                <div class="d-flex flex-wrap gap-1" style="min-width: 175px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->province): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fs-72 cursor-pointer"
                                              wire:click="filterByProvince('<?php echo e(addslashes($customer->province)); ?>')"
                                              title="Lọc theo tỉnh/thành: <?php echo e($customer->province); ?>">
                                            <i class="fa-solid fa-location-dot me-1"></i><?php echo e($customer->province); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1" style="font-size: 0.72rem;">Chưa có tỉnh/thành</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->ward): ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1 fs-72 cursor-pointer"
                                              wire:click="filterByWard('<?php echo e(addslashes($customer->ward)); ?>', '<?php echo e(addslashes($customer->province)); ?>')"
                                              title="Lọc theo phường/xã: <?php echo e($customer->ward); ?>">
                                            <?php echo e($customer->ward); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->industrial_park): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 fs-72 cursor-pointer"
                                              wire:click="filterByIndustrialPark('<?php echo e(addslashes($customer->industrial_park)); ?>', '<?php echo e(addslashes($customer->province)); ?>')"
                                              title="Lọc theo KCN: <?php echo e($customer->industrial_park); ?>">
                                            <i class="fa-solid fa-industry me-1"></i><?php echo e($customer->industrial_park); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customerList === 'all'): ?>
                            <td class="px-4">
                                <div style="min-width: 310px; max-width: 440px; white-space: normal;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = array_slice($breakdown, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php echo $__env->make('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <span class="small text-muted">Chưa phát sinh dịch vụ</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($breakdown) > 3): ?>
                                        <details class="more-services mt-1">
                                            <summary class="fw-semibold">+ <?php echo e(count($breakdown) - 3); ?> dịch vụ khác</summary>
                                            <div class="mt-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($breakdown, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <?php echo $__env->make('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </div>
                                        </details>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['customers.edit', 'customers.delete'])): ?>
                            <td class="text-end pe-4 text-nowrap">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.edit')): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle rounded-8px px-2 py-1.5 me-1"
                                        wire:click="openEdit(<?php echo e($customer->id); ?>)"
                                        title="Sửa <?php echo e($customer->name); ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.delete')): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger border-light-subtle rounded-8px px-2 py-1.5"
                                        wire:click="delete(<?php echo e($customer->id); ?>)"
                                        wire:confirm="Xác nhận xóa khách hàng này?"
                                        title="Xóa <?php echo e($customer->name); ?>"
                                        <?php if($this->totalContractsCount($customer) > 0): echo 'disabled'; endif; ?>>
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="<?php echo e($columnCount); ?>" class="text-center py-5">
                                <i class="fa-solid fa-users-slash fa-3x text-muted mb-3 opacity-40"></i>
                                <div class="fw-semibold text-muted">Không tìm thấy khách hàng</div>
                                <div class="small text-muted mt-1">Thử thay đổi từ khóa hoặc xóa bộ lọc.</div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-md-none p-3">
            <?php ($mobileGroup = null); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($groupBy !== 'none' && $mobileGroup !== $this->groupValue($customer)): ?>
                    <?php ($mobileGroup = $this->groupValue($customer)); ?>
                    <div class="small fw-bold text-primary text-uppercase mt-3 mb-2 cursor-pointer"
                         wire:click="filterByGroup('<?php echo e(addslashes($mobileGroup)); ?>')">
                        <i class="fa-solid fa-location-dot me-1"></i><?php echo e($mobileGroup); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php ($breakdown = $this->serviceBreakdown($customer)); ?>
                <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'customer-mobile-'.e($customer->id).''; ?>wire:key="customer-mobile-<?php echo e($customer->id); ?>" class="card border-0 shadow-sm rounded-12px p-3 mb-3 bg-body">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <a href="<?php echo e(route('app.customers.contracts', $customer)); ?>"
                               class="fw-bold text-body text-decoration-none link-primary">
                                <?php echo e($customer->name); ?>

                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->tax_code): ?>
                                <div class="small text-muted mt-1 cursor-pointer"
                                     wire:click="filterBySearch('<?php echo e(addslashes($customer->tax_code)); ?>')">
                                    MST: <?php echo e($customer->tax_code); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->representative): ?>
                                <div class="small text-muted mt-1 cursor-pointer"
                                     wire:click="filterBySearch('<?php echo e(addslashes($customer->representative)); ?>')">
                                    Đại diện: <?php echo e($customer->representative); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->contact_person || $customer->phone || $customer->email): ?>
                                <div class="small text-muted mt-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->contact_person): ?><div>Liên hệ: <?php echo e($customer->contact_person); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->phone): ?><div><i class="fa-solid fa-phone me-1"></i><?php echo e($customer->phone); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->email): ?><div><i class="fa-solid fa-envelope me-1"></i><?php echo e($customer->email); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.edit')): ?>
                                <div class="mt-2" style="max-width: 220px;">
                                    <label class="form-label text-muted small mb-1" style="font-size: 0.72rem;">Người chăm sóc:</label>
                                    <select class="form-select form-select-sm border-secondary-subtle"
                                            wire:change="updateCaretaker(<?php echo e($customer->id); ?>, $event.target.value)"
                                            style="font-size: 0.8rem;">
                                        <option value="">Chưa phân công</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $caretakerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caretaker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($caretaker->id); ?>" <?php if((int) $customer->caretaker_id === (int) $caretaker->id): echo 'selected'; endif; ?>>
                                                <?php echo e($caretaker->name); ?>

                                            </option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->caretaker): ?>
                                    <div class="small text-muted mt-1">Chăm sóc: <?php echo e($customer->caretaker->name); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1.5 flex-shrink-0">
                            <a href="<?php echo e(route('app.quotation-tracking.index', ['search' => $customer->name])); ?>"
                               class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1.5"
                               style="font-size: 0.7rem;">
                                <?php echo e($customer->quotations_count); ?> BG
                            </a>
                            <a href="<?php echo e(route('app.customers.contracts', $customer)); ?>"
                               class="badge bg-success bg-opacity-10 text-success text-decoration-none px-2 py-1.5"
                               style="font-size: 0.7rem;">
                                <?php echo e($this->totalContractsCount($customer)); ?> HĐ
                            </a>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-1 mt-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->province): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fs-72 cursor-pointer"
                                  wire:click="filterByProvince('<?php echo e(addslashes($customer->province)); ?>')">
                                <i class="fa-solid fa-location-dot me-1"></i><?php echo e($customer->province); ?>

                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 fs-72">Chưa cập nhật</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->ward): ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1 fs-72 cursor-pointer"
                                  wire:click="filterByWard('<?php echo e(addslashes($customer->ward)); ?>', '<?php echo e(addslashes($customer->province)); ?>')">
                                <?php echo e($customer->ward); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->industrial_park): ?>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 fs-72 cursor-pointer"
                                  wire:click="filterByIndustrialPark('<?php echo e(addslashes($customer->industrial_park)); ?>', '<?php echo e(addslashes($customer->province)); ?>')">
                                <i class="fa-solid fa-industry me-1"></i><?php echo e($customer->industrial_park); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = array_slice($breakdown, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php echo $__env->make('livewire.admin.customers.partials.service-line', ['service' => $service, 'customer' => $customer], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <span class="small text-muted">Chưa phát sinh dịch vụ</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['customers.edit', 'customers.delete'])): ?>
                    <div class="d-flex gap-2 border-top mt-3 pt-3">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.edit')): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary border-light-subtle rounded-8px flex-fill py-2"
                                wire:click="openEdit(<?php echo e($customer->id); ?>)">
                            <i class="fa-solid fa-pen me-1"></i>Sửa
                        </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('customers.delete')): ?>
                        <button type="button" class="btn btn-sm btn-outline-danger border-light-subtle rounded-8px flex-fill py-2"
                                wire:click="delete(<?php echo e($customer->id); ?>)"
                                wire:confirm="Xác nhận xóa khách hàng này?"
                                <?php if($this->totalContractsCount($customer) > 0): echo 'disabled'; endif; ?>>
                            <i class="fa-solid fa-trash me-1"></i>Xóa
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="text-center py-5 text-muted card border-0 shadow-sm rounded-12px">Không tìm thấy khách hàng.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customers->hasPages()): ?>
            <div class="card-footer border-top bg-body px-3 px-md-4 py-3">
                <?php echo e($customers->links('livewire.admin.users.pagination')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div wire:ignore.self class="modal fade" id="customerFormModal" tabindex="-1" aria-labelledby="customer-form-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 bg-transparent p-4 pb-3">
                    <div>
                        <h5 id="customer-form-title" class="modal-title fw-bold text-body">
                            <?php echo e($isEditing ? 'Cập nhật khách hàng' : 'Thêm khách hàng mới'); ?>

                        </h5>
                        <p class="small text-muted mb-0 mt-1">Địa chỉ sẽ được tự nhận diện tỉnh/thành, phường/xã và KCN.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="customer-name" class="form-label fw-bold text-body">Tên khách hàng <span class="text-danger">*</span></label>
                                <input id="customer-name" type="text"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model.defer="formData.name" autocomplete="organization">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-5">
                                <label for="customer-tax-code" class="form-label fw-bold text-body">Mã số thuế</label>
                                <input id="customer-tax-code" type="text"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.tax_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model.defer="formData.tax_code">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.tax_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-representative" class="form-label fw-bold text-body">Người đại diện</label>
                                <input id="customer-representative" type="text"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.representative'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model.defer="formData.representative" autocomplete="name">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.representative'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-contact-person" class="form-label fw-bold text-body">Người liên hệ</label>
                                <input id="customer-contact-person" type="text"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.contact_person'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model.defer="formData.contact_person" autocomplete="name">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.contact_person'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-phone" class="form-label fw-bold text-body">Số điện thoại</label>
                                <input id="customer-phone" type="tel"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model.defer="formData.phone" autocomplete="tel">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-email" class="form-label fw-bold text-body">Email</label>
                                <input id="customer-email" type="email"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model.defer="formData.email" autocomplete="email">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="customer-caretaker" class="form-label fw-bold text-body">Người chăm sóc</label>
                                <select id="customer-caretaker"
                                        class="form-select border-light-subtle <?php $__errorArgs = ['formData.caretaker_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        wire:model.defer="formData.caretaker_id">
                                    <option value="">Chưa phân công</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $caretakerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caretaker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($caretaker->id); ?>"><?php echo e($caretaker->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <div class="form-text">Chỉ gồm nhân viên Kinh doanh và Trưởng phòng Kinh doanh đang hoạt động.</div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.caretaker_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label for="customer-address" class="form-label fw-bold text-body">Địa chỉ</label>
                                <textarea id="customer-address" rows="3"
                                          class="form-control border-light-subtle <?php $__errorArgs = ['formData.address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                          wire:model="formData.address"
                                          placeholder="Ví dụ: KCN Long Hậu, Xã Long Hậu, Tỉnh Tây Ninh"></textarea>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-8px mt-2"
                                        wire:click="detectAddressRegion"
                                        wire:loading.attr="disabled"
                                        wire:target="detectAddressRegion">
                                    <span wire:loading wire:target="detectAddressRegion" class="spinner-border spinner-border-sm me-1"></span>
                                    <i wire:loading.remove wire:target="detectAddressRegion" class="fa-solid fa-location-crosshairs me-1"></i>
                                    Nhận diện từ địa chỉ
                                </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="customer-province" class="form-label fw-bold text-body">Tỉnh / thành mới</label>
                                <select id="customer-province"
                                        class="form-select border-light-subtle <?php $__errorArgs = ['formData.province'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        wire:model="formData.province">
                                    <option value="">Chọn tỉnh/thành</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($province); ?>"><?php echo e($province); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.province'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="customer-ward" class="form-label fw-bold text-body">Phường / xã / đặc khu</label>
                                <input id="customer-ward" type="text"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.ward'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="formData.ward" placeholder="Ví dụ: Phường Bình Hòa">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.ward'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="customer-industrial-park" class="form-label fw-bold text-body">Khu công nghiệp</label>
                                <input id="customer-industrial-park" type="text"
                                       class="form-control border-light-subtle <?php $__errorArgs = ['formData.industrial_park'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       wire:model="formData.industrial_park" placeholder="Ví dụ: KCN Đông An">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['formData.industrial_park'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-transparent p-4 pt-0 justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary rounded-8px px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary rounded-8px px-4" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            Lưu khách hàng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="customerNormalizationModal" tabindex="-1"
         aria-labelledby="customer-normalization-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 bg-transparent p-4 pb-3">
                    <div>
                        <h5 id="customer-normalization-title" class="modal-title fw-bold text-body">Chuẩn hóa dữ liệu khách hàng cũ</h5>
                        <p class="small text-muted mb-0 mt-1">Xem trước — chưa thay đổi dữ liệu.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($normalizationPreview): ?>
                        <div class="alert alert-primary border-0 rounded-12px">
                            Tìm thấy <strong><?php echo e(number_format($normalizationPreview['changed'])); ?></strong>
                            / <?php echo e(number_format($normalizationPreview['total'])); ?> khách hàng có thể chuẩn hóa.
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="border border-light-subtle bg-body-tertiary rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold mb-1 text-body"><?php echo e(number_format($normalizationPreview['province_changed'])); ?></div>
                                    <div class="small text-muted">Tỉnh cũ → tỉnh mới</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-light-subtle bg-body-tertiary rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold mb-1 text-body"><?php echo e(number_format($normalizationPreview['ward_detected'])); ?></div>
                                    <div class="small text-muted">Phường/xã nhận diện được</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-light-subtle bg-body-tertiary rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold mb-1 text-body"><?php echo e(number_format($normalizationPreview['industrial_park_detected'])); ?></div>
                                    <div class="small text-muted">KCN nhận diện được</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border border-warning bg-warning bg-opacity-10 rounded-12px p-3 h-100">
                                    <div class="h5 fw-bold text-warning mb-1"><?php echo e(number_format($normalizationPreview['needs_review'])); ?></div>
                                    <div class="small text-muted">Cần rà soát phường/xã</div>
                                </div>
                            </div>
                        </div>
                        <p class="small text-muted mt-3 mb-0">
                            Công cụ không tự đoán phường/xã khi địa chỉ thiếu thông tin. Các ô đã có dữ liệu sẽ được giữ nguyên.
                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer border-top-0 bg-transparent p-4 pt-0 justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary rounded-8px px-4" data-bs-dismiss="modal">Để sau</button>
                    <button type="button" class="btn btn-primary rounded-8px px-4"
                            wire:click="normalizeLegacyCustomers"
                            wire:confirm="Áp dụng chuẩn hóa cho dữ liệu khách hàng cũ?"
                            wire:loading.attr="disabled"
                            wire:target="normalizeLegacyCustomers">
                        <span wire:loading wire:target="normalizeLegacyCustomers" class="spinner-border spinner-border-sm me-1"></span>
                        Áp dụng chuẩn hóa
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        window.addEventListener('openCustomerFormModal', () => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('customerFormModal')).show();
        });

        window.addEventListener('closeCustomerFormModal', () => {
            bootstrap.Modal.getInstance(document.getElementById('customerFormModal'))?.hide();
        });

        window.addEventListener('openCustomerNormalizationModal', () => {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('customerNormalizationModal')).show();
        });

        window.addEventListener('closeCustomerNormalizationModal', () => {
            bootstrap.Modal.getInstance(document.getElementById('customerNormalizationModal'))?.hide();
        });
    </script>
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH C:\laragon\www\laravel\resources\views/livewire/admin/customers/customer-manager.blade.php ENDPATH**/ ?>