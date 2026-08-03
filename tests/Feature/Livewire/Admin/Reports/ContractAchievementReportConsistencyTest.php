<?php

namespace Tests\Feature\Livewire\Admin\Reports;

use App\Enums\Role as RoleEnum;
use App\Livewire\Admin\Reports\Consulting\ConsultingAchievementReport;
use App\Livewire\Admin\Reports\Consulting\ConsultingContractReport;
use App\Livewire\Admin\Reports\Consulting\ConsultingGeneralReport;
use App\Livewire\Admin\Reports\Consulting\ConsultingMonitoringReport;
use App\Livewire\Admin\Reports\Consulting\ConsultingServiceReport;
use App\Livewire\Admin\Reports\Technical\TechnicalAchievementReport;
use App\Livewire\Admin\Reports\Technical\TechnicalContractReport;
use App\Livewire\Admin\Reports\Technical\TechnicalFieldReport;
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
                        && $row['pct'] === 100.0
                ));
        }
    }

    public function test_consulting_and_technical_reports_use_the_same_business_contract_count(): void
    {
        $department = Department::create([
            'name' => 'Báo cáo tư vấn - kỹ thuật',
            'slug' => 'bao-cao-tu-van-ky-thuat',
            'is_active' => true,
        ]);
        $director = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $director->assignRole(Role::findOrCreate(RoleEnum::GIAM_DOC->value));

        $consultant = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $consultant->assignRole(Role::findOrCreate(RoleEnum::TU_VAN->value));

        $technical = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => true,
        ]);
        $technical->assignRole(Role::findOrCreate(RoleEnum::KY_THUAT->value));

        $customer = Customer::create(['name' => 'Khách hàng đồng bộ báo cáo']);
        $contracts = collect([
            ['value' => 45_000_000, 'status' => 'ĐANG THỰC HIỆN'],
            ['value' => 55_000_000, 'status' => 'HOÀN THÀNH'],
        ])->map(function (array $data) use ($customer, $department, $consultant, $technical, $director) {
            $contract = ContractLegal::create([
                'shd_bc' => '02/2026/HĐBC-REPORT',
                'customer_id' => $customer->id,
                'staff_id' => $director->id,
                'consultant_id' => $consultant->id,
                'department_id' => $department->id,
                'loai_dich_vu' => 'Quan trắc môi trường',
                'value' => $data['value'],
                'status' => $data['status'],
                'signed_at' => '2026-03-10',
            ]);

            foreach ([$consultant, $technical] as $assignee) {
                ContractAssignment::create([
                    'assignable_type' => ContractLegal::class,
                    'assignable_id' => $contract->id,
                    'user_id' => $assignee->id,
                    'assigned_by' => $director->id,
                ]);
            }

            return $contract;
        });

        ContractWorkflowStep::create([
            'contract_type' => ContractLegal::class,
            'contract_id' => $contracts->last()->id,
            'user_id' => $technical->id,
            'step_name' => 'finished',
            'action' => 'complete',
        ]);

        $this->actingAs($director);

        foreach ([ConsultingContractReport::class, TechnicalContractReport::class] as $component) {
            Livewire::test($component)
                ->set('year', 2026)
                ->set('contract_type', 'consulting')
                ->assertViewHas('items', fn ($items): bool => $items->total() === 1)
                ->assertViewHas('summary', fn ($summary): bool => (int) $summary->total === 1
                    && (int) $summary->completed === 1
                    && (int) $summary->active === 0);
        }

        Livewire::test(ConsultingGeneralReport::class)
            ->set('year', 2026)
            ->assertViewHas('totals', fn (array $totals): bool => $totals['count'] === 1
                && $totals['completed'] === 1
                && $totals['active'] === 0
                && $totals['value'] === 100_000_000.0);

        foreach ([ConsultingServiceReport::class, ConsultingMonitoringReport::class] as $component) {
            Livewire::test($component)
                ->set('year', 2026)
                ->assertViewHas('items', fn ($items): bool => $items->total() === 1)
                ->assertViewHas('summary', fn ($summary): bool => (int) $summary->count === 1
                    && (float) $summary->total_value === 100_000_000.0);
        }

        Livewire::test(TechnicalFieldReport::class)
            ->set('year', 2026)
            ->assertViewHas('items', fn ($items): bool => $items->total() === 1)
            ->assertViewHas('summary', fn ($summary): bool => (int) $summary->total === 1
                && (float) $summary->total_value === 100_000_000.0);
    }
}
