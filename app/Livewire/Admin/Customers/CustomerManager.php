<?php

namespace App\Livewire\Admin\Customers;

use App\Enums\CustomerCareStatus;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use App\Services\CustomerRegionNormalizer;
use App\Support\VietnameseAddressParser;
use App\Support\VietnamProvinces;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    #[Locked]
    public string $customerList = 'all';

    public string $provinceFilter = '';

    public string $wardFilter = '';

    public string $industrialParkFilter = '';

    public $serviceQuotationFilter = [];

    public string $serviceContractFilter = '';

    public string $staffFilter = '';

    public string $caretakerStatusFilter = '';

    public string $careStatusFilter = '';

    public string $sectorFilter = '';

    public string $appendixFilter = '';

    public string $serviceFilter = ''; // Backward compatibility for older/cached sessions

    public string $groupBy = 'province';

    public bool $showModal = false;

    public array $normalizationPreview = [];

    public bool $isEditing = false;

    public ?int $editingId = null;

    public array $formData = [
        'name' => '',
        'tax_code' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
        'province' => '',
        'ward' => '',
        'industrial_park' => '',
        'representative' => '',
        'contact_person' => '',
        'caretaker_id' => '',
        'sector' => '',
        'appendix' => '',
    ];

    /**
     * Relation => [model, fallback service label].
     */
    private const CONTRACT_RELATIONS = [
        'contracts' => [ContractWaste::class, 'Chất thải'],
        'contractsConsulting' => [ContractLegal::class, 'Quan trắc & hồ sơ môi trường'],
        'contractsCommercial' => [ContractResearch::class, 'Nghiên cứu & công nghệ'],
        'contractsProject' => [ContractTechnical::class, 'Ứng phó sự cố'],
        'contractsEnergy' => [ContractEmission::class, 'Năng lượng & giảm phát thải'],
        'contractsSustainability' => [ContractSustainability::class, 'Phát triển bền vững'],
    ];

    protected function viewPermission(): Permission
    {
        return Permission::CUSTOMERS_VIEW;
    }

    protected function editPermission(): Permission
    {
        return Permission::CUSTOMERS_EDIT;
    }

    protected function createPermission(): ?Permission
    {
        return Permission::CUSTOMERS_CREATE;
    }

    protected function deletePermission(): ?Permission
    {
        return Permission::CUSTOMERS_DELETE;
    }

    protected function isCustomerListDirectory(): bool
    {
        return false;
    }

    protected function directoryTitle(): string
    {
        return 'Danh sách khách hàng';
    }

    protected function directoryDescription(): string
    {
        return 'Theo dõi khách hàng theo tỉnh/thành, phường/xã, khu công nghiệp và hiệu suất báo giá – hợp đồng.';
    }

    public function paginationView(): string
    {
        return 'livewire.admin.users.pagination';
    }

    public function updating(string $property): void
    {
        if (in_array($property, [
            'search',
            'provinceFilter',
            'wardFilter',
            'industrialParkFilter',
            'serviceQuotationFilter',
            'serviceContractFilter',
            'staffFilter',
            'caretakerStatusFilter',
            'careStatusFilter',
            'sectorFilter',
            'appendixFilter',
            'serviceFilter',
            'groupBy',
        ], true)) {
            $this->resetPage();
        }
    }

    public function updatedProvinceFilter(): void
    {
        $this->wardFilter = '';
        $this->industrialParkFilter = '';
    }

    public function updatedWardFilter(): void
    {
        $this->industrialParkFilter = '';
    }

    public function updated(string $property, mixed $value): void
    {
        if ($property !== 'formData.address') {
            return;
        }

        $this->fillDetectedRegion((string) $value);
    }

    public function detectAddressRegion(): void
    {
        $this->fillDetectedRegion((string) ($this->formData['address'] ?? ''), overwrite: true);
    }

    private function fillDetectedRegion(string $address, bool $overwrite = false): void
    {
        $detected = VietnameseAddressParser::parse($address);

        foreach (['province', 'ward', 'industrial_park'] as $field) {
            if (($overwrite || blank($this->formData[$field] ?? null)) && filled($detected[$field])) {
                $this->formData[$field] = $detected[$field];
            }
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'provinceFilter',
            'wardFilter',
            'industrialParkFilter',
            'serviceQuotationFilter',
            'serviceContractFilter',
            'staffFilter',
            'caretakerStatusFilter',
            'careStatusFilter',
            'sectorFilter',
            'appendixFilter',
            'serviceFilter',
        ]);
        $this->groupBy = 'province';
        $this->resetPage();
    }

    public function filterByProvince(string $province): void
    {
        $this->provinceFilter = trim($province);
        $this->wardFilter = '';
        $this->industrialParkFilter = '';
        $this->resetPage();
    }

    public function filterByWard(string $ward, string $province = ''): void
    {
        if (filled($province)) {
            $this->provinceFilter = trim($province);
        }
        $this->wardFilter = trim($ward);
        $this->industrialParkFilter = '';
        $this->resetPage();
    }

    public function filterByIndustrialPark(string $industrialPark, string $province = ''): void
    {
        if (filled($province)) {
            $this->provinceFilter = trim($province);
        }
        $this->industrialParkFilter = trim($industrialPark);
        $this->resetPage();
    }

    public function filterByGroup(string $groupValue): void
    {
        $groupValue = trim($groupValue);
        if ($groupValue === '' || $groupValue === 'Chưa cập nhật') {
            return;
        }

        match ($this->groupBy) {
            'ward' => $this->filterByWard($groupValue),
            'industrial_park' => $this->filterByIndustrialPark($groupValue),
            default => $this->filterByProvince($groupValue),
        };
    }

    public function filterBySearch(string $text): void
    {
        $this->search = trim($text);
        $this->resetPage();
    }

    public function filterByService(string $serviceLabel): void
    {
        $serviceLabel = trim($serviceLabel);
        if ($serviceLabel === '') {
            return;
        }

        $this->serviceContractFilter = $serviceLabel;
        $this->serviceQuotationFilter = [$serviceLabel];
        $this->resetPage();
    }

    public function previewLegacyNormalization(): void
    {
        abort_unless(! $this->isCustomerListDirectory() && auth()->user()->can(Permission::CUSTOMERS_EDIT->value), 403);

        $this->normalizationPreview = app(CustomerRegionNormalizer::class)->run();
        $this->dispatch('openCustomerNormalizationModal');
    }

    public function normalizeLegacyCustomers(): void
    {
        abort_unless(! $this->isCustomerListDirectory() && auth()->user()->can(Permission::CUSTOMERS_EDIT->value), 403);

        $this->normalizationPreview = app(CustomerRegionNormalizer::class)->run(apply: true);
        $this->dispatch('closeCustomerNormalizationModal');
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => "Đã chuẩn hóa {$this->normalizationPreview['changed']} khách hàng cũ.",
        ]);
    }

    public function openCreate(): void
    {
        $permission = $this->createPermission();
        abort_unless($permission && auth()->user()->can($permission->value), 403);

        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
        $this->dispatch('openCustomerFormModal');
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->can($this->viewPermission()->value), 403);

        $customer = $this->findScopedCustomer($id);

        $this->editingId = $customer->id;
        $this->formData = [
            'name' => (string) $customer->name,
            'tax_code' => (string) ($customer->tax_code ?? ''),
            'address' => (string) ($customer->address ?? ''),
            'phone' => (string) ($customer->phone ?? ''),
            'email' => (string) ($customer->email ?? ''),
            'province' => (string) ($customer->province ?? ''),
            'ward' => (string) ($customer->ward ?? ''),
            'industrial_park' => (string) ($customer->industrial_park ?? ''),
            'representative' => (string) ($customer->representative ?? ''),
            'contact_person' => (string) ($customer->contact_person ?? ''),
            'caretaker_id' => $customer->caretaker_id ? (string) $customer->caretaker_id : '',
            'sector' => (string) ($customer->sector ?? ''),
            'appendix' => (string) ($customer->appendix ?? ''),
        ];

        $this->isEditing = true;
        $this->showModal = true;
        $this->dispatch('openCustomerFormModal');
    }

    public function totalContractsCount(Customer $customer): int
    {
        return collect(array_keys(self::CONTRACT_RELATIONS))
            ->sum(fn (string $relation): int => (int) $customer->getAttribute(Str::snake($relation).'_count'));
    }

    public function save(): void
    {
        $permission = $this->isEditing ? $this->editPermission() : $this->createPermission();
        abort_unless($permission && auth()->user()->can($permission->value), 403);

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:customers,name'.($this->editingId ? ','.$this->editingId : ''),
            'formData.tax_code' => 'nullable|string|max:50',
            'formData.address' => 'nullable|string|max:2000',
            'formData.phone' => 'nullable|string|max:30',
            'formData.email' => 'nullable|email|max:255',
            'formData.province' => 'nullable|string|max:255',
            'formData.ward' => 'nullable|string|max:255',
            'formData.industrial_park' => 'nullable|string|max:255',
            'formData.representative' => 'nullable|string|max:255',
            'formData.contact_person' => 'nullable|string|max:255',
            'formData.caretaker_id' => ['nullable', 'integer', Rule::in($this->caretakerOptions()->pluck('id')->all())],
            'formData.sector' => 'nullable|string|max:255',
            'formData.appendix' => 'nullable|string|max:255',
        ], [], [
            'formData.name' => 'tên khách hàng',
            'formData.tax_code' => 'mã số thuế',
            'formData.address' => 'địa chỉ',
            'formData.phone' => 'số điện thoại',
            'formData.email' => 'email',
            'formData.province' => 'tỉnh thành',
            'formData.ward' => 'phường xã',
            'formData.industrial_park' => 'khu công nghiệp',
            'formData.representative' => 'người đại diện',
            'formData.contact_person' => 'người liên hệ',
            'formData.caretaker_id' => 'người chăm sóc',
            'formData.sector' => 'ngành hoặc lĩnh vực',
            'formData.appendix' => 'phụ lục',
        ]);

        $detected = VietnameseAddressParser::parse($this->formData['address'] ?? null);
        $data = [
            'name' => trim((string) $this->formData['name']),
            'tax_code' => $this->nullableFormValue('tax_code'),
            'address' => $this->nullableFormValue('address'),
            'phone' => $this->nullableFormValue('phone'),
            'email' => $this->nullableFormValue('email'),
            'province' => $this->nullableFormValue('province') ?? $detected['province'],
            'ward' => VietnameseAddressParser::canonicalizeWard($this->nullableFormValue('ward') ?? $detected['ward']),
            'industrial_park' => VietnameseAddressParser::canonicalizeIndustrialPark($this->nullableFormValue('industrial_park') ?? $detected['industrial_park']),
            'representative' => $this->nullableFormValue('representative'),
            'contact_person' => $this->nullableFormValue('contact_person'),
            'caretaker_id' => filled($this->formData['caretaker_id'] ?? null) ? (int) $this->formData['caretaker_id'] : null,
            'sector' => $this->nullableFormValue('sector'),
            'appendix' => $this->nullableFormValue('appendix'),
        ];

        if ($this->isEditing && $this->editingId) {
            $this->findScopedCustomer($this->editingId)->update($data);
            $message = $this->isCustomerListDirectory()
                ? 'Cập nhật dữ liệu khách hàng thành công.'
                : 'Cập nhật khách hàng thành công.';
        } else {
            Customer::create($data);
            $message = 'Thêm khách hàng thành công.';
        }

        $this->dispatch('closeCustomerFormModal');
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => $message]);
        $this->resetForm();
    }

    public function updateCaretaker(int $customerId, mixed $caretakerId): void
    {
        abort_unless(auth()->user()->can($this->editPermission()->value), 403);

        $caretakerId = filled($caretakerId) ? (int) $caretakerId : null;

        if ($caretakerId !== null) {
            $validCaretakerIds = $this->caretakerOptions()->pluck('id')->all();
            if (! in_array($caretakerId, $validCaretakerIds, true)) {
                $this->dispatch('swal:toast', [
                    'type' => 'error',
                    'message' => 'Nhân viên chăm sóc không hợp lệ.',
                ]);

                return;
            }
        }

        $customer = $this->findScopedCustomer($customerId);
        $customer->update(['caretaker_id' => $caretakerId]);

        $caretakerName = $caretakerId ? User::find($caretakerId)?->name : null;
        $message = $caretakerName
            ? "Đã phân công {$caretakerName} chăm sóc khách hàng {$customer->name}."
            : "Đã bỏ phân công người chăm sóc cho khách hàng {$customer->name}.";

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function updateCareStatus(int $customerId, string $status): void
    {
        abort_unless(auth()->user()->can($this->editPermission()->value), 403);

        $careStatus = CustomerCareStatus::tryFrom($status);

        if ($careStatus === null) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Trạng thái chăm sóc không hợp lệ.',
            ]);

            return;
        }

        $customer = $this->findScopedCustomer($customerId);
        $customer->update(['care_status' => $careStatus->value]);

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => "Đã cập nhật trạng thái chăm sóc: {$careStatus->label()} — {$customer->name}.",
        ]);
    }

    public function delete(int $id): void
    {
        $permission = $this->deletePermission();
        abort_unless($permission && auth()->user()->can($permission->value), 403);

        $customer = $this->findScopedCustomer($id);

        if ($this->contractCountFromDatabase($customer) > 0) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => 'Không thể xóa vì khách hàng đang được dùng trong hợp đồng.',
            ]);

            return;
        }

        $customer->delete();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => 'Đã xóa khách hàng.']);
    }

    public function groupValue(Customer $customer): string
    {
        $value = match ($this->groupBy) {
            'ward' => $customer->ward,
            'industrial_park' => $customer->industrial_park,
            default => $customer->province,
        };

        return trim((string) $value) ?: 'Chưa cập nhật';
    }

    /**
     * @return array<int, array{label: string, quotations: int, contracts: int}>
     */
    public function serviceBreakdown(Customer $customer): array
    {
        $services = [];

        foreach ($customer->quotations as $quotation) {
            $label = self::canonicalizeService($quotation->service) ?: 'Chưa phân loại dịch vụ';
            $services[$label] ??= ['label' => $label, 'quotations' => 0, 'contracts' => 0];
            $services[$label]['quotations']++;
        }

        foreach (self::CONTRACT_RELATIONS as $relation => [, $fallbackLabel]) {
            foreach ($customer->{$relation} as $contract) {
                $label = self::canonicalizeService($contract->loai_dich_vu) ?: $fallbackLabel;
                $services[$label] ??= ['label' => $label, 'quotations' => 0, 'contracts' => 0];
                $services[$label]['contracts']++;
            }
        }

        $services = array_values($services);
        usort($services, static function (array $left, array $right): int {
            $countComparison = ($right['quotations'] + $right['contracts']) <=> ($left['quotations'] + $left['contracts']);

            return $countComparison !== 0 ? $countComparison : strcasecmp($left['label'], $right['label']);
        });

        return $services;
    }

    private function nullableFormValue(string $field): ?string
    {
        $value = trim((string) ($this->formData[$field] ?? ''));

        return $value !== '' ? $value : null;
    }

    private function contractCountFromDatabase(Customer $customer): int
    {
        return collect(array_keys(self::CONTRACT_RELATIONS))
            ->sum(fn (string $relation): int => $customer->{$relation}()->count());
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formData = [
            'name' => '',
            'tax_code' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'province' => '',
            'ward' => '',
            'industrial_park' => '',
            'representative' => '',
            'contact_person' => '',
            'caretaker_id' => '',
            'sector' => '',
            'appendix' => '',
        ];
        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function customerQuery(): Builder
    {
        $relations = array_keys(self::CONTRACT_RELATIONS);
        $with = ['quotations:id,company_name,service', 'caretaker:id,name'];

        foreach ($relations as $relation) {
            $with[$relation] = fn ($query) => $query->select('id', 'customer_id', 'loai_dich_vu');
        }

        $query = Customer::query()
            ->withCount(array_merge(['quotations'], $relations))
            ->with($with);

        $query = $this->applyCustomerList($query)
            ->when($this->search, function (Builder $query): void {
                $search = trim($this->search);
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('tax_code', 'like', "%{$search}%")
                        ->orWhere('representative', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('ward', 'like', "%{$search}%")
                        ->orWhere('industrial_park', 'like', "%{$search}%")
                        ->orWhere('sector', 'like', "%{$search}%")
                        ->orWhere('appendix', 'like', "%{$search}%")
                        ->orWhereHas('caretaker', fn (Builder $caretakerQuery) => $caretakerQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($this->provinceFilter, fn (Builder $q) => $q->where('province', $this->provinceFilter))
            ->when($this->wardFilter, fn (Builder $q) => $q->where('ward', $this->wardFilter))
            ->when($this->industrialParkFilter, fn (Builder $q) => $q->where('industrial_park', $this->industrialParkFilter))
            ->when($this->sectorFilter, fn (Builder $q) => $q->where('sector', $this->sectorFilter))
            ->when($this->appendixFilter, fn (Builder $q) => $q->where('appendix', $this->appendixFilter))
            ->when($this->caretakerStatusFilter, function (Builder $query): void {
                if ($this->caretakerStatusFilter === 'assigned') {
                    $query->whereNotNull('caretaker_id');
                } elseif ($this->caretakerStatusFilter === 'unassigned') {
                    $query->whereNull('caretaker_id');
                } elseif ($this->caretakerStatusFilter === 'has_contract') {
                    $query->where(function (Builder $sub): void {
                        foreach (array_keys(self::CONTRACT_RELATIONS) as $relation) {
                            $sub->orWhereHas($relation);
                        }
                    });
                } elseif ($this->caretakerStatusFilter === 'has_quotation') {
                    $query->whereHas('quotations');
                } elseif ($this->caretakerStatusFilter === 'has_contact') {
                    $query->where(function (Builder $q): void {
                        $q->where(fn ($sub) => $sub->whereNotNull('phone')->where('phone', '!=', ''))
                            ->orWhere(fn ($sub) => $sub->whereNotNull('email')->where('email', '!=', ''))
                            ->orWhere(fn ($sub) => $sub->whereNotNull('contact_person')->where('contact_person', '!=', ''))
                            ->orWhere(fn ($sub) => $sub->whereNotNull('representative')->where('representative', '!=', ''));
                    });
                } elseif ($this->caretakerStatusFilter === 'no_contact') {
                    $query->where(function (Builder $q): void {
                        $q->where(fn ($sub) => $sub->whereNull('phone')->orWhere('phone', ''))
                            ->where(fn ($sub) => $sub->whereNull('email')->orWhere('email', ''))
                            ->where(fn ($sub) => $sub->whereNull('contact_person')->orWhere('contact_person', ''))
                            ->where(fn ($sub) => $sub->whereNull('representative')->orWhere('representative', ''));
                    });
                } elseif ($this->caretakerStatusFilter === 'no_service') {
                    $query->whereDoesntHave('quotations');
                    foreach (array_keys(self::CONTRACT_RELATIONS) as $relation) {
                        $query->whereDoesntHave($relation);
                    }
                }
            })
            ->when($this->careStatusFilter, fn (Builder $q) => $q->where('care_status', $this->careStatusFilter))
            ->when($this->staffFilter, function (Builder $query): void {
                $staffId = (int) $this->staffFilter;

                if ($this->customerList !== 'all') {
                    $query->where('caretaker_id', $staffId);
                } else {
                    $query->where(function (Builder $q) use ($staffId): void {
                        $q->where('caretaker_id', $staffId)
                            ->orWhereHas('quotations', fn (Builder $quotationQuery) => $quotationQuery->where('staff_id', $staffId));

                        foreach (array_keys(self::CONTRACT_RELATIONS) as $relation) {
                            $q->orWhereHas($relation, fn (Builder $contractQuery) => $contractQuery->where('staff_id', $staffId));
                        }
                    });
                }
            })
            ->when(! empty($this->serviceQuotationFilter), function (Builder $query): void {
                $selectedServices = is_array($this->serviceQuotationFilter)
                    ? $this->serviceQuotationFilter
                    : [$this->serviceQuotationFilter];

                $allMatchingValues = [];
                foreach ($selectedServices as $selectedService) {
                    $selectedService = trim((string) $selectedService);
                    if ($selectedService === '') {
                        continue;
                    }

                    $canonical = self::canonicalizeService($selectedService);
                    foreach (self::getServiceVariants($canonical) as $v) {
                        $allMatchingValues[] = Str::lower($v);
                    }

                    $aliases = [
                        'qtmt' => 'Quan trắc môi trường',
                        'pllđ' => 'Phân loại lao động',
                        'plld' => 'Phân loại lao động',
                        'qtmtld' => 'Quan trắc môi trường lao động',
                    ];

                    foreach ($aliases as $abbr => $full) {
                        if (strcasecmp($full, $canonical) === 0) {
                            foreach (self::getServiceVariants($abbr) as $v) {
                                $allMatchingValues[] = Str::lower($v);
                            }
                        }
                    }
                }

                $allMatchingValues = array_values(array_unique($allMatchingValues));

                if (! empty($allMatchingValues)) {
                    $placeholders = implode(',', array_fill(0, count($allMatchingValues), '?'));
                    $query->whereHas('quotations', fn (Builder $quoteQuery) => $quoteQuery->whereRaw("LOWER(service) IN ($placeholders)", $allMatchingValues));
                }
            })
            ->when($this->serviceContractFilter, function (Builder $query): void {
                $canonical = self::canonicalizeService($this->serviceContractFilter);
                $matchingValues = [];
                foreach (self::getServiceVariants($canonical) as $v) {
                    $matchingValues[] = Str::lower($v);
                }

                $aliases = [
                    'qtmt' => 'Quan trắc môi trường',
                    'pllđ' => 'Phân loại lao động',
                    'plld' => 'Phân loại lao động',
                    'qtmtld' => 'Quan trắc môi trường lao động',
                ];

                foreach ($aliases as $abbr => $full) {
                    if (strcasecmp($full, $canonical) === 0) {
                        foreach (self::getServiceVariants($abbr) as $v) {
                            $matchingValues[] = Str::lower($v);
                        }
                    }
                }

                $matchingValues = array_values(array_unique($matchingValues));
                $placeholders = implode(',', array_fill(0, count($matchingValues), '?'));

                $query->where(function (Builder $q) use ($placeholders, $matchingValues): void {
                    $first = true;
                    foreach (array_keys(self::CONTRACT_RELATIONS) as $relation) {
                        if ($first) {
                            $q->whereHas($relation, fn (Builder $contractQuery) => $contractQuery->whereRaw("LOWER(loai_dich_vu) IN ($placeholders)", $matchingValues));
                            $first = false;
                        } else {
                            $q->orWhereHas($relation, fn (Builder $contractQuery) => $contractQuery->whereRaw("LOWER(loai_dich_vu) IN ($placeholders)", $matchingValues));
                        }
                    }
                });
            });

        $groupColumn = match ($this->groupBy) {
            'ward' => 'ward',
            'industrial_park' => 'industrial_park',
            'none' => null,
            default => 'province',
        };

        if ($groupColumn) {
            $query->orderByRaw("CASE WHEN {$groupColumn} IS NULL OR {$groupColumn} = '' THEN 1 ELSE 0 END")
                ->orderBy($groupColumn);
        }

        return $query->orderBy('name');
    }

    private function applyCustomerList(Builder $query): Builder
    {
        return match ($this->customerList) {
            'ghg_inventory' => $query->where('is_ghg_inventory', true),
            'energy_audit' => $query->where('is_energy_audit', true),
            default => $query->where('is_ghg_inventory', false)->where('is_energy_audit', false),
        };
    }

    private function findScopedCustomer(int $id): Customer
    {
        return $this->applyCustomerList(Customer::query())->findOrFail($id);
    }

    private function distinctValues(string $column, ?Builder $query = null): Collection
    {
        return ($query ?? Customer::query())
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    private function serviceQuotationOptions(): Collection
    {
        $customerNamesWithContracts = Customer::query()
            ->where(function (Builder $query) {
                foreach (array_keys(self::CONTRACT_RELATIONS) as $relation) {
                    $query->orWhereHas($relation);
                }
            })
            ->pluck('name')
            ->all();

        return Quotation::query()
            ->whereIn('company_name', $customerNamesWithContracts)
            ->whereNotNull('service')
            ->where('service', '!=', '')
            ->distinct()
            ->pluck('service')
            ->map(fn ($service) => self::canonicalizeService($service))
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function serviceContractOptions(): Collection
    {
        $services = collect();

        foreach (self::CONTRACT_RELATIONS as [$model]) {
            $services = $services->merge(
                $model::query()
                    ->whereNotNull('loai_dich_vu')
                    ->where('loai_dich_vu', '!=', '')
                    ->distinct()
                    ->pluck('loai_dich_vu')
            );
        }

        return $services
            ->map(fn ($service) => self::canonicalizeService($service))
            ->filter()
            ->unique()
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function staffOptions(): Collection
    {
        $staffIds = Quotation::query()
            ->whereNotNull('staff_id')
            ->distinct()
            ->pluck('staff_id');

        foreach (self::CONTRACT_RELATIONS as [$model]) {
            $staffIds = $staffIds->merge(
                $model::query()
                    ->whereNotNull('staff_id')
                    ->distinct()
                    ->pluck('staff_id')
            );
        }

        return User::query()
            ->whereIn('id', $staffIds->merge(Customer::query()->whereNotNull('caretaker_id')->pluck('caretaker_id'))->unique()->values())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function caretakerOptions(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', Role::salesRoles()))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public static function normalizeVietnameseAccents(string $str): string
    {
        $map = [
            "\xc5\xa9y" => "u\xe1\xbb\xb9", // ũy -> uỹ
            "\xc5\xa8y" => "U\xe1\xbb\xb9", // Ũy -> Uỹ
            "\xc5\xa8Y" => "U\xe1\xbb\xb8", // ŨY -> UỸ

            "\xc3\xbay" => "u\xc3\xbd",     // úy -> uý
            "\xc3\x9ay" => "U\xc3\xbd",     // Úy -> Uý
            "\xc3\x9aY" => "U\xc3\x9d",     // ÚY -> UÝ

            "\xc3\xb9y" => "u\xe1\xbb\xb3", // ùy -> uỳ
            "\xc3\x99y" => "U\xe1\xbb\xb3", // Ùy -> Uỳ
            "\xc3\x99Y" => "U\xe1\xbb\xb2", // ÙY -> UỲ

            "\xe1\xbb\xa7y" => "u\xe1\xbb\xb7", // ủy -> uỷ
            "\xe1\xbb\xa6y" => "U\xe1\xbb\xb7", // Ủy -> Uỷ
            "\xe1\xbb\xa6Y" => "U\xe1\xbb\xb6", // ỦY -> UỶ

            "\xe1\xbb\xa5y" => "u\xe1\xbb\xb5", // cụy -> uỵ
            "\xe1\xbb\xa4y" => "U\xe1\xbb\xb5", // Ụy -> Uỵ
            "\xe1\xbb\xa4Y" => "U\xe1\xbb\xb4", // ỤY -> UỴ
        ];

        return str_replace(array_keys($map), array_values($map), $str);
    }

    public static function getServiceVariants(string $service): array
    {
        $variants = [$service];

        $reverseMap = [
            "u\xe1\xbb\xb9" => "\xc5\xa9y", // uỹ -> ũy
            "U\xe1\xbb\xb9" => "\xc5\xa8y", // Uỹ -> Ũy
            "U\xe1\xbb\xb8" => "\xc5\xa8Y", // UỸ -> ŨY

            "u\xc3\xbd" => "\xc3\xbay", // uý -> úy
            "U\xc3\xbd" => "\xc3\x9ay", // Uý -> Úy
            "U\xc3\x9d" => "\xc3\x9aY", // UÝ -> ÚY

            "u\xe1\xbb\xb3" => "\xc3\xb9y", // uỳ -> ùy
            "U\xe1\xbb\xb3" => "\xc3\x99y", // Uỳ -> Ùy
            "U\xe1\xbb\xb2" => "\xc3\x99Y", // UỲ -> ÙY

            "u\xe1\xbb\xb7" => "\xe1\xbb\xa7y", // uỷ -> ủy
            "U\xe1\xbb\xb7" => "\xe1\xbb\xa6y", // Uỷ -> Ủy
            "U\xe1\xbb\xb6" => "\xe1\xbb\xa6Y", // UỶ -> ỦY

            "u\xe1\xbb\xb5" => "\xe1\xbb\xa5y", // uỵ -> cụy
            "U\xe1\xbb\xb5" => "\xe1\xbb\xa4y", // Uỵ -> Ụy
            "U\xe1\xbb\xb4" => "\xe1\xbb\xa4Y", // UỴ -> ỤY
        ];

        $alt = str_replace(array_keys($reverseMap), array_values($reverseMap), $service);
        if ($alt !== $service) {
            $variants[] = $alt;
        }

        $norm = self::normalizeVietnameseAccents($service);
        if ($norm !== $service && ! in_array($norm, $variants, true)) {
            $variants[] = $norm;
        }

        return $variants;
    }

    public static function canonicalizeService(?string $service): string
    {
        $service = trim((string) $service);
        if ($service === '') {
            return '';
        }

        $service = self::normalizeVietnameseAccents($service);
        $lower = Str::lower($service);

        $aliases = [
            'qtmt' => 'Quan trắc môi trường',
            'pllđ' => 'Phân loại lao động',
            'plld' => 'Phân loại lao động',
            'qtmtld' => 'Quan trắc môi trường lao động',
            'qtmt và bcctbvmt' => 'BCCTBVMT',
        ];

        if (array_key_exists($lower, $aliases)) {
            return $aliases[$lower];
        }

        return Str::ucfirst($service);
    }

    private function summary(Collection $customerIds): array
    {
        $ids = $customerIds->all();
        $customerNames = Customer::query()->whereKey($ids)->pluck('name');
        $contractCount = 0;

        foreach (self::CONTRACT_RELATIONS as [$model]) {
            $contractCount += $model::query()->whereIn('customer_id', $ids)->count();
        }

        $groupColumn = match ($this->groupBy) {
            'ward' => 'ward',
            'industrial_park' => 'industrial_park',
            default => 'province',
        };

        return [
            'customers' => $customerIds->count(),
            'quotations' => Quotation::query()->whereIn('company_name', $customerNames)->count(),
            'contracts' => $contractCount,
            'groups' => Customer::query()
                ->whereKey($ids)
                ->whereNotNull($groupColumn)
                ->where($groupColumn, '!=', '')
                ->distinct()
                ->count($groupColumn),
        ];
    }

    public function render()
    {
        abort_unless(auth()->user()->can($this->viewPermission()->value), 403);

        $query = $this->customerQuery();
        $summaryQuery = clone $query;
        $customerIds = $summaryQuery
            ->setEagerLoads([])
            ->reorder()
            ->pluck('customers.id');

        $wardQuery = $this->applyCustomerList(Customer::query())
            ->when($this->provinceFilter, fn (Builder $q) => $q->where('province', $this->provinceFilter));
        $industrialParkQuery = $this->applyCustomerList(Customer::query())
            ->when($this->provinceFilter, fn (Builder $q) => $q->where('province', $this->provinceFilter))
            ->when($this->wardFilter, fn (Builder $q) => $q->where('ward', $this->wardFilter));

        $hasAdvancedFilters = filled($this->wardFilter)
            || filled($this->staffFilter)
            || filled($this->caretakerStatusFilter)
            || filled($this->careStatusFilter)
            || filled($this->sectorFilter)
            || filled($this->appendixFilter)
            || filled($this->serviceContractFilter)
            || ! empty($this->serviceQuotationFilter)
            || filled($this->groupBy) && $this->groupBy !== 'province';

        return view('livewire.admin.customers.customer-manager', [
            'customers' => $query->paginate(15),
            'provinces' => VietnamProvinces::list(),
            'filterProvinces' => $this->provincesForCurrentList(),
            'wards' => $this->distinctValues('ward', $wardQuery),
            'industrialParks' => $this->distinctValues('industrial_park', $industrialParkQuery),
            'sectorOptions' => $this->distinctValues('sector', $this->applyCustomerList(Customer::query())),
            'appendixOptions' => $this->distinctValues('appendix', $this->applyCustomerList(Customer::query())),
            'serviceQuotationOptions' => $this->serviceQuotationOptions(),
            'serviceContractOptions' => $this->serviceContractOptions(),
            'staffOptions' => $this->staffOptions(),
            'caretakerOptions' => $this->caretakerOptions(),
            'careStatusOptions' => CustomerCareStatus::options(),
            'summary' => $this->summary($customerIds),
            'hasAdvancedFilters' => $hasAdvancedFilters,
            'directoryTitle' => $this->directoryTitle(),
            'directoryDescription' => $this->directoryDescription(),
            'isCustomerListDirectory' => $this->isCustomerListDirectory(),
            'entityLabel' => 'khách hàng',
            'canCreate' => ($permission = $this->createPermission()) && auth()->user()->can($permission->value),
            'canEdit' => auth()->user()->can($this->editPermission()->value),
            'canDelete' => ($permission = $this->deletePermission()) && auth()->user()->can($permission->value),
            'canNormalize' => ! $this->isCustomerListDirectory() && auth()->user()->can(Permission::CUSTOMERS_EDIT->value),
        ])->layout('admin.layouts.app');
    }

    private function provincesForCurrentList(): Collection
    {
        return $this->applyCustomerList(Customer::query())
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');
    }
}
