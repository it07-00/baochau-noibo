# Laravel Refactor Plan — Hệ thống Quản lý Nội bộ Môi trường
## PHP 8.3 + Livewire 4.2 + Spatie Permission | Enterprise Refactor

---

# ⚠️ NGUYÊN TẮC LÀM VIỆC

1. **Đọc toàn bộ code** trước khi refactor — không đọc không refactor
2. **Hỏi lại** nếu chưa rõ business logic
3. **Không assume**, không refactor theo template có sẵn
4. **Chỉ refactor những gì thực sự có vấn đề** — nếu code đã tốt, nói thẳng
5. **Không phá logic hiện tại**, không đổi behavior của hệ thống
6. **Không over-engineering** — đơn giản mà đúng tốt hơn phức tạp mà sai

---

# 📊 Trạng thái hiện tại (2025-05-07)

## ✅ Đã hoàn thành

| Phase | Mô tả | Commit |
|-------|-------|--------|
| 1.2 | Permission Enum — thay thế 89 hardcoded permission strings | `24b4ed2` |
| 1.3 | InvoiceStatus + PaymentScheduleStatus Enums | `aeac5fca` |
| 1.4 | ContractType Enum thay thế `CONTRACT_TYPES`/`MODEL_MAP` arrays | `09be8c06` |
| 2.1 | CommissionService — tách approve/reject/create workflow | `7f59d2af` |
| 2.2 | AbstractContractGenericManager — base class, giảm 4 contract managers từ ~607 xuống ~15 dòng mỗi cái | `d3c89ae0` |
| 3 | Thêm 5 Enums: QuotationStatus, CommissionRequestStatus, DailyReportStatus, ContractRenewalStatus, ContractVoucherStatus — xóa VOUCHER_STATUSES constant | session |
| 4 | Thay thế toàn bộ magic role strings bằng Role enum trong 15+ components | session |
| 6 | Action classes: UpsertQuotationAction, SubmitDailyReportAction, UpsertContractWasteAction | session |
| 7 | Query optimization: CommissionRequestManager 5→1 grouped summary query; StatisticsBoard byType 3→1 query/type + SQL GROUP BY aggregation for sourceSalesMap | session |

## 🔴 Còn lại — theo thứ tự ưu tiên

### Phase 5 — Form Objects (Livewire 4.x Forms) — HOÃN
- Deferred: quá invasive, đụng chạm Blade views

### Phase 7 — còn lại (deferred)
- `select()` cho paginated queries trong Reports — bỏ qua: 20 rows/page nên lợi ích nhỏ, nguy cơ vỡ Blade views cao
- StatisticsBoard cache — phức tạp (cache key cần include filters, invalidation khi upsert contract)

### Phase 8 — Tests
- [ ] Feature tests cho critical flows: tạo hợp đồng, duyệt commission
- [ ] Authorization tests: role-based access control

---

# 🏗️ Stack thực tế

```
PHP 8.3 · Laravel 13 · Livewire 4.2 · Spatie Permission
MySQL (Laragon local) · Vite + Tailwind CSS
```

**Dependencies:**
- `spatie/laravel-permission` — RBAC
- `spatie/laravel-activitylog` — Audit logging (đã dùng trên key models)
- `phpoffice/phpspreadsheet` — Excel import/export
- `livewire/livewire` ^4.2

---

# 👥 Roles thực tế (9 roles)

```php
// app/Enums/Role.php
enum Role: string
{
    case IT              = 'it';                // IT/Admin — toàn quyền
    case QuanLy          = 'quan-ly';           // Quản lý
    case KinhDoanh       = 'kinh-doanh';        // Kinh doanh/Sales
    case KeToan          = 'ke-toan';           // Kế toán
    case TuVan           = 'tu-van';            // Tư vấn/CSKH
    case KyThuat         = 'ky-thuat';          // Kỹ thuật
    case TpKinhDoanh     = 'tp-kinh-doanh';     // Trưởng phòng kinh doanh
    case TpTuVan         = 'tp-tu-van';         // Trưởng phòng tư vấn
    case TpKyThuat       = 'tp-ky-thuat';       // Trưởng phòng kỹ thuật
}
```

---

# 📁 Cấu trúc thư mục hiện tại vs Target

## Hiện tại (thực tế)
```
app/
├── Console/Commands/
├── Enums/              ← 5 enums (Permission, Role, ContractType, InvoiceStatus, PaymentScheduleStatus)
├── Http/
│   ├── Controllers/Admin/   ← 6 controllers (truyền thống, đơn giản)
│   ├── Controllers/Auth/    ← LoginController
│   └── Middleware/          ← CheckPermission, CheckRole, EnsureUserIsActive
├── Listeners/
│   └── LogAuthActivity.php
├── Livewire/
│   ├── Admin/               ← 61+ components
│   │   ├── Attendance/      (2 files)
│   │   ├── Commissions/     (2 files)
│   │   ├── Contracts/       (10 files — có AbstractContractGenericManager base)
│   │   ├── Customers/       (2 files)
│   │   ├── DailyReports/    (1 file — 352 dòng)
│   │   ├── Departments/     (1 file)
│   │   ├── Handlers/        (2 files)
│   │   ├── Hr/              (2 files)
│   │   ├── InternalDocs/    (2 files)
│   │   ├── Invoices/        (2 files)
│   │   ├── Marketing/       (1 file)
│   │   ├── PostalDeliveries/(1 file — 347 dòng)
│   │   ├── Quotations/      (1 file — 855 dòng ⚠️)
│   │   ├── Reports/         (14 files)
│   │   ├── Roles/           (1 file)
│   │   ├── Sales/           (3 files)
│   │   ├── Users/           (1 file)
│   │   └── WorkSchedules/   (1 file)
│   └── Concerns/            ← CleanMoneyInput, ContractValidation (traits)
├── Models/             ← 35 models
├── Notifications/      ← 7 notifications
├── Observers/          ← ContractPaymentScheduleObserver
├── Providers/
├── Services/           ← AttendanceService, CommissionService, ViettelPostService
└── Support/
    └── VietnamProvinces.php
```

## Target (sau khi refactor hoàn tất)
```
app/
├── Actions/
│   ├── Commissions/
│   │   ├── ApproveCommissionAction.php
│   │   └── RejectCommissionAction.php
│   ├── Contracts/
│   │   ├── CreateContractWasteAction.php
│   │   └── UpdateContractWasteAction.php
│   ├── Quotations/
│   │   ├── CreateQuotationAction.php
│   │   ├── UpdateQuotationAction.php
│   │   └── ConvertToContractAction.php
│   └── DailyReports/
│       └── SubmitDailyReportAction.php
│
├── DTOs/               ← Nếu cần (thứ yếu)
│
├── Enums/              ← MỞ RỘNG
│   ├── Permission.php              ✅ done
│   ├── Role.php                    ✅ done
│   ├── ContractType.php            ✅ done
│   ├── InvoiceStatus.php           ✅ done
│   ├── PaymentScheduleStatus.php   ✅ done
│   ├── QuotationStatus.php         ← phase 3
│   ├── ContractWasteStatus.php     ← phase 3
│   ├── CommissionRequestStatus.php ← phase 3
│   ├── DailyReportStatus.php       ← phase 3
│   └── ContractConsultingStatus.php← phase 3
│
├── Exceptions/         ← Nếu cần custom exceptions
│
├── Livewire/
│   ├── Forms/          ← THÊM MỚI (phase 5)
│   │   ├── QuotationForm.php
│   │   ├── ContractWasteForm.php
│   │   ├── ContractConsultingForm.php
│   │   └── CommissionRequestForm.php
│   ├── Admin/          ← Giữ nguyên cấu trúc, làm mỏng components
│   └── Concerns/       ← Giữ nguyên
│
├── Models/             ← Thêm scopes, casts chuẩn
│
├── Observers/          ← Thêm Observer khi cần auto-invalidate cache
│
├── Policies/           ← THÊM MỚI (phase 4)
│   ├── CommissionRequestPolicy.php
│   ├── QuotationPolicy.php
│   └── ContractPolicy.php
│
├── Services/           ← GIỮ + MỞ RỘNG NẾU CẦN
│   ├── AttendanceService.php   ✅ tốt
│   ├── CommissionService.php   ✅ tốt
│   └── ViettelPostService.php  ✅ tốt
│
└── Support/
    └── VietnamProvinces.php
```

---

# 🎯 Mục tiêu chất lượng

- Clean Architecture phù hợp với quy mô thực tế
- SOLID Principles — không over-engineer
- Maintainable & Readable Code
- Livewire 4.2 Best Practices
- PHP 8.3 Modern Features
- Spatie Permission tích hợp đúng tầng
- Production Ready

---

# ⚙️ Yêu cầu kỹ thuật chi tiết

---

## 1. Livewire Component phải mỏng

Component chỉ làm:
- Quản lý UI state (search, filter, modal open/close, pagination)
- Nhận input từ user
- Gọi Service/Action
- Cập nhật state để re-render

**Không được có trong Component:**
- Business logic phức tạp
- Validation thủ công — dùng Form Object
- Tính toán nặng trong `render()`

```php
// ❌ TRƯỚC — Fat component (hiện tại trong QuotationManager)
final class QuotationManager extends Component
{
    // 30+ public properties
    public string $search = '';
    public $ten_khach_hang = '';
    public $gia_tri_hop_dong = '';
    // ...

    public function save(): void
    {
        $this->validate([...]);          // ❌ validation inline
        $this->cleanMoneyFields();       // ❌ business logic trong component
        Quotation::create([...]);        // ❌ query trực tiếp
    }
}

// ✅ SAU — Component mỏng
final class QuotationManager extends Component
{
    use WithPagination;

    public QuotationForm $form;
    public string $search = '';

    public function mount(): void
    {
        $this->authorize(Permission::ViewQuotations->value);
    }

    public function save(): void
    {
        $this->authorize(Permission::CreateQuotations->value);
        $this->form->validate();

        app(CreateQuotationAction::class)->execute($this->form->toData(), auth()->user());

        $this->form->reset();
        $this->dispatch('notify', type: 'success', message: 'Đã lưu báo giá.');
    }

    public function render(): View
    {
        $quotations = Quotation::query()
            ->with(['staff:id,name'])
            ->when(
                auth()->user()->hasRole(Role::KinhDoanh->value),
                fn($q) => $q->where('staff_id', auth()->id())
            )
            ->when($this->search, fn($q) =>
                $q->where('ten_khach_hang', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.quotations.quotation-manager', compact('quotations'));
    }
}
```

---

## 2. Livewire Form Object — thay thế inline validation

Dùng **Form Object** (Livewire 4.x) cho mọi form có > 2 fields.

```php
// app/Livewire/Forms/QuotationForm.php
final class QuotationForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $ten_khach_hang = '';

    #[Validate('nullable|numeric|min:0')]
    public string $gia_tri_hop_dong = '';

    #[Validate('required')]
    public string $trang_thai = '';

    // CleanMoneyInput logic tích hợp vào Form
    public function getCleanContractValue(): int
    {
        return (int) str_replace(['.', ','], '', $this->gia_tri_hop_dong);
    }

    public function setFromModel(Quotation $quotation): void
    {
        $this->ten_khach_hang    = $quotation->ten_khach_hang;
        $this->gia_tri_hop_dong  = number_format($quotation->gia_tri_hop_dong);
        $this->trang_thai        = $quotation->trang_thai;
    }
}
```

---

## 3. Authorization Layer — 4 tầng

```
Tầng 1 — Route Middleware:   Permission::toMiddleware(Permission::ViewQuotations)
          → Chặn trước khi Livewire component được load

Tầng 2 — mount():            $this->authorize(Permission::ViewQuotations->value)
          → Chặn khi component được khởi tạo

Tầng 3 — Action methods:     $this->authorize('create', Quotation::class)
          → Chặn trước mỗi action cụ thể

Tầng 4 — Blade @can/@role:   Ẩn/hiện UI element
          → UX only, KHÔNG phải security
```

**Không bao giờ chỉ check ở Blade.** User có thể bypass bằng cách gọi trực tiếp Livewire method.

```php
// ❌ HIỆN TẠI — inline role check rải rác
final class StatisticsBoard extends Component
{
    public function mount(): void
    {
        if (!auth()->user()->hasRole(Role::IT->value)) {
            abort(403);
        }
    }
}

// ✅ SAU — dùng Permission enum
final class StatisticsBoard extends Component
{
    public function mount(): void
    {
        $this->authorize(Permission::ViewStatistics->value);
    }
}
```

---

## 4. Enum thay Magic Strings — vẫn còn thiếu

### Đã có (không cần đổi):
```php
// ✅ Tốt rồi
InvoiceStatus::Draft, InvoiceStatus::Paid, ...
PaymentScheduleStatus::Pending, ...
ContractType::Waste, ContractType::Consulting, ...
Permission::ViewQuotations, Permission::CreateContracts, ...
Role::IT, Role::KinhDoanh, ...
```

### Còn thiếu — cần tạo:
```php
// ❌ HIỆN TẠI trong QuotationManager, ContractWasteManager,...
$statuses = ['Chưa ký', 'Đã ký', 'Tạm dừng', 'Kết thúc', 'Hủy bỏ'];

// ✅ Target
enum QuotationStatus: string
{
    case ChuaKy   = 'Chưa ký';
    case DaKy     = 'Đã ký';
    case TamDung  = 'Tạm dừng';
    case KetThuc  = 'Kết thúc';
    case HuyBo    = 'Hủy bỏ';

    public function label(): string { return $this->value; }

    public function color(): string
    {
        return match($this) {
            self::DaKy    => 'green',
            self::TamDung => 'yellow',
            self::HuyBo   => 'red',
            default       => 'gray',
        };
    }
}

// ❌ HIỆN TẠI trong CommissionService
if ($commission->trang_thai === 'Đã chi') { ... }

// ✅ Target
enum CommissionRequestStatus: string
{
    case Pending  = 'Chờ duyệt';
    case Approved = 'Đã duyệt';
    case Paid     = 'Đã chi';
    case Rejected = 'Từ chối';
}
```

---

## 5. PHP 8.3 Features — Bắt buộc

```php
// ✅ Readonly class — DTOs bất biến
readonly class QuotationData
{
    public function __construct(
        public string  $ten_khach_hang,
        public int     $gia_tri_hop_dong,
        public string  $trang_thai,
        public int     $staff_id,
        public ?string $ghi_chu = null,
    ) {}
}

// ✅ Constructor Promotion
final class CreateQuotationAction
{
    public function __construct(
        private readonly CommissionService $commissionService,
    ) {}

    public function execute(QuotationData $data, User $creator): Quotation
    {
        return DB::transaction(function () use ($data, $creator): Quotation {
            $quotation = Quotation::create([
                'ten_khach_hang'   => $data->ten_khach_hang,
                'gia_tri_hop_dong' => $data->gia_tri_hop_dong,
                'trang_thai'       => $data->trang_thai,
                'staff_id'         => $data->staff_id,
            ]);

            return $quotation;
        });
    }
}

// ✅ Enum với typed constants (PHP 8.3)
final class CacheKey
{
    const string DASHBOARD_STATS     = 'dashboard.stats';
    const string STATISTICS_OVERVIEW = 'statistics.overview';
    const int    DEFAULT_TTL         = 900; // 15 phút
}
```

---

## 6. Query Optimization

```php
// ❌ HIỆN TẠI — CommissionRequestManager (tiềm ẩn N+1)
$query->whereHas('contract', function ($qc) {
    $qc->where('shd_bc', 'like', '%' . $this->search . '%')
        ->orWhereHas('customer', function ($qcust) {
            $qcust->where('name', 'like', '%' . $this->search . '%');
        });
});

// ✅ SAU — Tách search trực tiếp, eager load đúng
$commissions = CommissionRequest::query()
    ->with(['contract.customer:id,name', 'requestedBy:id,name'])
    ->when($this->search, fn($q) =>
        $q->where('shd_bc', 'like', "%{$this->search}%")
          ->orWhereHas('contract.customer', fn($qc) =>
              $qc->where('name', 'like', "%{$this->search}%")))
    ->latest()
    ->paginate(20);

// ✅ StatisticsBoard — cache dashboard queries
$stats = Cache::remember(CacheKey::DASHBOARD_STATS, CacheKey::DEFAULT_TTL, fn(): array => [
    'total_contracts'  => ContractWaste::active()->count(),
    'pending_invoices' => InvoiceBaoChau::whereStatus(InvoiceStatus::Draft)->count(),
    // ...
]);
```

---

## 7. Spatie Permission — Tích hợp chuẩn

### Super Admin bypass (IT role)
```php
// bootstrap/app.php — Gate::before() ĐÃ CÓ hoặc cần thêm
Gate::before(function (User $user, string $ability): ?bool {
    if ($user->hasRole(Role::IT->value)) {
        return true; // IT bypass tất cả gates
    }
    return null;
});
```

### Policy tích hợp Spatie
```php
final class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ViewQuotations->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CreateQuotations->value);
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->can(Permission::EditQuotations->value)
            && ($user->id === $quotation->staff_id
                || $user->hasRole(Role::QuanLy->value));
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->can(Permission::DeleteQuotations->value);
    }
}
```

### Route Middleware (đã có — kiểm tra đúng cách dùng Enum)
```php
// ✅ Hiện tại đang dùng đúng
Route::middleware(Permission::toMiddleware(Permission::ViewQuotations))
    ->get('/quotations', QuotationManager::class)
    ->name('admin.quotations.index');
```

---

## 8. Eloquent Model chuẩn

```php
// ✅ Ví dụ Quotation model — cần cập nhật thêm casts và scopes
final class Quotation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ten_khach_hang', 'gia_tri_hop_dong', 'trang_thai', 'staff_id', 'ghi_chu',
    ];

    protected function casts(): array
    {
        return [
            'trang_thai'       => QuotationStatus::class,  // ← sau khi có Enum
            'ngay_bao_gia'     => 'date',
            'gia_tri_hop_dong' => 'integer',
        ];
    }

    // ✅ Scopes
    public function scopeByStaff(Builder $query, int $staffId): void
    {
        $query->where('staff_id', $staffId);
    }

    public function scopeSearch(Builder $query, string $keyword): void
    {
        $query->where('ten_khach_hang', 'like', "%{$keyword}%");
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
```

---

## 9. Coding Standards

- **PSR-12** + **Laravel Pint** — auto format
- **Type hinting** tất cả parameters
- **Return type** tất cả methods
- **`final class`** mặc định — trừ khi cần extend
- **`readonly`** cho DTOs và properties bất biến
- **Dependency Injection** — không dùng `app()` / `resolve()` thủ công trong business logic
- **Early Return** — tránh nested if/else
- **Method ≤ 20 dòng** — một trách nhiệm
- **Enum thay magic string** — bắt buộc cho permission, role, status
- **Strict naming**:
  - Component: `QuotationManager`, `ContractConsultingManager`
  - Form Object: `QuotationForm`, `ContractWasteForm`
  - Action: `CreateQuotationAction`, `ApproveCommissionAction`
  - Service: `CommissionService`, `AttendanceService`
  - Notification: `CommissionRequestSubmittedNotification`
  - Permission Enum: `Permission::ViewQuotations`
  - Role Enum: `Role::KinhDoanh`

---

## 10. Quy trình refactor từng phần

Với mỗi phần:
1. Chỉ rõ vấn đề
2. Code **BEFORE**
3. Code **AFTER**
4. Giải thích cải tiến
5. Rủi ro cần lưu ý

---

# 🔥 Kết quả mong muốn

Sau khi refactor hoàn tất, codebase phải:

- Permission check đúng tầng — Route → mount() → action method → Blade UI
- Không có magic string permission/role/status — tất cả qua Enum
- Livewire component nhỏ, focused, dễ đọc (mục tiêu < 200 dòng/component)
- Business logic tách biệt trong Action/Service — dễ test độc lập
- Dễ onboarding developer mới
- Dễ maintain nhiều năm
