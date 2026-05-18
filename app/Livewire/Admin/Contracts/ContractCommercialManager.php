<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\Permission;
use App\Models\ContractResearch;

class ContractCommercialManager extends AbstractContractGenericManager
{
    protected function getModelClass(): string        { return ContractResearch::class; }
    protected function getContractType(): string      { return 'commercial'; }
    protected function getPermCreate(): Permission    { return Permission::CONTRACTS_COMMERCIAL_CREATE; }
    protected function getPermEdit(): Permission      { return Permission::CONTRACTS_COMMERCIAL_EDIT; }
    protected function getPermDelete(): Permission    { return Permission::CONTRACTS_COMMERCIAL_DELETE; }
    protected function getViewName(): string          { return 'contract-commercial-manager'; }
    protected function getPageTitle(): string         { return 'Nghiên cứu và chuyển đổi công nghệ'; }
    protected function getExportTitle(): string       { return 'Hợp đồng thương mại'; }
    protected function getExportFilenamePrefix(): string { return 'HopDong_ThuongMai'; }
}
