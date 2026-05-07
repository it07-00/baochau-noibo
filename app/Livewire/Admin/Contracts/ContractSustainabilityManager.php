<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\Permission;
use App\Models\ContractSustainability;

class ContractSustainabilityManager extends AbstractContractGenericManager
{
    protected function getModelClass(): string        { return ContractSustainability::class; }
    protected function getContractType(): string      { return 'sustainability'; }
    protected function getPermCreate(): Permission    { return Permission::CONTRACTS_SUSTAINABILITY_CREATE; }
    protected function getPermEdit(): Permission      { return Permission::CONTRACTS_SUSTAINABILITY_EDIT; }
    protected function getPermDelete(): Permission    { return Permission::CONTRACTS_SUSTAINABILITY_DELETE; }
    protected function getViewName(): string          { return 'contract-sustainability-manager'; }
    protected function getPageTitle(): string         { return 'TV & BC PTBV'; }
    protected function getExportTitle(): string       { return 'HĐ Phát triển bền vững'; }
    protected function getExportFilenamePrefix(): string { return 'HopDong_PhatTrienBenVung'; }
}
