<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\Permission;
use App\Models\ContractEmission;

class ContractEnergyManager extends AbstractContractGenericManager
{
    protected function getModelClass(): string        { return ContractEmission::class; }
    protected function getContractType(): string      { return 'energy'; }
    protected function getPermCreate(): Permission    { return Permission::CONTRACTS_ENERGY_CREATE; }
    protected function getPermEdit(): Permission      { return Permission::CONTRACTS_ENERGY_EDIT; }
    protected function getPermDelete(): Permission    { return Permission::CONTRACTS_ENERGY_DELETE; }
    protected function getViewName(): string          { return 'contract-energy-manager'; }
    protected function getPageTitle(): string         { return 'Phát thải & Năng lượng'; }
    protected function getExportTitle(): string       { return 'HĐ Năng lượng'; }
    protected function getExportFilenamePrefix(): string { return 'HopDong_NangLuong'; }
}
