<?php

namespace App\Livewire\Concerns;

trait HasMultiServiceSelection
{
    public const PRESET_SERVICES = [
        'Quan trắc môi trường lao động',
        'Quan trắc môi trường',
        'Báo cáo công tác bảo vệ môi trường',
        'Đăng ký môi trường',
        'Giấy phép môi trường',
        'Ứng phó sự cố',
        'Phân loại lao động',
        'Kiểm kê khí nhà kính',
        'Giảm phát thải',
    ];

    public array $selectedServices = [];

    public bool $hasCustomService = false;

    public string $customServiceText = '';

    public function updatedHasCustomService($value): void
    {
        if (! $value) {
            $this->customServiceText = '';
        }
    }

    protected function populateServiceFields(?string $serviceString): void
    {
        $this->selectedServices = [];
        $this->hasCustomService = false;
        $this->customServiceText = '';

        if (blank($serviceString)) {
            return;
        }

        $parts = array_filter(array_map('trim', explode(',', $serviceString)));

        $remainingParts = [];
        foreach ($parts as $part) {
            $matchedPreset = null;
            foreach (self::PRESET_SERVICES as $preset) {
                if (mb_strtolower($part) === mb_strtolower($preset)) {
                    $matchedPreset = $preset;
                    break;
                }
            }

            if ($matchedPreset !== null) {
                if (! in_array($matchedPreset, $this->selectedServices, true)) {
                    $this->selectedServices[] = $matchedPreset;
                }
            } else {
                $remainingParts[] = $part;
            }
        }

        if (! empty($remainingParts)) {
            $this->hasCustomService = true;
            $this->customServiceText = implode(', ', $remainingParts);
        }
    }

    protected function prepareServiceData(string $field = 'service'): void
    {
        $services = is_array($this->selectedServices) ? $this->selectedServices : [];

        if ($this->hasCustomService && filled($this->customServiceText)) {
            $customs = array_filter(array_map('trim', explode(',', $this->customServiceText)));
            foreach ($customs as $custom) {
                if (! in_array($custom, $services, true)) {
                    $services[] = $custom;
                }
            }
        }

        $this->formData[$field] = implode(', ', $services);
    }
}
