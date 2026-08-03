<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('customers.edit')): ?>
    <select class="form-select form-select-sm border-secondary-subtle py-1 px-2"
            wire:change="updateCareStatus(<?php echo e($customer->id); ?>, $event.target.value)"
            style="font-size: 0.82rem; max-width: 170px;"
            title="Cập nhật trạng thái chăm sóc">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $careStatusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <option value="<?php echo e($opt['value']); ?>" <?php if($customer->care_status?->value === $opt['value']): echo 'selected'; endif; ?>>
                <?php echo e($opt['label']); ?>

            </option>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </select>
<?php else: ?>
    <?php
        $cs = $customer->care_status;
        $csBadgeClass = $cs ? $cs->badgeClass() : 'bg-secondary bg-opacity-10 text-secondary';
        $csLabel = $cs ? $cs->label() : 'Chưa liên hệ';
    ?>
    <span class="badge px-2 py-1 <?php echo e($csBadgeClass); ?>" style="font-size: 0.75rem;"><?php echo e($csLabel); ?></span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\laragon\www\laravel\resources\views/livewire/admin/customers/partials/care-status-cell.blade.php ENDPATH**/ ?>