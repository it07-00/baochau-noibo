<?php

namespace App\Livewire\Concerns;

/**
 * Strip dấu chấm phân cách hàng nghìn (71.900.000 → 71900000)
 * từ các field tiền trước khi validate/save.
 */
trait CleanMoneyInput
{
    protected function cleanMoney(mixed $value, bool $blankAsZero = false): mixed
    {
        if ($blankAsZero && $value === null) {
            return 0;
        }

        if (is_string($value)) {
            $cleaned = str_replace('.', '', trim($value));
            if ($blankAsZero && $cleaned === '') {
                return 0;
            }

            return is_numeric($cleaned) ? (float) $cleaned : $value;
        }

        return $value;
    }

    protected function cleanMoneyFields(array &$data, array $fields, bool $blankAsZero = false): void
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->cleanMoney($data[$field], $blankAsZero);
            }
        }
    }

    protected function cleanMoneyProperties(array $fields, bool $blankAsZero = false): void
    {
        foreach ($fields as $field) {
            if (isset($this->{$field}) || property_exists($this, $field)) {
                $this->{$field} = $this->cleanMoney($this->{$field}, $blankAsZero);
            }
        }
    }
}
