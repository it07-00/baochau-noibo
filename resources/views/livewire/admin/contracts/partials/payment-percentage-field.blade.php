<div class="col-md-3">
    <label class="form-label small fw-semibold">% thanh toán</label>
    <div class="input-group">
        <input type="number" min="0" max="100" step="0.01" class="form-control"
            wire:model.live.debounce.300ms="formData.payment_percentage">
        <span class="input-group-text">%</span>
    </div>
    @error('formData.payment_percentage')
        <div class="text-danger mt-1">{{ $message }}</div>
    @enderror
</div>
