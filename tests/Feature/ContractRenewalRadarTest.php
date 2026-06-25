<?php

namespace Tests\Feature;

use App\Enums\ContractRenewalStatus;
use App\Enums\Role as RoleEnum;
use App\Models\ContractLegal;
use App\Models\Customer;
use App\Models\Department;
use App\Models\User;
use App\Support\ContractRenewalRadar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ContractRenewalRadarTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        Carbon::setTestNow('2026-06-25 08:00:00');

        $this->department = Department::create([
            'name' => 'Kinh doanh',
            'slug' => 'kinh-doanh',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'ACME Factory',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_sales_user_sees_own_contract_signed_anniversary_within_30_days(): void
    {
        $salesUser = $this->createUser(RoleEnum::KINH_DOANH);
        $otherSalesUser = $this->createUser(RoleEnum::KINH_DOANH);

        $visibleContract = $this->createContract($salesUser, '2025-07-20');
        $this->createContract($salesUser, '2025-08-01');
        $this->createContract($salesUser, '2025-07-10', ContractRenewalStatus::DA_TAI_KY->value);
        $this->createContract($otherSalesUser, '2025-07-18');

        $contracts = ContractRenewalRadar::visibleFor($salesUser);

        $this->assertCount(1, $contracts);
        $this->assertSame($visibleContract->id, $contracts->first()['id']);
        $this->assertSame(25, $contracts->first()['days_left']);
        $this->assertSame('20/07/2026', $contracts->first()['renewal_date']->format('d/m/Y'));
    }

    public function test_sales_manager_sees_sales_team_contracts(): void
    {
        $manager = $this->createUser(RoleEnum::TP_KINH_DOANH);
        $salesUser = $this->createUser(RoleEnum::KINH_DOANH);
        $technicalUser = $this->createUser(RoleEnum::KY_THUAT);

        $managerContract = $this->createContract($manager, '2025-07-15');
        $salesContract = $this->createContract($salesUser, '2025-07-20');
        $this->createContract($technicalUser, '2025-07-18');

        $contractIds = ContractRenewalRadar::visibleFor($manager)->pluck('id')->all();

        $this->assertContains($managerContract->id, $contractIds);
        $this->assertContains($salesContract->id, $contractIds);
        $this->assertCount(2, $contractIds);
    }

    private function createUser(RoleEnum $role): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'department_id' => $this->department->id,
        ]);
        $user->assignRole($role->value);

        return $user;
    }

    private function createContract(User $staff, string $signedAt, ?string $renewalStatus = null): ContractLegal
    {
        return ContractLegal::create([
            'customer_id' => $this->customer->id,
            'staff_id' => $staff->id,
            'department_id' => $this->department->id,
            'signed_at' => $signedAt,
            'value' => 12000000,
            'renewal_status' => $renewalStatus ?? ContractRenewalStatus::CHUA_DEN_HAN->value,
            'is_renewal' => false,
        ]);
    }
}
