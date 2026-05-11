# CLAUDE.md — Bảo Châu Environment ERP

Hướng dẫn cho Claude (và AI assistant) hiểu codebase này.

---

## Tổng quan dự án

**Tên:** Hệ thống ERP nội bộ — Công ty Môi trường Bảo Châu  
**Mục đích:** Quản lý hợp đồng, doanh số, hóa đơn, chấm công, báo cáo hàng ngày, hoa hồng, chuyển phát nhanh.  
**Ngôn ngữ giao diện:** Tiếng Việt (locale `vi`, timezone `Asia/Ho_Chi_Minh`)

---

## Tech Stack

| Layer | Công nghệ |
|---|---|
| Backend | Laravel 13.0, PHP 8.3+ |
| Frontend reactive | Livewire 4.2 |
| CSS | Bootstrap 5 (Conca theme) + Tailwind CSS 4.0 |
| Build | Vite 8.0 |
| DB | MySQL |
| Auth/Permission | Spatie Laravel Permission |
| Activity log | Spatie Laravel Activity Log |
| Excel | PHPOffice/PHPSpreadsheet |
| File storage | Local disk hoặc AWS S3 |
| API ngoài | Viettel Post API |
| HTTP client JS | Axios 1.11 |
| Pagination style | Bootstrap 5 |

---

## Kiến trúc tổng thể

**Hybrid architecture:**
- Livewire components cho các trang list/manager (CRUD inline, reactive).
- Controller truyền thống cho auth, dashboard KPI, CRUD forms (users, roles, departments, settings, export excel).

**Không dùng API JSON** — toàn bộ là server-side rendering với Livewire wire protocol.

---

## Cấu trúc thư mục quan trọng

```
app/
  Actions/          — Action classes (DailyReports, Quotations, Contracts)
  Enums/            — PHP 8.1 Enums (Status, Role, Permission...)
  Http/
    Controllers/    — Auth + Admin controllers (8 files)
    Middleware/     — CheckPermission, CheckRole, EnsureUserIsActive
    Requests/       — Form Request validation classes
  Livewire/
    Admin/          — 54 Livewire components
    Concerns/       — Shared traits: ContractValidation, CleanMoneyInput
  Models/           — 31 Eloquent models
  Notifications/    — 7 notifications (kênh database)
  Observers/        — ContractPaymentScheduleObserver
  Providers/        — AppServiceProvider
  Services/         — AttendanceService, ViettelPostService

resources/views/
  admin/layouts/    — app.blade.php (master layout)
  admin/partials/   — head, sidebar, header, breadcrumb, footer, scripts
  admin/pages/      — Trang truyền thống (dashboard, users, roles, departments, profile, settings)
  livewire/admin/   — Views cho Livewire components
  components/       — Blade components tái sử dụng
  errors/           — Trang lỗi (400, 403, 404, 500)
  auth/             — login.blade.php

routes/web.php      — Toàn bộ routes (guest + auth)
database/migrations — 73 migration files
database/seeders    — 16 seeder files
```

---

## Models & Database

### Users & Tổ chức
- `User` — HasRoles, Notifiable; cột `is_active` (khóa tài khoản)
- `Department` — Phòng ban

### Khách hàng & Nhà thầu phụ
- `Customer` — slug routing, SoftDeletes, LogsActivity
- `Handler` — slug routing, SoftDeletes, LogsActivity

### Hợp đồng (6 loại)

| Model | Bảng DB | Loại hợp đồng |
|---|---|---|
| `ContractWaste` | `contract_wastes` | Chất thải & tiếng ồn |
| `ContractLegal` | `contract_consultings` | Pháp lý & hồ sơ môi trường |
| `ContractResearch` | `contract_commercials` | NC & chuyển đổi công nghệ |
| `ContractTechnical` | `contract_projects` | Kỹ thuật & ứng phó sự cố |
| `ContractEmission` | `contract_energies` | Phát thải & năng lượng |
| `ContractSustainability` | `contract_sustainabilities` | TV & báo cáo PTBV |

### Hỗ trợ hợp đồng (Polymorphic)
- `ContractAssignment` — morphTo `assignable` (giao việc)
- `ContractWorkflowStep` — morphTo `contract` (có hằng số STEPS + STEPS_TECHNICAL)
- `ContractMilestoneFile` — morphTo `contract`
- `ContractPaymentSchedule` — morphTo `contract` (có MODEL_MAP constant)
- `ContractProgressNote` — morphTo `contract`
- `CommissionRequest` — morphTo `contract`, SoftDeletes, LogsActivity (CONTRACT_TYPES constant)

### Legacy
- `ConsultingWorkflowStep`, `ConsultingMilestoneFile` — belongsTo `ContractLegal` (cũ, không dùng morphTo)

### Hóa đơn
- `InvoiceBaoChau` — bảng `invoice_bao_chau`, SoftDeletes
- `InvoiceHandler` — bảng `invoice_handlers`, SoftDeletes

### Doanh số
- `SalesProgressive` — bảng `progressive_sales`
- `SalesRenewal` — bảng `renewal_sales`
- `SalesTarget`, `Quotation`

### Vận hành
- `DailyReport` — cột `plan` **KHÔNG nullable** (lưu chuỗi rỗng `""` thay vì `null`)
- `MarketingDailyReport`
- `PostalDelivery`
- `InternalDoc` — cột `files` cast to array
- `CommissionRequest` — workflow duyệt hoa hồng

### Chấm công
- `AttendanceEmployee`, `AttendanceLog`, `AttendanceImport`

---

## Roles & Permissions

### 8 Roles

| Slug | Tên | Mô tả |
|---|---|---|
| `it` | IT/Admin hệ thống | **Super admin** — bypass toàn bộ route middleware |
| `giam-doc` | Giám đốc | Xem tất cả dữ liệu kinh doanh, CRUD hợp đồng |
| `tp-kinh-doanh` | Trưởng phòng KD | CRUD hợp đồng, duyệt hoa hồng |
| `kinh-doanh` | Nhân viên KD | Xem/tạo hợp đồng, tạo hoa hồng |
| `tu-van` | Tư vấn | Xem hợp đồng TV, CRUD mail-delivery |
| `ky-thuat` | Kỹ thuật | Xem hợp đồng kỹ thuật |
| `marketing` | Marketing | Báo cáo marketing |
| `ke-toan` | Kế toán | CRUD hóa đơn, payment schedules, duyệt hoa hồng |

> **Backward compat:** Role `quan-ly` (cũ) được sync cùng permissions với `giam-doc`.

### 3 Lớp Authorization

1. **Route Middleware** (`CheckPermission` / `CheckRole`):
   - IT role → bypass tất cả (top of both middleware).
   - Alias: `permission` → `CheckPermission`, `role` → `CheckRole`, `active` → `EnsureUserIsActive`.

2. **Blade `@can` / `@canany`** (Spatie Gate):
   - Dùng trong manager views để ẩn/hiện action buttons và `<th>` cột Actions.
   - IT **không có** Spatie permissions cho business modules → `@can()` trả về `false` → không thấy nút Edit/Delete trên hợp đồng/khách hàng (intentional).
   - IT chỉ có permissions: `users.*`, `roles.*`, `departments.*`, `settings.*`, `mail-delivery.*`, `internal-docs.*`, `daily-reports.*`, `activity-log.view`, `cham-cong.*`.

3. **Role checks trong Blade** (`hasAnyRole()`):
   - Contract managers dùng `auth()->user()->hasAnyRole([...])` thay vì `@can()` (pattern cũ hơn).

### Permission Format
- `module.action` — ví dụ: `contracts-waste.view`, `commissions.edit`
- Các action: `view`, `create`, `edit`, `delete`, `view-all`, `export`

### Logic đặc biệt
- `commissions.canEdit = can('commissions.edit') && !hasRole('ke-toan')` — kế toán là người duyệt, không phải editor
- `commissions.canApprove = hasRole('ke-toan')` — chỉ kế toán duyệt hoa hồng
- GĐ có `departments.*` CRUD (quản lý cơ cấu tổ chức, nhưng không có users/roles/settings)

---

## Livewire Components (54 components)

### Shared Traits (`app/Livewire/Concerns/`)
- `ContractValidation` — validation rules dùng chung cho contract forms
- `CleanMoneyInput` — format/clean số tiền VND trong input

### Root Admin (6)
`HomeBoard`, `StatisticsBoard`, `RankingsBoard`, `ItDashboard`, `NotificationBell`, `ActivityLogViewer`

### Contracts (9)
`ContractWasteManager`, `ContractConsultingManager`, `ContractCommercialManager`, `ContractProjectManager`, `ContractEnergyManager`, `ContractSustainabilityManager`, `ContractPaymentScheduleManager`, `ContractWorkflowPanel`, `ContractWorkflowProgress`

### Sales (3)
`SalesTargetRegistration`, `ProgressiveSalesManager`, `RenewalSalesManager`

### Reports (18)
- **Sales (8):** `SalesOverviewReport`, `SalesSummaryReport`, `SalesAchievementReport`, `SalesTargetReport`, `SalesRevenueReport`, `SalesTrackingReport`, `PersonalSalesReport`, `PersonalRenewalReport`
- **Consulting (5):** `ConsultingGeneralReport`, `ConsultingAchievementReport`, `ConsultingContractReport`, `ConsultingMonitoringReport`, `ConsultingServiceReport`
- **Technical (3):** `TechnicalAchievementReport`, `TechnicalContractReport`, `TechnicalFieldReport`
- **Marketing (2):** `MarketingSummaryReport`, `MarketingTargetReport`

### Others (18)
`CommissionRequestManager`, `CommissionRequestForm`, `CustomerManager`, `CustomerContractsView`, `HandlerManager`, `HandlerContractsView`, `DepartmentManager`, `RoleManager`, `UserManager`, `DailyReportManager`, `MarketingReportManager`, `InvoiceBaoChauManager`, `InvoiceHandlerManager`, `PostalDeliveryManager`, `QuotationManager`, `InternalDocManager`, `AttendanceManager`, `AttendanceEmployeeManager`

---

## Routes chính (`routes/web.php`)

| URL | Component / Controller | Permission |
|---|---|---|
| `/` | Redirect | — |
| `/login` | `LoginController` | guest |
| `/hop-dong/chat-thai-va-tieng-on` | `ContractWasteManager` | `contracts-waste.view` |
| `/hop-dong/phap-ly-va-ho-so-mt` | `ContractConsultingManager` | `contracts-consulting.view` |
| `/hop-dong/ky-thuat-va-ung-pho-sc` | `ContractTechnicalManager` | `contracts-project.view` |
| `/hop-dong/nc-va-chuyen-doi-cong-nghe` | `ContractCommercialManager` | `contracts-commercial.view` |
| `/hop-dong/tv-va-bao-cao-ptbv` | `ContractSustainabilityManager` | `contracts-sustainability.view` |
| `/hop-dong/phat-thai-va-nang-luong` | `ContractEnergyManager` | `contracts-energy.view` |
| `/doanh-so/tai-ky` | `RenewalSalesManager` | role: `kinh-doanh,tp-kinh-doanh` |
| `/hoa-hong` | `CommissionRequestManager` | `commissions.view` |
| `/theo-doi-bao-gia` | `QuotationManager` | `quotation-tracking.view` |
| `/hoa-don/bao-chau` | `InvoiceBaoChauManager` | `invoices.view` |
| `/hoa-don/chu-xu-ly` | `InvoiceHandlerManager` | `handler-invoices.view` |
| `/nhat-ky-cong-viec` | `DailyReportManager` | `daily-reports.view` |
| `/cham-cong` | `AttendanceManager` | `cham-cong.view` |
| `/cham-cong/nhan-vien` | `AttendanceEmployeeManager` | `cham-cong.edit` |
| `/bao-cao/kinh-doanh/*` | Sales Reports (8) | `reports-sales.view` |
| `/bao-cao/tu-van/*` | Consulting Reports | `reports-consulting.view` |
| `/bao-cao/ky-thuat/*` | Technical Reports | `reports-technical.view` |
| `/nhat-ky-hoat-dong` | `ActivityLogViewer` | `activity-log.view` |

> **Legacy redirect:** `GET /admin/{path?}` → `/{path}` (301)

---

## Controllers (8 files)

- `LoginController` — show, login (throttle 5/1min), logout
- `DashboardController` — KPI stats (tổng khách hàng, doanh số tháng, hợp đồng năm)
- `UserController` — resource CRUD (traditional views)
- `RoleController` — resource CRUD
- `DepartmentController` — resource CRUD
- `SettingController` — profile, index, password, updateProfile, updatePassword
- `AttendanceExportController` — export Excel (summary + detail)

---

## Services

- **`AttendanceService`** — `getMonthData()`, `buildSummaryGrid()`, `buildEmployeeDetail()`
- **`ViettelPostService`** — `getToken()` (cached 20h), `createOrder()`, `trackOrder()`, `getPricing()`
  - Base URL: `https://partner.viettelpost.vn/v2`
  - Sender: Công ty Môi trường Bảo Châu

---

## Notifications (7, kênh database)

- `CommissionRequestSubmittedNotification`
- `CommissionRequestStatusUpdatedNotification`
- `ContractAssignedNotification`
- `ContractProgressNoteNotification`
- `ContractWorkflowUpdatedNotification`
- `DailyReportReminderNotification`
- `DailyReportSubmittedNotification`

---

## Observer & Provider

- **`ContractPaymentScheduleObserver`** — `created/updated` → sync `SalesProgressive`; `deleted` → xóa `SalesProgressive` tương ứng
- **`AppServiceProvider`** — force HTTPS (non-local), Bootstrap 5 pagination, đăng ký Observer, lắng nghe auth events qua `LogAuthActivity`

---

## Scheduled Commands (`routes/console.php`)

- `avatars:migrate {--from=public}` — migrate avatar files
- `uploads:migrate {--from=public} {--path=*}` — migrate uploaded files
- **Schedule:** `daily-report-reminder` chạy lúc **16:30 thứ 2–6** — gửi `DailyReportReminderNotification` cho user active (trừ `giam-doc`) chưa nộp báo cáo hôm nay.

---

## Layout & Views

### Master Layout
```
app.blade.php
├── head.blade.php        (meta, Bootstrap CSS, SweetAlert2, Tailwind)
├── sidebar.blade.php     (menu role-based, collapsible)
├── header.blade.php      (user info, notification bell, theme toggle)
├── @yield('content') / {{ $slot }}
├── footer.blade.php
└── scripts.blade.php     (jQuery, Bootstrap JS, SweetAlert2, CKEditor, money format)
    └── @livewireScripts
```

### Components tái sử dụng
- `components/user-avatar.blade.php` — avatar với fallback màu + chữ cái đầu
- `components/admin/summary-card.blade.php` — KPI card (title, value, badge, iconClass)

### Icon library: Bootstrap Icons (`bi-*`)

---

## Patterns & Conventions

### Livewire UX Patterns
- **Toast notifications:** dispatch event `swal:toast` với payload `['type' => 'success|error|warning', 'message' => '...']` (không bọc thêm 1 lớp mảng).
- **Permission deny trong modal:** dispatch error toast rồi `return` — **không dùng** `abort_unless()` (sẽ crash toàn trang).
- **Validation:** catch lỗi đầu tiên, toast nó, rethrow để field errors vẫn render inline.
- **Validation messages:** luôn truyền `messages` + `attributes` tường minh (tiếng Việt) vào `validate()`, không dựa vào `lang/vi/validation.php`.

### Money Input
- Sử dụng trait `CleanMoneyInput` để strip dấu chấm phân cách nghìn trước khi lưu.
- Hiển thị: format `71.900.000 đ` (dấu chấm nghìn, đơn vị `đ`).

### Contract Create từ Quotation
- Khi mở modal từ `quotation_id`, cần đảm bảo `formData.department_id` được điền (có thể bị bỏ trống vì field ẩn) trước khi `validate()`.

### Cross-module Field Rollout
Khi thêm cột mới (ví dụ `voucher_status`), phải cập nhật: DB migration → Model `fillable` → Livewire `formData`/`filter`/query/export → Blade filter/table/detail/form.

### Auth Lock
- `users.is_active` kiểm soát trạng thái tài khoản.
- Login dùng `Auth::attempt([...credentials, 'is_active' => true])`.
- Middleware `active` đảm bảo session hiện tại bị ngắt nếu tài khoản bị khóa.

---

## Dữ liệu mẫu & Config

- **Default password:** env `DEFAULT_USER_PASSWORD` (default: `moitruongbaochau789`)
- **8 phòng ban seeded:** Ban Giám đốc, Admin/IT, Kinh doanh, Kỹ thuật, Kế toán, Tư vấn-CSKH, Marketing, Tổng hợp
- **63 tỉnh thành** trong `Support/VietnamProvinces::list()`
- **Active seeders (DatabaseSeeder):** RolesSeeder, PermissionsSeeder, DepartmentsSeeder, SampleUsersSeeder

---

## Lưu ý báo cáo doanh số

- `SalesSummaryReport` ≠ `SalesOverviewReport` — khi user nhắc "bảng tổng kết doanh số", đây là `SalesSummaryReport` (route `app.reports.sales.summary`).
- `SalesSummaryReport` tính từ hợp đồng đã ký (`signed_at`, `value`) qua 6 bảng contract; tái ký = `is_renewal=true`.
- `filter_staff` trong `SalesSummaryReport` phải áp dụng qua cả bảng `ContractPaymentSchedule` (via `staff_id` trên bảng contract).
- `ConsultingContractReport` và `TechnicalContractReport` là cùng 1 class, được route đến 6 URLs khác nhau (6 loại hợp đồng).

---

## Ghi chú bảo mật & trạng thái

- **IT có `Gate::before()` → ĐÃ FIX:** `AppServiceProvider::boot()` đã có `Gate::before()` trả `true` cho role `it` → IT thấy toàn bộ nút Edit/Delete kể cả hợp đồng/khách hàng.
- **Contract Livewire backend guards → ĐÃ CÓ:** `AbstractContractGenericManager` có `abort_unless(can(permEdit/permDelete/permCreate))` trong `edit()`, `delete()`, `save()` — không chỉ ẩn trên Blade.
- **Contract managers Blade vẫn dùng `hasAnyRole()` ⚠️:** Backend PHP đã dùng `can()` đúng cách, nhưng Blade-level UI control vẫn role-based → khó cấp quyền ngoại lệ mà không thay đổi role.
- **`advance-requests.*`, `articles.*`, `waste-requests.*` chưa có routes ⚠️:** Permissions có trong seeder nhưng không có route/UI → chưa triển khai.
