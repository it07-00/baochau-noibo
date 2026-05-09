<?php

namespace App\Livewire\Concerns;

/**
 * Strip dấu chấm phân cách hàng nghìn (71.900.000 → 71900000)
 * từ các field tiền trước khi validate/save.
 */
trait CleanMoneyInput
{
    protected function cleanMoney(mixed $value): mixed
    {
        if (is_string($value)) {
            $cleaned = str_replace('.', '', $value);
            return is_numeric($cleaned) ? (float) $cleaned : $value;
        }
        return $value;
    }

    protected function cleanMoneyFields(array &$data, array $fields): void
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->cleanMoney($data[$field]);
            }
        }
    }

    protected function cleanMoneyProperties(array $fields): void
    {
        foreach ($fields as $field) {
            if (isset($this->{$field})) {
                $this->{$field} = $this->cleanMoney($this->{$field});
            }
        }
    }
}
