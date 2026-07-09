<?php

use App\Enums\Permission;
use App\Enums\Role;
use App\Http\Controllers\Admin\AttendanceExportController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\QuotationDocumentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Admin\ActivityLogViewer;
use App\Livewire\Admin\Attendance\AttendanceEmployeeManager;
use App\Livewire\Admin\Attendance\AttendanceManager;
use App\Livewire\Admin\Commissions\CommissionRequestForm;
use App\Livewire\Admin\Commissions\CommissionRequestManager;
use App\Livewire\Admin\Contracts\ContractCommercialManager;
use App\Livewire\Admin\Contracts\ContractConsultingManager;
use App\Livewire\Admin\Contracts\ContractEnergyManager;
use App\Livewire\Admin\Contracts\ContractProjectManager;
use App\Livewire\Admin\Contracts\ContractSustainabilityManager;
use App\Livewire\Admin\Contracts\ContractWasteManager;
use App\Livewire\Admin\Customers\CustomerContractsView;
use App\Livewire\Admin\Customers\CustomerManager;
use App\Livewire\Admin\DailyReports\DailyReportManager;
use App\Livewire\Admin\Departments\DepartmentManager;
use App\Livewire\Admin\Finance\CashFlowDashboard;
use App\Livewire\Admin\Handlers\HandlerContractsView;
use App\Livewire\Admin\Handlers\HandlerManager;
use App\Livewire\Admin\HomeBoard;
use App\Livewire\Admin\Hr\HrProfileDetail;
use App\Livewire\Admin\Hr\HrProfileManager;
use App\Livewire\Admin\InternalDocs\InternalDocManager;
use App\Livewire\Admin\InternalDocs\SoftwareManager;
use App\Livewire\Admin\InternalNotifications\InternalNotificationManager;
use App\Livewire\Admin\ItDashboard;
use App\Livewire\Admin\Marketing\MarketingContentManager;
use App\Livewire\Admin\PostalDeliveries\PostalDeliveryManager;
use App\Livewire\Admin\QuotationDocuments\QuotationDocumentManager;
use App\Livewire\Admin\Quotations\QuotationManager;
use App\Livewire\Admin\RankingsBoard;
use App\Livewire\Admin\Reports\Consulting\ConsultingAchievementReport;
use App\Livewire\Admin\Reports\Consulting\ConsultingContractReport;
use App\Livewire\Admin\Reports\Marketing\MarketingSummaryReport;
use App\Livewire\Admin\Reports\Marketing\MarketingTargetReport;
use App\Livewire\Admin\Reports\Sales\PersonalSalesReport;
use App\Livewire\Admin\Reports\Sales\SalesProjectProgressReport;
use App\Livewire\Admin\Reports\Sales\SalesSummaryReport;
use App\Livewire\Admin\Reports\Sales\SalesTargetReport;
use App\Livewire\Admin\Reports\Technical\TechnicalAchievementReport;
use App\Livewire\Admin\Reports\Technical\TechnicalContractReport;
use App\Livewire\Admin\Roles\RoleManager;
use App\Livewire\Admin\Sales\SalesTargetRegistration;
use App\Livewire\Admin\StatisticsBoard;
use App\Livewire\Admin\Users\UserManager;
use App\Livewire\Admin\WorkSchedules\WorkScheduleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->hasRole(Role::THUC_TAP->value)) {
        return redirect()->route('app.daily-reports.index');
    }

    return redirect()->route('app.home');
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
    $target = '/'.ltrim($path ?: 'dashboard', '/');
    $query = $request->getQueryString();

    if ($query) {
        $target .= '?'.$query;
    }

    return redirect($target, 301);
})->where('path', '.*');

Route::middleware(['auth', 'active', 'intern.daily-report'])->name('app.')->group(function () {
    Route::get('/', HomeBoard::class)->name('home');
    Route::get('/bang-dieu-khien', StatisticsBoard::class)->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [SettingController::class, 'profile'])->name('index');
        Route::post('/', [SettingController::class, 'updateProfile'])->name('update');
    });

    Route::get('/change-password', [SettingController::class, 'password'])->name('password.index');
    Route::post('/change-password', [SettingController::class, 'updatePassword'])->name('password.update');

    // Người dùng
    Route::middleware(Permission::toMiddleware(Permission::USERS_VIEW))->group(function () {
        Route::get('users', UserManager::class)->name('users.index');
        Route::resource('users', UserController::class)->except(['index']);
    });

    // Vai trò
    Route::middleware(Permission::toMiddleware(Permission::ROLES_VIEW))->group(function () {
        Route::get('roles', RoleManager::class)->name('roles.index');
        Route::resource('roles', RoleController::class)->except(['index']);
    });

    // Phòng ban
    Route::middleware(Permission::toMiddleware(Permission::DEPARTMENTS_VIEW))->group(function () {
        Route::get('phong-ban', DepartmentManager::class)->name('departments.index');
        Route::resource('departments', DepartmentController::class)->except(['index']);
    });

    // Nhà thầu phụ
    Route::middleware(Permission::toMiddleware(Permission::HANDLERS_VIEW))->group(function () {
        Route::get('nha-thau-phu', HandlerManager::class)->name('handlers.index');
        Route::get('nha-thau-phu/{handler}/hop-dong', HandlerContractsView::class)->name('handlers.contracts');
    });

    // Khách hàng
    Route::middleware(Permission::toMiddleware(Permission::CUSTOMERS_VIEW))->group(function () {
        Route::get('khach-hang', CustomerManager::class)->name('customers.index');
        Route::get('khach-hang/{customer}/hop-dong', CustomerContractsView::class)->name('customers.contracts');
    });

    // Cài đặt
    Route::prefix('cai-dat')->name('settings.')->middleware(Permission::toMiddleware(Permission::SETTINGS_VIEW))->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
    });

    // Công văn nội bộ & Phần mềm
    Route::get('cong-van-noi-bo', InternalDocManager::class)->name('internal-docs.index')->middleware(Permission::toMiddleware(Permission::INTERNAL_DOCS_VIEW));
    Route::get('phan-mem-noi-bo', SoftwareManager::class)->name('internal-software.index');

    // Thông báo nội bộ
    Route::get('thong-bao-noi-bo', InternalNotificationManager::class)
        ->name('internal-notifications.index')
        ->middleware(Role::toMiddleware(Role::IT));

    // Nhật ký công việc
    Route::get('nhat-ky-cong-viec', DailyReportManager::class)->name('daily-reports.index')->middleware(Permission::toMiddleware(Permission::DAILY_REPORTS_VIEW));

    // Lịch công tác
    Route::get('lich-cong-tac', WorkScheduleManager::class)->name('work-schedules.index');

    // Kế hoạch content Marketing
    Route::get('marketing/ke-hoach-content', MarketingContentManager::class)
        ->name('marketing.content.index')
        ->middleware(Role::toMiddleware(Role::MARKETING, Role::TP_KINH_DOANH, Role::KINH_DOANH, Role::GIAM_DOC, Role::IT));

    // Hoa hồng
    Route::prefix('hoa-hong')->name('commissions.')->middleware(Permission::toMiddleware(Permission::COMMISSIONS_VIEW))->group(function () {
        Route::get('/', CommissionRequestManager::class)->name('index');
        Route::get('/tao-moi', CommissionRequestForm::class)->name('create');
        Route::get('/{id}/chinh-sua', CommissionRequestForm::class)->name('edit');
    });

    // Dòng tiền
    Route::get('tai-chinh/dong-tien', CashFlowDashboard::class)
        ->name('finance.cash-flow')
        ->middleware(Permission::toMiddleware(Permission::CASH_FLOW_VIEW));

    // Hợp đồng
    Route::prefix('hop-dong')->name('contracts.')->group(function () {
        Route::get('chat-thai-va-tieng-on', ContractWasteManager::class)->name('waste.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_WASTE_VIEW));
        Route::get('phap-ly-va-ho-so-mt', ContractConsultingManager::class)->name('consulting.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_CONSULTING_VIEW));
        Route::get('ky-thuat-va-ung-pho-sc', ContractProjectManager::class)->name('project.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_PROJECT_VIEW));
        Route::get('nc-va-chuyen-doi-cong-nghe', ContractCommercialManager::class)->name('commercial.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_COMMERCIAL_VIEW));
        Route::get('tv-va-bao-cao-ptbv', ContractSustainabilityManager::class)->name('sustainability.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_SUSTAINABILITY_VIEW));
        Route::get('phat-thai-va-nang-luong', ContractEnergyManager::class)->name('energy.index')->middleware(Permission::toMiddleware(Permission::CONTRACTS_ENERGY_VIEW));
    });

    // Doanh số
    Route::prefix('doanh-so')->name('sales.')->group(function () {
        Route::get('dang-ky-muc-tieu', SalesTargetRegistration::class)
            ->name('target-registration')
            ->middleware([Permission::toMiddleware(Permission::QUOTATION_TRACKING_VIEW), Role::toMiddleware(Role::KINH_DOANH, Role::TP_KINH_DOANH)]);
    });

    // Chuyển phát
    Route::get('chuyen-phat-nhanh', PostalDeliveryManager::class)->name('postal-deliveries.index')->middleware(Permission::toMiddleware(Permission::MAIL_DELIVERY_VIEW));

    // Theo dõi báo giá
    Route::get('theo-doi-bao-gia', QuotationManager::class)
        ->name('quotation-tracking.index')
        ->middleware([Permission::toMiddleware(Permission::QUOTATION_TRACKING_VIEW), Role::toMiddleware(Role::KINH_DOANH, Role::TP_KINH_DOANH, Role::GIAM_DOC)]);

    // Tạo Báo giá
    Route::prefix('tao-bao-gia')->name('quotation-docs.')->middleware(Permission::toMiddleware(Permission::QUOTATION_TRACKING_VIEW))->group(function () {
        Route::get('/', QuotationDocumentManager::class)
            ->name('index')
            ->middleware([Permission::toMiddleware(Permission::QUOTATION_TRACKING_CREATE), Role::toMiddleware(Role::KINH_DOANH, Role::TP_KINH_DOANH)]);
        Route::get('/{id}/xuat-word', [QuotationDocumentController::class, 'exportWord'])->name('export-word');
        Route::get('/{id}/xuat-pdf', [QuotationDocumentController::class, 'exportPdf'])->name('export-pdf');
    });

    // Báo cáo Kinh doanh
    Route::prefix('bao-cao/kinh-doanh')->name('reports.sales.')->middleware(Permission::toMiddleware(Permission::REPORTS_SALES_VIEW))->group(function () {
        Route::get('tong-hop', SalesSummaryReport::class)->name('summary');
        Route::get('chi-tieu', SalesTargetReport::class)->name('target');
        Route::get('ca-nhan', PersonalSalesReport::class)->name('personal');
        Route::get('tien-do-du-an', SalesProjectProgressReport::class)->name('project-progress');
    });

    // Báo cáo Tư vấn
    Route::prefix('bao-cao/tu-van')->name('reports.consulting-work.')->middleware(Permission::toMiddleware(Permission::REPORTS_CONSULTING_VIEW))->group(function () {
        Route::get('chat-thai-va-tieng-on', ConsultingContractReport::class)->name('waste');
        Route::get('phap-ly-va-ho-so-mt', ConsultingContractReport::class)->name('consulting');
        Route::get('ky-thuat-va-ung-pho-sc', ConsultingContractReport::class)->name('project');
        Route::get('nc-va-chuyen-doi-cong-nghe', ConsultingContractReport::class)->name('commercial');
        Route::get('tv-va-bao-cao-ptbv', ConsultingContractReport::class)->name('sustainability');
        Route::get('phat-thai-va-nang-luong', ConsultingContractReport::class)->name('energy');
        Route::get('duong-dua', ConsultingAchievementReport::class)->name('achievement');
    });

    // Báo cáo Kỹ thuật
    Route::prefix('bao-cao/ky-thuat')->name('reports.technical.')->middleware(Permission::toMiddleware(Permission::REPORTS_TECHNICAL_VIEW))->group(function () {
        Route::get('chat-thai-va-tieng-on', TechnicalContractReport::class)->name('waste');
        Route::get('phap-ly-va-ho-so-mt', TechnicalContractReport::class)->name('consulting');
        Route::get('ky-thuat-va-ung-pho-sc', TechnicalContractReport::class)->name('project');
        Route::get('nc-va-chuyen-doi-cong-nghe', TechnicalContractReport::class)->name('commercial');
        Route::get('tv-va-bao-cao-ptbv', TechnicalContractReport::class)->name('sustainability');
        Route::get('phat-thai-va-nang-luong', TechnicalContractReport::class)->name('energy');
        Route::get('duong-dua', TechnicalAchievementReport::class)->name('achievement');
    });

    // Báo cáo Marketing
    Route::prefix('bao-cao/marketing')->name('reports.marketing.')->middleware(Permission::toMiddleware(Permission::REPORTS_VIEW))->group(function () {
        Route::get('tong-hop', MarketingSummaryReport::class)->name('summary');
        Route::get('chi-tieu', MarketingTargetReport::class)->name('target');
    });

    // Bảng thống kê & Bảng xếp hạng
    Route::get('thong-ke', StatisticsBoard::class)->name('statistics')->middleware(Permission::toMiddleware(Permission::STATISTICS_VIEW));
    Route::get('xep-hang', RankingsBoard::class)->name('rankings')->middleware(Permission::toMiddleware(Permission::RANKINGS_VIEW));
    Route::get('he-thong', ItDashboard::class)->name('it-dashboard')->middleware(Role::toMiddleware(Role::IT));

    // Nhật ký hoạt động
    Route::get('nhat-ky-hoat-dong', ActivityLogViewer::class)->name('activity-log')->middleware(Permission::toMiddleware(Permission::ACTIVITY_LOG_VIEW));

    // Chấm công
    Route::get('cham-cong', AttendanceManager::class)->name('attendance.index')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_VIEW));
    Route::get('cham-cong/nhan-vien', AttendanceEmployeeManager::class)->name('attendance.employees')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_EDIT));
    Route::get('cham-cong/xuat-excel/{month}', [AttendanceExportController::class, 'export'])->name('attendance.export')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_EXPORT));
    Route::get('cham-cong/xuat-excel-chitiet/{month}', [AttendanceExportController::class, 'exportDetail'])->name('attendance.export-detail')->middleware(Permission::toMiddleware(Permission::CHAM_CONG_EXPORT));

    // Quản lý hồ sơ nhân sự
    Route::prefix('nhan-su')->name('hr.')->middleware(Permission::toMiddleware(Permission::HR_PROFILES_VIEW))->group(function () {
        Route::get('/', HrProfileManager::class)->name('index');
        Route::get('/{user}', HrProfileDetail::class)->name('detail');
    });
});
