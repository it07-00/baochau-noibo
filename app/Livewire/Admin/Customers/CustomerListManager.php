<?php

namespace App\Livewire\Admin\Customers;

use App\Enums\Permission;

class CustomerListManager extends CustomerManager
{
    public function mount(string $customerListType): void
    {
        $this->customerList = match ($customerListType) {
            'ghg_inventory' => 'ghg_inventory',
            'energy_audit' => 'energy_audit',
            default => abort(404),
        };
    }

    protected function viewPermission(): Permission
    {
        return Permission::CUSTOMER_LISTS_VIEW;
    }

    protected function editPermission(): Permission
    {
        return Permission::CUSTOMER_LISTS_EDIT;
    }

    protected function createPermission(): ?Permission
    {
        return null;
    }

    protected function deletePermission(): ?Permission
    {
        return null;
    }

    protected function isCustomerListDirectory(): bool
    {
        return true;
    }

    protected function directoryTitle(): string
    {
        return $this->customerList === 'ghg_inventory'
            ? 'Dữ liệu khách hàng KKKNK'
            : 'Dữ liệu khách hàng KTNL';
    }

    protected function directoryDescription(): string
    {
        return $this->customerList === 'ghg_inventory'
            ? 'Danh sách khách hàng từ dữ liệu kiểm kê khí nhà kính, phục vụ phân công và theo dõi chăm sóc.'
            : 'Danh sách khách hàng từ dữ liệu kiểm toán năng lượng, phục vụ phân công và theo dõi chăm sóc.';
    }
}
