<?php

namespace App\Services\Reports;

use App\Enums\QuotationStatus;
use App\Enums\Role;
use App\Models\ContractEmission;
use App\Models\ContractLegal;
use App\Models\ContractResearch;
use App\Models\ContractSustainability;
use App\Models\ContractTechnical;
use App\Models\ContractWaste;
use App\Models\Quotation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

final class PotentialTrendsReportService
{
    /** @var array<class-string, string> */
    private const CONTRACT_MODELS = [
        ContractWaste::class => 'Chất thải và tiếng ồn',
        ContractLegal::class => 'Pháp lý và hồ sơ môi trường',
        ContractTechnical::class => 'Kỹ thuật và ứng phó sự cố',
        ContractResearch::class => 'Nghiên cứu và chuyển đổi công nghệ',
        ContractSustainability::class => 'Tư vấn và báo cáo phát triển bền vững',
        ContractEmission::class => 'Phát thải và năng lượng',
    ];

    /** @var list<string> */
    private const PIPELINE_STATUSES = [
        QuotationStatus::DANG_THEO_DOI->value,
        QuotationStatus::HEN_BAO_GIA->value,
        QuotationStatus::BAO_GIA_TIEM_NANG->value,
    ];

    public function canViewAllStaff(User $viewer): bool
    {
        return $viewer->hasAnyRole([
            Role::IT->value,
            Role::GIAM_DOC->value,
            Role::TP_KINH_DOANH->value,
        ]);
    }

    /**
     * @param  array{date_from:string,date_to:string,staff_id:?int,service:string,province:string,status:string}  $filters
     * @return array<string, mixed>
     */
    public function build(User $viewer, array $filters): array
    {
        $filters = $this->normalizeFilters($viewer, $filters);
        $cacheKey = 'potential-report:v2:'.sha1(json_encode([
            'viewer' => $viewer->id,
            'can_view_all' => $this->canViewAllStaff($viewer),
            'filters' => $filters,
        ], JSON_THROW_ON_ERROR));

        return Cache::remember(
            $cacheKey,
            max(1, (int) config('analytics.potential_report.cache_ttl_seconds', 300)),
            fn (): array => $this->calculate($viewer, $filters),
        );
    }

    /**
     * @return array{staffs:array<int, array{id:int,name:string}>,services:list<string>,provinces:list<string>,statuses:list<string>}
     */
    public function filterOptions(User $viewer): array
    {
        $staffId = $this->canViewAllStaff($viewer) ? null : (int) $viewer->id;
        $services = Quotation::query()
            ->when($staffId, fn (Builder $query) => $query->where('staff_id', $staffId))
            ->whereNotNull('service')
            ->whereRaw("TRIM(service) <> ''")
            ->distinct()
            ->orderBy('service')
            ->pluck('service')
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->values()
            ->all();
        $provinces = Quotation::query()
            ->when($staffId, fn (Builder $query) => $query->where('staff_id', $staffId))
            ->whereNotNull('province')
            ->whereRaw("TRIM(province) <> ''")
            ->distinct()
            ->orderBy('province')
            ->pluck('province')
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $contractServices = $modelClass::query()
                ->when($staffId, fn (Builder $query) => $query->where('staff_id', $staffId))
                ->whereNotNull('loai_dich_vu')
                ->whereRaw("TRIM(loai_dich_vu) <> ''")
                ->distinct()
                ->pluck('loai_dich_vu')
                ->map(fn ($value): string => trim((string) $value))
                ->all();
            $contractProvinces = $modelClass::query()
                ->when($staffId, fn (Builder $query) => $query->where('staff_id', $staffId))
                ->whereNotNull('province')
                ->whereRaw("TRIM(province) <> ''")
                ->distinct()
                ->pluck('province')
                ->map(fn ($value): string => trim((string) $value))
                ->all();
            $services = array_merge($services, $contractServices);
            $provinces = array_merge($provinces, $contractProvinces);
        }

        $staffIds = Quotation::query()->whereNotNull('staff_id')->distinct()->pluck('staff_id');
        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $staffIds = $staffIds->merge($modelClass::query()->whereNotNull('staff_id')->distinct()->pluck('staff_id'));
        }
        $staffs = $this->canViewAllStaff($viewer)
            ? User::whereIn('id', $staffIds->unique())
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user): array => ['id' => $user->id, 'name' => $user->name])
                ->all()
            : [['id' => $viewer->id, 'name' => $viewer->name]];

        $statuses = Quotation::query()
            ->whereNotNull('status')
            ->whereRaw("TRIM(status) <> ''")
            ->distinct()
            ->pluck('status')
            ->map(fn ($value): string => trim((string) $value))
            ->merge(QuotationStatus::values())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return [
            'staffs' => $staffs,
            'services' => collect($services)->filter()->unique()->sort()->values()->all(),
            'provinces' => collect($provinces)->filter()->unique()->sort()->values()->all(),
            'statuses' => $statuses,
        ];
    }

    /**
     * @param  array{date_from:string,date_to:string,staff_id:?int,service:string,province:string,status:string}  $filters
     */
    public function exportRows(User $viewer, array $filters): LazyCollection
    {
        $filters = $this->normalizeFilters($viewer, $filters);

        $query = Quotation::query()
            ->leftJoin('users', 'users.id', '=', 'quotations.staff_id')
            ->whereBetween('quotations.date', [$filters['date_from'], $filters['date_to']])
            ->when($filters['staff_id'], fn ($query, int $staffId) => $query->where('quotations.staff_id', $staffId))
            ->when($filters['service'] !== '', fn ($query) => $query->where('quotations.service', $filters['service']))
            ->when($filters['province'] !== '', fn ($query) => $query->where('quotations.province', $filters['province']))
            ->when($filters['status'] !== '', fn ($query) => $query->where('quotations.status', $filters['status']))
            ->select([
                'quotations.id',
                'quotations.date',
                'quotations.company_name',
                'quotations.service',
                'quotations.province',
                'quotations.status',
                'quotations.total_value',
                'quotations.value_inc_vat',
                'quotations.original_value',
                'users.name as staff_name',
            ]);

        return $query->lazyById(500, 'quotations.id', 'id');
    }

    /**
     * @param  array{date_from:string,date_to:string,staff_id:?int,service:string,province:string,status:string}  $filters
     * @return array<string, mixed>
     */
    private function calculate(User $viewer, array $filters): array
    {
        $from = CarbonImmutable::parse($filters['date_from'])->startOfDay();
        $to = CarbonImmutable::parse($filters['date_to'])->endOfDay();
        $periodDays = $from->diffInDays($to) + 1;
        $previousTo = $from->subDay()->endOfDay();
        $previousFrom = $previousTo->subDays($periodDays - 1)->startOfDay();

        $currentQuery = $this->quotationQuery($filters, $from, $to);
        $previousQuery = $this->quotationQuery($filters, $previousFrom, $previousTo);
        $currentMetrics = $this->quotationMetrics(clone $currentQuery);
        $previousMetrics = $this->quotationMetrics(clone $previousQuery);
        $currentMetrics += $this->customerMetrics(clone $currentQuery, $from, $to);
        $previousMetrics += $this->customerMetrics(clone $previousQuery, $previousFrom, $previousTo);

        $contractMetrics = $this->contractMetrics($filters, $from, $to);
        $previousContractMetrics = $this->contractMetrics($filters, $previousFrom, $previousTo);

        $kpis = [
            'opportunities' => $this->metricWithGrowth($currentMetrics['opportunities'], $previousMetrics['opportunities']),
            'customers' => $this->metricWithGrowth($currentMetrics['customers'], $previousMetrics['customers']),
            'new_customers' => $this->metricWithGrowth($currentMetrics['new_customers'], $previousMetrics['new_customers']),
            'conversion_rate' => $this->metricWithGrowth($currentMetrics['conversion_rate'], $previousMetrics['conversion_rate']),
            'potential_value' => $this->metricWithGrowth($currentMetrics['potential_value'], $previousMetrics['potential_value']),
            'revenue' => $this->metricWithGrowth($contractMetrics['revenue'], $previousContractMetrics['revenue']),
            'signed_contracts' => $this->metricWithGrowth($contractMetrics['signed_contracts'], $previousContractMetrics['signed_contracts']),
            'returning_customers' => $this->metricWithGrowth($currentMetrics['returning_customers'], $previousMetrics['returning_customers']),
        ];

        $services = $this->serviceAnalysis($filters, $from, $to, $previousFrom, $previousTo);
        $regions = $this->regionAnalysis($filters, $from, $to);
        $staffPerformance = $this->staffPerformance($filters, $from, $to, $previousFrom, $previousTo);
        $dataQuality = $this->dataQuality($filters, $from, $to);

        return [
            'scope' => [
                'can_view_all_staff' => $this->canViewAllStaff($viewer),
                'staff_id' => $filters['staff_id'],
            ],
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'previous_from' => $previousFrom->toDateString(),
                'previous_to' => $previousTo->toDateString(),
                'days' => $periodDays,
            ],
            'kpis' => $kpis,
            'quotation_metrics' => $currentMetrics,
            'contract_metrics' => $contractMetrics,
            'trend' => $this->trend($filters, $from, $to),
            'status_breakdown' => $this->statusBreakdown(clone $currentQuery),
            'services' => $services,
            'regions' => $regions,
            'staff_performance' => $staffPerformance,
            'recommendations' => $this->recommendations($kpis, $services, $regions, $currentMetrics),
            'data_quality' => $dataQuality,
            'recent_opportunities' => $this->recentOpportunities(clone $currentQuery),
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    /** @return array<string, int|float> */
    private function quotationMetrics(Builder $query): array
    {
        $lostStatuses = array_merge(
            [QuotationStatus::ROT_BAO_GIA->value],
            (array) config('analytics.potential_report.legacy_lost_quotation_statuses', []),
        );
        $lostPlaceholders = implode(',', array_fill(0, count($lostStatuses), '?'));
        $pipelinePlaceholders = implode(',', array_fill(0, count(self::PIPELINE_STATUSES), '?'));
        $valueExpression = $this->quotationValueExpression();
        $bindings = [
            QuotationStatus::KY_HOP_DONG->value,
            ...$lostStatuses,
            ...self::PIPELINE_STATUSES,
        ];

        $row = $query->selectRaw(
            "COUNT(*) as opportunities,
             COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as won,
             COALESCE(SUM(CASE WHEN status IN ({$lostPlaceholders}) THEN 1 ELSE 0 END), 0) as lost,
             COALESCE(SUM(CASE WHEN status IN ({$pipelinePlaceholders}) THEN 1 ELSE 0 END), 0) as pipeline,
             COALESCE(SUM(CASE WHEN status IN ({$pipelinePlaceholders}) THEN {$valueExpression} ELSE 0 END), 0) as potential_value",
            [...$bindings, ...self::PIPELINE_STATUSES],
        )->first();

        $opportunities = (int) ($row?->opportunities ?? 0);
        $won = (int) ($row?->won ?? 0);

        return [
            'opportunities' => $opportunities,
            'won' => $won,
            'lost' => (int) ($row?->lost ?? 0),
            'pipeline' => (int) ($row?->pipeline ?? 0),
            'potential_value' => (float) ($row?->potential_value ?? 0),
            'conversion_rate' => $opportunities > 0 ? round(($won / $opportunities) * 100, 2) : 0.0,
        ];
    }

    /** @return array{customers:int,new_customers:int,returning_customers:int} */
    private function customerMetrics(Builder $query, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $currentCustomers = (clone $query)
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->selectRaw('TRIM(company_name) as company_name')
            ->groupByRaw('TRIM(company_name)');

        $firstQuoteDates = Quotation::query()
            ->whereNotNull('company_name')
            ->whereRaw("TRIM(company_name) <> ''")
            ->whereNotNull('date')
            ->selectRaw('TRIM(company_name) as company_name, MIN(date) as first_date')
            ->groupByRaw('TRIM(company_name)');

        $customers = DB::query()->fromSub(clone $currentCustomers, 'current_customers')->count();
        $newCustomers = DB::query()
            ->fromSub($currentCustomers, 'current_customers')
            ->joinSub($firstQuoteDates, 'first_quotes', 'first_quotes.company_name', '=', 'current_customers.company_name')
            ->whereBetween('first_quotes.first_date', [$from->toDateString(), $to->toDateString()])
            ->count();

        return [
            'customers' => $customers,
            'new_customers' => $newCustomers,
            'returning_customers' => max(0, $customers - $newCustomers),
        ];
    }

    /** @return array{signed_contracts:int,contract_value:float,revenue:float} */
    private function contractMetrics(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $metrics = ['signed_contracts' => 0, 'contract_value' => 0.0, 'revenue' => 0.0];

        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $signed = $this->contractQuery($modelClass, $filters, 'signed_at', $from, $to)
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(value), 0) as contract_value')
                ->first();
            $metrics['signed_contracts'] += (int) ($signed?->total ?? 0);
            $metrics['contract_value'] += (float) ($signed?->contract_value ?? 0);
            $metrics['revenue'] += (float) $this->contractQuery($modelClass, $filters, 'submitted_at', $from, $to)
                ->whereNotNull('submitted_at')
                ->sum('revenue');
        }

        return $metrics;
    }

    /** @return list<array<string, int|float|string|null>> */
    private function serviceAnalysis(
        array $filters,
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $previousFrom,
        CarbonImmutable $previousTo,
    ): array {
        $current = $this->groupedQuotationAnalysis($filters, $from, $to, 'service');
        $previous = collect($this->groupedQuotationAnalysis($filters, $previousFrom, $previousTo, 'service'))->keyBy('label');
        $maxOpportunities = max(1, (int) collect($current)->max('opportunities'));
        $maxPotential = max(1.0, (float) collect($current)->max('potential_value'));
        $weights = (array) config('analytics.potential_report.service_score_weights', []);

        return collect($current)->map(function (array $row) use ($previous, $maxOpportunities, $maxPotential, $weights): array {
            $previousCount = (int) ($previous->get($row['label'])['opportunities'] ?? 0);
            $growth = $this->growth((float) $row['opportunities'], (float) $previousCount);
            $growthScore = $growth === null ? 0.0 : max(0.0, min(1.0, ($growth + 100) / 200));
            $score =
                ($row['opportunities'] / $maxOpportunities) * ($weights['opportunities'] ?? 30)
                + ($row['potential_value'] / $maxPotential) * ($weights['potential_value'] ?? 30)
                + ($row['conversion_rate'] / 100) * ($weights['conversion_rate'] ?? 25)
                + $growthScore * ($weights['growth'] ?? 15);

            return $row + [
                'growth' => $growth,
                'score' => round(max(0, min(100, $score)), 1),
            ];
        })->sortByDesc('score')->take(10)->values()->all();
    }

    /** @return list<array<string, int|float|string|null>> */
    private function regionAnalysis(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rows = collect($this->groupedQuotationAnalysis($filters, $from, $to, 'province'))->keyBy('label');
        $revenueByRegion = [];

        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $contractRows = $this->contractQuery($modelClass, $filters, 'submitted_at', $from, $to)
                ->whereNotNull('submitted_at')
                ->selectRaw("COALESCE(NULLIF(TRIM(province), ''), 'Chưa xác định') as label, COALESCE(SUM(revenue), 0) as revenue")
                ->groupBy('label')
                ->get();
            foreach ($contractRows as $contractRow) {
                $label = (string) $contractRow->label;
                $revenueByRegion[$label] = ($revenueByRegion[$label] ?? 0) + (float) $contractRow->revenue;
            }
        }

        foreach ($revenueByRegion as $label => $revenue) {
            if (! $rows->has($label)) {
                $rows->put($label, [
                    'label' => $label,
                    'opportunities' => 0,
                    'won' => 0,
                    'potential_value' => 0.0,
                    'conversion_rate' => 0.0,
                ]);
            }
        }

        $maxOpportunities = max(1, (int) $rows->max('opportunities'));
        $maxPotential = max(1.0, (float) $rows->max('potential_value'));
        $maxRevenue = max(1.0, (float) collect($revenueByRegion)->max());
        $weights = (array) config('analytics.potential_report.region_score_weights', []);

        return $rows->map(function (array $row) use ($revenueByRegion, $maxOpportunities, $maxPotential, $maxRevenue, $weights): array {
            $revenue = (float) ($revenueByRegion[$row['label']] ?? 0);
            $score =
                ($row['opportunities'] / $maxOpportunities) * ($weights['opportunities'] ?? 35)
                + ($row['potential_value'] / $maxPotential) * ($weights['potential_value'] ?? 25)
                + ($row['conversion_rate'] / 100) * ($weights['conversion_rate'] ?? 20)
                + ($revenue / $maxRevenue) * ($weights['revenue'] ?? 20);

            return $row + [
                'revenue' => $revenue,
                'score' => round(max(0, min(100, $score)), 1),
            ];
        })->sortByDesc('score')->take(10)->values()->all();
    }

    /** @return list<array<string, int|float|string|null>> */
    private function staffPerformance(
        array $filters,
        CarbonImmutable $from,
        CarbonImmutable $to,
        CarbonImmutable $previousFrom,
        CarbonImmutable $previousTo,
    ): array {
        $current = collect($this->groupedStaffQuotationAnalysis($filters, $from, $to))->keyBy('staff_id');
        $previous = collect($this->groupedStaffQuotationAnalysis($filters, $previousFrom, $previousTo))->keyBy('staff_id');
        $currentRevenue = $this->revenueByStaff($filters, $from, $to);
        $previousRevenue = $this->revenueByStaff($filters, $previousFrom, $previousTo);
        $staffIds = $current->keys()->merge(array_keys($currentRevenue))->filter()->unique()->values();
        $users = User::whereIn('id', $staffIds)->pluck('name', 'id');

        return $staffIds->map(function ($staffId) use ($current, $previous, $currentRevenue, $previousRevenue, $users): array {
            $row = $current->get($staffId, [
                'opportunities' => 0,
                'won' => 0,
                'conversion_rate' => 0.0,
            ]);
            $previousRow = $previous->get($staffId, ['opportunities' => 0]);
            $revenue = (float) ($currentRevenue[$staffId] ?? 0);
            $oldRevenue = (float) ($previousRevenue[$staffId] ?? 0);

            return [
                'staff_id' => (int) $staffId,
                'name' => (string) ($users[$staffId] ?? 'Nhân sự không còn hoạt động'),
                'opportunities' => (int) $row['opportunities'],
                'won' => (int) $row['won'],
                'conversion_rate' => (float) $row['conversion_rate'],
                'revenue' => $revenue,
                'opportunity_growth' => $this->growth((float) $row['opportunities'], (float) $previousRow['opportunities']),
                'revenue_growth' => $this->growth($revenue, $oldRevenue),
            ];
        })->sortByDesc('revenue')->values()->all();
    }

    /** @return array{labels:list<string>,opportunities:list<int>,won:list<int>,revenue:list<float>,granularity:string} */
    private function trend(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $days = $from->diffInDays($to) + 1;
        $granularity = match (true) {
            $days <= 31 => 'day',
            $days <= 180 => 'week',
            $days <= 730 => 'month',
            default => 'quarter',
        };
        $buckets = $this->emptyBuckets($from, $to, $granularity);

        $quotationRows = $this->quotationQuery($filters, $from, $to)
            ->selectRaw('DATE(date) as report_date, COUNT(*) as opportunities, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as won', [QuotationStatus::KY_HOP_DONG->value])
            ->groupByRaw('DATE(date)')
            ->get();
        foreach ($quotationRows as $row) {
            $key = $this->bucketKey(CarbonImmutable::parse($row->report_date), $granularity);
            if (isset($buckets[$key])) {
                $buckets[$key]['opportunities'] += (int) $row->opportunities;
                $buckets[$key]['won'] += (int) $row->won;
            }
        }

        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $revenueRows = $this->contractQuery($modelClass, $filters, 'submitted_at', $from, $to)
                ->whereNotNull('submitted_at')
                ->selectRaw('DATE(submitted_at) as report_date, COALESCE(SUM(revenue), 0) as revenue')
                ->groupByRaw('DATE(submitted_at)')
                ->get();
            foreach ($revenueRows as $row) {
                $key = $this->bucketKey(CarbonImmutable::parse($row->report_date), $granularity);
                if (isset($buckets[$key])) {
                    $buckets[$key]['revenue'] += (float) $row->revenue;
                }
            }
        }

        return [
            'labels' => array_column($buckets, 'label'),
            'opportunities' => array_column($buckets, 'opportunities'),
            'won' => array_column($buckets, 'won'),
            'revenue' => array_column($buckets, 'revenue'),
            'granularity' => $granularity,
        ];
    }

    /** @return list<array{label:string,count:int}> */
    private function statusBreakdown(Builder $query): array
    {
        return $query
            ->selectRaw("COALESCE(NULLIF(TRIM(status), ''), 'Chưa xác định') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'count' => (int) $row->total])
            ->all();
    }

    /** @return array{quotations:array<string,int>,contracts:array<string,int>,total_issues:int} */
    private function dataQuality(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $quotation = $this->quotationQuery($filters, $from, $to)
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN service IS NULL OR TRIM(service) = '' THEN 1 ELSE 0 END), 0) as missing_service,
                 COALESCE(SUM(CASE WHEN province IS NULL OR TRIM(province) = '' THEN 1 ELSE 0 END), 0) as missing_province,
                 COALESCE(SUM(CASE WHEN company_name IS NULL OR TRIM(company_name) = '' THEN 1 ELSE 0 END), 0) as missing_customer,
                 COALESCE(SUM(CASE WHEN date IS NULL THEN 1 ELSE 0 END), 0) as missing_date"
            )->first();
        $contracts = ['missing_service' => 0, 'missing_province' => 0, 'missing_staff' => 0];

        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $row = $this->contractQuery($modelClass, $filters, 'signed_at', $from, $to)
                ->selectRaw(
                    "COALESCE(SUM(CASE WHEN loai_dich_vu IS NULL OR TRIM(loai_dich_vu) = '' THEN 1 ELSE 0 END), 0) as missing_service,
                     COALESCE(SUM(CASE WHEN province IS NULL OR TRIM(province) = '' THEN 1 ELSE 0 END), 0) as missing_province,
                     COALESCE(SUM(CASE WHEN staff_id IS NULL THEN 1 ELSE 0 END), 0) as missing_staff"
                )->first();
            $contracts['missing_service'] += (int) ($row?->missing_service ?? 0);
            $contracts['missing_province'] += (int) ($row?->missing_province ?? 0);
            $contracts['missing_staff'] += (int) ($row?->missing_staff ?? 0);
        }

        $quotations = [
            'missing_service' => (int) ($quotation?->missing_service ?? 0),
            'missing_province' => (int) ($quotation?->missing_province ?? 0),
            'missing_customer' => (int) ($quotation?->missing_customer ?? 0),
            'missing_date' => (int) ($quotation?->missing_date ?? 0),
        ];

        return [
            'quotations' => $quotations,
            'contracts' => $contracts,
            'total_issues' => array_sum($quotations) + array_sum($contracts),
        ];
    }

    /** @return list<array{priority:string,title:string,reason:string,metric:string}> */
    private function recommendations(array $kpis, array $services, array $regions, array $quotationMetrics): array
    {
        $recommendations = [];
        $conversion = (float) $kpis['conversion_rate']['value'];
        if ($quotationMetrics['opportunities'] >= 3 && $conversion < 25) {
            $recommendations[] = [
                'priority' => 'Cao',
                'title' => 'Rà soát các báo giá đang theo dõi',
                'reason' => 'Tỷ lệ chuyển đổi hiện thấp hơn ngưỡng 25%.',
                'metric' => number_format($conversion, 2, ',', '.').'%',
            ];
        }
        if (($kpis['revenue']['growth'] ?? 0) < 0) {
            $recommendations[] = [
                'priority' => 'Cao',
                'title' => 'Ưu tiên khôi phục doanh số',
                'reason' => 'Doanh số ghi nhận đang giảm so với kỳ liền trước.',
                'metric' => number_format((float) $kpis['revenue']['growth'], 2, ',', '.').'%',
            ];
        }

        $topService = collect($services)->first(fn (array $row): bool => $row['label'] !== 'Chưa phân loại');
        if ($topService && $topService['score'] >= 50) {
            $recommendations[] = [
                'priority' => 'Trung bình',
                'title' => 'Tập trung dịch vụ '.$topService['label'],
                'reason' => 'Dịch vụ dẫn đầu theo lượng cơ hội, giá trị tiềm năng, chuyển đổi và tăng trưởng.',
                'metric' => 'Điểm tiềm năng '.number_format((float) $topService['score'], 1, ',', '.'),
            ];
        }

        $topRegion = collect($regions)->first(fn (array $row): bool => $row['label'] !== 'Chưa xác định');
        if ($topRegion && $topRegion['score'] >= 45) {
            $recommendations[] = [
                'priority' => 'Trung bình',
                'title' => 'Tăng cường khai thác '.$topRegion['label'],
                'reason' => 'Khu vực có điểm tổng hợp cao nhất từ cơ hội, giá trị tiềm năng, chuyển đổi và doanh số.',
                'metric' => 'Điểm tiềm năng '.number_format((float) $topRegion['score'], 1, ',', '.'),
            ];
        }

        if ($quotationMetrics['returning_customers'] > $quotationMetrics['new_customers']) {
            $recommendations[] = [
                'priority' => 'Thấp',
                'title' => 'Xây dựng danh sách bán thêm cho khách hàng cũ',
                'reason' => 'Số khách hàng quay lại cao hơn số khách hàng mới trong kỳ.',
                'metric' => number_format($quotationMetrics['returning_customers']).' khách quay lại',
            ];
        }

        if ($recommendations === []) {
            $recommendations[] = [
                'priority' => 'Thấp',
                'title' => 'Tiếp tục cập nhật dữ liệu báo giá',
                'reason' => 'Chưa có tín hiệu đủ mạnh để sinh định hướng chuyên biệt cho kỳ này.',
                'metric' => number_format($quotationMetrics['opportunities']).' cơ hội',
            ];
        }

        return array_slice($recommendations, 0, 5);
    }

    /** @return list<array<string, int|float|string|null>> */
    private function recentOpportunities(Builder $query): array
    {
        return $query->with('staff:id,name')
            ->latest('date')
            ->latest('id')
            ->limit(12)
            ->get(['id', 'date', 'staff_id', 'company_name', 'service', 'province', 'status', 'total_value', 'value_inc_vat', 'original_value'])
            ->map(fn (Quotation $quotation): array => [
                'id' => $quotation->id,
                'date' => $quotation->date?->format('d/m/Y'),
                'staff' => $quotation->staff?->name ?? 'Chưa phân công',
                'customer' => $quotation->company_name ?: 'Chưa có tên khách hàng',
                'service' => $quotation->service ?: 'Chưa phân loại',
                'province' => $quotation->province ?: 'Chưa xác định',
                'status' => $quotation->status ?: 'Chưa xác định',
                'value' => $this->quotationValue($quotation),
            ])->all();
    }

    /** @return list<array<string, int|float|string>> */
    private function groupedQuotationAnalysis(array $filters, CarbonImmutable $from, CarbonImmutable $to, string $column): array
    {
        $fallback = $column === 'province' ? 'Chưa xác định' : 'Chưa phân loại';
        $valueExpression = $this->quotationValueExpression();
        $pipelinePlaceholders = implode(',', array_fill(0, count(self::PIPELINE_STATUSES), '?'));

        return $this->quotationQuery($filters, $from, $to)
            ->selectRaw(
                "COALESCE(NULLIF(TRIM({$column}), ''), '{$fallback}') as label,
                 COUNT(*) as opportunities,
                 COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as won,
                 COALESCE(SUM(CASE WHEN status IN ({$pipelinePlaceholders}) THEN {$valueExpression} ELSE 0 END), 0) as potential_value",
                [QuotationStatus::KY_HOP_DONG->value, ...self::PIPELINE_STATUSES],
            )
            ->groupBy('label')
            ->get()
            ->map(function ($row): array {
                $opportunities = (int) $row->opportunities;
                $won = (int) $row->won;

                return [
                    'label' => (string) $row->label,
                    'opportunities' => $opportunities,
                    'won' => $won,
                    'potential_value' => (float) $row->potential_value,
                    'conversion_rate' => $opportunities > 0 ? round(($won / $opportunities) * 100, 2) : 0.0,
                ];
            })->all();
    }

    /** @return list<array{staff_id:int,opportunities:int,won:int,conversion_rate:float}> */
    private function groupedStaffQuotationAnalysis(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->quotationQuery($filters, $from, $to)
            ->selectRaw('staff_id, COUNT(*) as opportunities, COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as won', [QuotationStatus::KY_HOP_DONG->value])
            ->groupBy('staff_id')
            ->get()
            ->map(function ($row): array {
                $opportunities = (int) $row->opportunities;
                $won = (int) $row->won;

                return [
                    'staff_id' => (int) $row->staff_id,
                    'opportunities' => $opportunities,
                    'won' => $won,
                    'conversion_rate' => $opportunities > 0 ? round(($won / $opportunities) * 100, 2) : 0.0,
                ];
            })->all();
    }

    /** @return array<int, float> */
    private function revenueByStaff(array $filters, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $result = [];
        foreach (array_keys(self::CONTRACT_MODELS) as $modelClass) {
            $rows = $this->contractQuery($modelClass, $filters, 'submitted_at', $from, $to)
                ->whereNotNull('submitted_at')
                ->selectRaw('staff_id, COALESCE(SUM(revenue), 0) as revenue')
                ->groupBy('staff_id')
                ->get();
            foreach ($rows as $row) {
                $staffId = (int) $row->staff_id;
                $result[$staffId] = ($result[$staffId] ?? 0) + (float) $row->revenue;
            }
        }

        return $result;
    }

    private function quotationQuery(array $filters, CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return Quotation::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($filters['staff_id'], fn (Builder $query, int $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['service'] !== '', fn (Builder $query) => $query->where('service', $filters['service']))
            ->when($filters['province'] !== '', fn (Builder $query) => $query->where('province', $filters['province']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']));
    }

    /** @param class-string $modelClass */
    private function contractQuery(
        string $modelClass,
        array $filters,
        string $dateColumn,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): Builder {
        return $modelClass::query()
            ->whereBetween($dateColumn, [$from->toDateString(), $to->toDateString()])
            ->when($filters['staff_id'], fn (Builder $query, int $staffId) => $query->where('staff_id', $staffId))
            ->when($filters['service'] !== '', fn (Builder $query) => $query->where('loai_dich_vu', $filters['service']))
            ->when($filters['province'] !== '', fn (Builder $query) => $query->where('province', $filters['province']));
    }

    /** @return array<string, array{label:string,opportunities:int,won:int,revenue:float}> */
    private function emptyBuckets(CarbonImmutable $from, CarbonImmutable $to, string $granularity): array
    {
        $buckets = [];
        $cursor = $from;
        while ($cursor <= $to) {
            $key = $this->bucketKey($cursor, $granularity);
            $buckets[$key] ??= [
                'label' => $this->bucketLabel($cursor, $granularity),
                'opportunities' => 0,
                'won' => 0,
                'revenue' => 0.0,
            ];
            $cursor = match ($granularity) {
                'day' => $cursor->addDay(),
                'week' => $cursor->addWeek(),
                'month' => $cursor->addMonth(),
                default => $cursor->addMonths(3),
            };
        }

        return $buckets;
    }

    private function bucketKey(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'day' => $date->format('Y-m-d'),
            'week' => $date->startOfWeek()->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            default => $date->format('Y').'-Q'.(int) ceil($date->month / 3),
        };
    }

    private function bucketLabel(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'day' => $date->format('d/m'),
            'week' => 'Tuần '.$date->startOfWeek()->format('d/m'),
            'month' => 'T'.$date->month.'/'.$date->year,
            default => 'Q'.(int) ceil($date->month / 3).'/'.$date->year,
        };
    }

    /** @return array{value:int|float,growth:?float,previous:int|float} */
    private function metricWithGrowth(int|float $current, int|float $previous): array
    {
        return ['value' => $current, 'growth' => $this->growth((float) $current, (float) $previous), 'previous' => $previous];
    }

    private function growth(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / abs($previous)) * 100, 2);
    }

    private function quotationValueExpression(): string
    {
        return 'COALESCE(NULLIF(total_value, 0), NULLIF(value_inc_vat, 0), original_value, 0)';
    }

    private function quotationValue(Quotation $quotation): float
    {
        return (float) ($quotation->total_value ?: $quotation->value_inc_vat ?: $quotation->original_value ?: 0);
    }

    private function normalizeFilters(User $viewer, array $filters): array
    {
        $from = CarbonImmutable::parse($filters['date_from'] ?? now()->startOfMonth()->toDateString());
        $to = CarbonImmutable::parse($filters['date_to'] ?? now()->toDateString());
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $staffId = isset($filters['staff_id']) && $filters['staff_id'] !== null
            ? (int) $filters['staff_id']
            : null;
        if (! $this->canViewAllStaff($viewer)) {
            $staffId = (int) $viewer->id;
        } elseif ($staffId && ! User::whereKey($staffId)->where('is_active', true)->exists()) {
            $staffId = null;
        }

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'staff_id' => $staffId,
            'service' => trim((string) ($filters['service'] ?? '')),
            'province' => trim((string) ($filters['province'] ?? '')),
            'status' => trim((string) ($filters['status'] ?? '')),
        ];
    }
}
