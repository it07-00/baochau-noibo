<div class="d-flex align-items-center justify-content-between gap-2 mb-1" title="<?php echo e($service['label']); ?>">
    <span class="badge d-inline-block bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 text-truncate fw-semibold cursor-pointer"
          style="font-size: 0.75rem; max-width: 220px; text-align: left;"
          wire:click="filterByService('<?php echo e(addslashes($service['label'])); ?>')"
          title="Lọc theo dịch vụ: <?php echo e($service['label']); ?>">
        <i class="fa-solid fa-gear me-1"></i><?php echo e($service['label']); ?>

    </span>
    <div class="d-flex gap-1 align-items-center flex-shrink-0">
        <a href="<?php echo e(route('app.quotation-tracking.index', ['search' => $customer->name])); ?>"
           class="badge bg-primary bg-opacity-10 text-primary text-decoration-none px-2 py-1"
           aria-label="<?php echo e($service['quotations']); ?> báo giá của <?php echo e($customer->name); ?>"
           style="font-size: 0.68rem; font-weight: 600;">
            <?php echo e($service['quotations']); ?> BG
        </a>
        <a href="<?php echo e(route('app.customers.contracts', $customer)); ?>"
           class="badge bg-success bg-opacity-10 text-success text-decoration-none px-2 py-1"
           aria-label="<?php echo e($service['contracts']); ?> hợp đồng của <?php echo e($customer->name); ?>"
           style="font-size: 0.68rem; font-weight: 600;">
            <?php echo e($service['contracts']); ?> HĐ
        </a>
    </div>
</div>
<?php /**PATH C:\laragon\www\laravel\resources\views/livewire/admin/customers/partials/service-line.blade.php ENDPATH**/ ?>