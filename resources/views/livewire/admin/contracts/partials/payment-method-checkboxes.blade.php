<div class="border rounded-2 p-2 bg-light">
    <div class="row g-2">
        @foreach ($payment_methods as $index => $pm)
            <div class="col-12 col-lg-6">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                        id="payment_method_{{ $this->getId() }}_{{ $index }}"
                        value="{{ $pm }}" wire:model="paymentMethods">
                    <label class="form-check-label small" for="payment_method_{{ $this->getId() }}_{{ $index }}">
                        {{ $pm }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>
