<?php

namespace App\Livewire\Admin\Contracts;

use App\Enums\Permission;
use App\Models\ContractTechnical;

class ContractProjectManager extends AbstractContractGenericManager
{
    protected function getModelClass(): string        { return ContractTechnical::class; }
    protected function getContractType(): string      { return 'project'; }
    protected function getPermCreate(): Permission    { return Permission::CONTRACTS_PROJECT_CREATE; }
    protected function getPermEdit(): Permission      { return Permission::CONTRACTS_PROJECT_EDIT; }
    protected function getPermDelete(): Permission    { return Permission::CONTRACTS_PROJECT_DELETE; }
    protected function getViewName(): string          { return 'contract-project-manager'; }
    protected function getPageTitle(): string         { return 'Ứng phó sự cố'; }
    protected function getExportTitle(): string       { return 'Hợp đồng dự án'; }
    protected function getExportFilenamePrefix(): string { return 'HopDong_DuAn'; }
}
