<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\Consulting\ConsultingAchievementReport;
use App\Livewire\Admin\Reports\Technical\TechnicalAchievementReport;
use App\Models\ContractAssignment;
use App\Models\ContractLegal;
use App\Models\ContractWorkflowStep;
use App\Models\Customer;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractAchievementReportConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_rankings_count_payment_rows_with_the_same_bao_chau_number_once(): void
    {
        $department = Department::create([
            'name' => 'Tư vấn - Kỹ thuật',
            'slug' => 'tu-van-ky-thuat',
            'is_active' => true,
        ]);
        $customer = Customer::create(['name' => 'Khách hàng kiểm tra bảng xếp hạng']);

        foreach ([
            [RoleEnum::TU_VAN, ConsultingAchievementReport::class, 'TV'],
            [RoleEnum::KY_THUAT, TechnicalAchievementReport::class, 'KT'],
        ] as [$roleEnum, $component, $suffix]) {
            $role = Role::findOrCreate($roleEnum->value);
            $staff = User::factory()->create([
                'department_id' => $department->id,
                'is_active' => true,
            ]);
            $staff->assignRole($role);

            $contracts = collect([45_000_000, 55_000_000])->map(function (int $value) use ($customer, $department, $staff, $suffix) {
                $contract = ContractLegal::create([
                    'shd_bc' => "01/2026/HĐBC-{$suffix}",
                    'customer_id' => $customer->id,
                    'staff_id' => $staff->id,
                    'department_id' => $department->id,
                    'value' => $value,
                    'signed_at' => '2026-02-10',
                ]);

                ContractAssignment::create([
                    'assignable_type' => ContractLegal::class,
                    'assignable_id' => $contract->id,
                    'user_id' => $staff->id,
                    'assigned_by' => $staff->id,
                ]);

                return $contract;
            });

            ContractWorkflowStep::create([
                'contract_type' => ContractLegal::class,
                'contract_id' => $contracts->last()->id,
                'user_id' => $staff->id,
                'step_name' => 'finished',
                'action' => 'complete',
            ]);

            $this->actingAs($staff);

            Livewire::test($component)
                ->set('year', 2026)
                ->assertViewHas('rateRankings', fn ($rankings): bool => $rankings->contains(
                    fn (array $row): bool => $row['user_id'] === $staff->id
                        && $row['total'] === 1
                        && $row['finished'] === 1
                        && $row['pct'] === 100
                ));
        }
    }
}
