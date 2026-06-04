<?php

use App\Enums\Permission;
use App\Enums\Role;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('app.home')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Legacy URLs redirect: /admin/* -> /*
Route::middleware(['auth', 'active'])->get('/admin/{path?}', function (Request $request, ?string $path = null) {
    $target = '/' . ltrim($path ?: 'dashboard', '/');
    $query = $request->getQueryString();

    if ($query) {
        $target .= '?' . $query;
    }

    return redirect($target, 301);
})->where('path', '.*');

Route::middleware(['auth', 'active'])->name('app.')->group(function () {
    Route::get('/', \App\Livewire\Admin\HomeBoard::class)->name('home');
    Route::get('/bang-dieu-khien', \App\Livewire\Admin\StatisticsBoard::class)->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [SettingController::class, 'profile'])->name('index');
        Route::post('/', [SettingController::class, 'updateProfile'])->name('update');
    });

    Route::get('/change-password', [SettingController::class, 'password'])->name('password.index');
    Route::post('/change-password', [SettingController::class, 'updatePassword'])->name('password.update');

    // Người dùng
    Route::middleware(Permission::toMiddleware(Permission::USERS_VIEW))->group(function () {
        Route::get('users', \App\Livewire\Admin\Users\UserManager::class)->name('users.index');
        Route::resource('users', UserController::class)->except(['index']);
    });

    // Vai trò
    Route::middleware(Permission::toMiddleware(Permission::ROLES_VIEW))->group(function () {
        Route::get('roles', \App\Livewire\Admin\Roles\RoleManager::class)->name('roles.index');
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['index']);
    });

    // Phòng ban
    Route::middleware(Permission::toMiddleware(Permission::DEPARTMENTS_VIEW))->group(function () {
        Route::get('phong-ban', \App\Livewire\Admin\Departments\DepartmentManager::class)->name('departments.index');
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['index']);
    });

    // Nhà thầu phụ
    Route::middleware(Permission::toMiddleware(Permission::HANDLERS_VIEW))->group(function () {
        Route::get('nha-thau-phu', \App\Livewire\Admin\Handlers\HandlerManager::class)->name('handlers.index');
        Route::get('nha-thau-phu/{handler}/hop-dong', \App\Livewire\Admin\Handlers\HandlerContractsView::class)->name('handlers.contracts');
    });

    // Khách hàng
    Route::middleware(Permission::toMiddleware(Permission::CUSTOMERS_VIEW))->group(function () {
        Route::get('khach-hang', \App\Livewire\Admin\Customers\CustomerManager::class)->name('customers.index');
        Route::get('khach-hang/{customer}/hop-dong', \App\Livewire\Admin\Customers\CustomerContractsView::class)->name('customers.contracts');
    });

    // Cài đặt
    Route::prefix('cai-dat')->name('settings.')->middleware(Permission::toMiddleware(Permission::SETTINGS_VIEW))->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
    });

    // Công văn nội bộ & Phần mềm
    Route::get('cong-van-noi-bo', \App\Livewire\Admin\InternalDocs\InternalDocManager::class)->name('internal-docs.index')->middleware(Permission::toMiddleware(Permission::INTERNAL_DOCS_VIEW));
    Route::get('phan-mem-noi-bo', \App\Livewire\Admin\InternalDocs\SoftwareManager::class)->name('internal-software.index');

    // Thông báo nội bộ
    Route::get('thong-bao-noi-bo', \App\Livewire\Admin\InternalNotifications\InternalNotificationManager::class)
        ->name('internal-notifications.index')
        ->middleware(Role::toMiddleware(Role::IT, Role::GIAM_DOC));

    // Nhật ký công việc
    Route::get('nhat-ky-cong-viec', \App\Livewire\Admin\DailyReports\DailyReportManager::class)->name('daily-reports.index')->middleware(Permission::toMiddleware(Permission::DAILY_REPORTS_VIEW));

    // Lịch công tác
    Route::get('lich-cong-tac', \App\Livewire\Admin\WorkSchedules\WorkScheduleManager::class)->name('work-schedules.index');

    // Kế hoạch content Marketing
    Route::get('marketing/ke-hoach-content', \App\Livewire\Admin\Marketing\MarketingContentManager::class)
        ->name('marketing.content.index')
        ->middleware(Role::toMiddleware(Role::MARKETING, Role::TP_KINH_DOANH, Role::GIAM_DOC, Role::IT));

    // Hoa hồng
    Route::prefix('hoa-hong')->name('commissions.')->middleware(Permission::toMiddleware(Permission::COMMISSIONS_VIEW))->group(function () {
        Route::get('/', \App\Livewire\Admin\Commissions\CommissionRequestManager::class)->name('index');
        Route::get('/tao-moi', \App\Livewire\Admin\Commissions\CommissionRequestForm::class)->name('create');
        Route::get('/{id}/chinh-sua', \App\Livewire\Admin\Commissions\CommissionRequestForm::class)->name('edit');
    });

    // Dòng tiền
    Route::get('tai-chinh/dong-tien', \App\Livewire\Admin\Finance\CashFlowDashboard::class)
        ->name('finance.cash-flow')
        ->middleware(Permission::toMiddleware(Permission::CASH_FLOW_VIEW));

    // Hợp đồng
    Route::prefix('hop-dong')->name('contracts.')->group(function () {
        Route::get('chat-thai-va-tieng-on', \App\Livewire\Admin\Contracts\ContractWasteManager::class)->name('waste.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_WASTE_VIEW));
        Route::get('phap-ly-va-ho-so-mt', \App\Livewire\Admin\Contracts\ContractConsultingManager::class)->name('consulting.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_CONSULTING_VIEW));
        Route::get('ky-thuat-va-ung-pho-sc', \App\Livewire\Admin\Contracts\ContractProjectManager::class)->name('project.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_PROJECT_VIEW));
        Route::get('nc-va-chuyen-doi-cong-nghe', \App\Livewire\Admin\Contracts\ContractCommercialManager::class)->name('commercial.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_COMMERCIAL_VIEW));
        Route::get('tv-va-bao-cao-ptbv', \App\Livewire\Admin\Contracts\ContractSustainabilityManager::class)->name('sustainability.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_SUSTAINABILITY_VIEW));
        Route::get('phat-thai-va-nang-luong', \App\Livewire\Admin\Contracts\ContractEnergyManager::class)->name('energy.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_ENERGY_VIEW));
    });

    // Doanh số
    Route::prefix('doanh-so')->name('sales.')->group(function () {
        Route::get('dang-ky-muc-tieu', \App\Livewire\Admin\Sales\SalesTargetRegistration::class)
            ->name('target-registration')
            ->middleware([Permission::toMiddleware(Permission::QUOTATION_TRACKING_VIEW), Role::toMiddleware(Role::KINH_DOANH, Role::TP_KINH_DOANH)]);
    });

    // Chuyển phát
    Route::get('chuyen-phat-nhanh', \App\Livewire\Admin\PostalDeliveries\PostalDeliveryManager::class)->name('postal-deliveries.index')->middleware(Permission::toMiddleware(Permission::MAIL_DELIVERY_VIEW));

    // Theo dõi báo giá
    Route::get('theo-doi-bao-gia', \App\Livewire\Admin\Quotations\QuotationManager::class)
        ->name('quotation-tracking.index')
        ->middleware([Permission::toMiddleware(Permission::QUOTATION_TRACKING_VIEW), Role::toMiddleware(Role::KINH_DOANH, Role::TP_KINH_DOANH, Role::GIAM_DOC)]);

    // Tạo Báo giá
    Route::prefix('tao-bao-gia')->name('quotation-docs.')->middleware(Permission::toMiddleware(Permission::QUOTATION_TRACKING_VIEW))->group(function () {
        Route::get('/', \App\Livewire\Admin\QuotationDocuments\QuotationDocumentManager::class)->name('index');
        Route::get('/{id}/xuat-word', [\App\Http\Controllers\Admin\QuotationDocumentController::class, 'exportWord'])->name('export-word');
        Route::get('/{id}/xuat-pdf', [\App\Http\Controllers\Admin\QuotationDocumentController::class, 'exportPdf'])->name('export-pdf');
    });

    // Báo cáo Kinh doanh
    Route::prefix('bao-cao/kinh-doanh')->name('reports.sales.')->middleware(Permission::toMiddleware(Permission::REPORTS_SALES_VIEW))->group(function () {
        Route::get('tong-hop', \App\Livewire\Admin\Reports\Sales\SalesSummaryReport::class)->name('summary');
        Route::get('chi-tieu', \App\Livewire\Admin\Reports\Sales\SalesTargetReport::class)->name('target');
        Route::get('ca-nhan', \App\Livewire\Admin\Reports\Sales\PersonalSalesReport::class)->name('personal');
    });

    // Báo cáo Tư vấn
    Route::prefix('bao-cao/tu-van')->name('reports.consulting-work.')->middleware(Permission::toMiddleware(Permission::REPORTS_CONSULTING_VIEW))->group(function () {
        Route::get('chat-thai-va-tieng-on', \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('waste');
        Route::get('phap-ly-va-ho-so-mt', \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('consulting');
        Route::get('ky-thuat-va-ung-pho-sc', \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('project');
        Route::get('nc-va-chuyen-doi-cong-nghe', \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('commercial');
        Route::get('tv-va-bao-cao-ptbv', \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('sustainability');
        Route::get('phat-thai-va-nang-luong', \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('energy');
        Route::get('duong-dua', \App\Livewire\Admin\Reports\Consulting\ConsultingAchievementReport::class)->name('achievement');
    });

    // Báo cáo Kỹ thuật
    Route::prefix('bao-cao/ky-thuat')->name('reports.technical.')->middleware(Permission::toMiddleware(Permission::REPORTS_TECHNICAL_VIEW))->group(function () {
        Route::get('chat-thai-va-tieng-on', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('waste');
        Route::get('phap-ly-va-ho-so-mt', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('consulting');
        Route::get('ky-thuat-va-ung-pho-sc', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('project');
        Route::get('nc-va-chuyen-doi-cong-nghe', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('commercial');
        Route::get('tv-va-bao-cao-ptbv', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('sustainability');
        Route::get('phat-thai-va-nang-luong', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('energy');
        Route::get('duong-dua', \App\Livewire\Admin\Reports\Technical\TechnicalAchievementReport::class)->name('achievement');
    });

    // Báo cáo Marketing
    Route::prefix('bao-cao/marketing')->name('reports.marketing.')->middleware(Permission::toMiddleware(Permission::REPORTS_VIEW))->group(function () {
        Route::get('tong-hop', \App\Livewire\Admin\Reports\Marketing\MarketingSummaryReport::class)->name('summary');
        Route::get('chi-tieu', \App\Livewire\Admin\Reports\Marketing\MarketingTargetReport::class)->name('target');
    });

    // Bảng thống kê & Bảng xếp hạng
    Route::get('thong-ke', \App\Livewire\Admin\StatisticsBoard::class)->name('statistics')->middleware(Permission::toMiddleware(Permission::STATISTICS_VIEW));
    Route::get('xep-hang', \App\Livewire\Admin\RankingsBoard::class)->name('rankings')->middleware(Permission::toMiddleware(Permission::RANKINGS_VIEW));
    Route::get('he-thong', \App\Livewire\Admin\ItDashboard::class)->name('it-dashboard')->middleware(Role::toMiddleware(Role::IT));

    // Nhật ký hoạt động
    Route::get('nhat-ky-hoat-dong', \App\Livewire\Admin\ActivityLogViewer::class)->name('activity-log')->middleware(Permission::toMiddleware(Permission::ACTIVITY_LOG_VIEW));

    // Chấm công
    Route::get('cham-cong', \App\Livewire\Admin\Attendance\AttendanceManager::class)->name('attendance.index')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_VIEW));
    Route::get('cham-cong/nhan-vien', \App\Livewire\Admin\Attendance\AttendanceEmployeeManager::class)->name('attendance.employees')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_EDIT));
    Route::get('cham-cong/xuat-excel/{month}', [\App\Http\Controllers\Admin\AttendanceExportController::class, 'export'])->name('attendance.export')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_EXPORT));
    Route::get('cham-cong/xuat-excel-chitiet/{month}', [\App\Http\Controllers\Admin\AttendanceExportController::class, 'exportDetail'])->name('attendance.export-detail')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_EXPORT));

    // Quản lý hồ sơ nhân sự
    Route::prefix('nhan-su')->name('hr.')->middleware(Permission::toMiddleware(Permission::HR_PROFILES_VIEW))->group(function () {
        Route::get('/', \App\Livewire\Admin\Hr\HrProfileManager::class)->name('index');
        Route::get('/{user}', \App\Livewire\Admin\Hr\HrProfileDetail::class)->name('detail');
    });
});
