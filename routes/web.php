<?php

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
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', \App\Livewire\Admin\Users\UserManager::class)->name('users.index');
        Route::resource('users', UserController::class)->except(['index']);
    });

    // Vai trò
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('roles', \App\Livewire\Admin\Roles\RoleManager::class)->name('roles.index');
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['index']);
    });

    // Phòng ban
    Route::middleware('permission:departments.view')->group(function () {
        Route::get('phong-ban', \App\Livewire\Admin\Departments\DepartmentManager::class)->name('departments.index');
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['index']);
    });

    // Nhà thầu phụ
    Route::middleware('permission:handlers.view')->group(function () {
        Route::get('nha-thau-phu', \App\Livewire\Admin\Handlers\HandlerManager::class)->name('handlers.index');
        Route::get('nha-thau-phu/{handler}/hop-dong', \App\Livewire\Admin\Handlers\HandlerContractsView::class)->name('handlers.contracts');
    });

    // Khách hàng
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('khach-hang', \App\Livewire\Admin\Customers\CustomerManager::class)->name('customers.index');
        Route::get('khach-hang/{customer}/hop-dong', \App\Livewire\Admin\Customers\CustomerContractsView::class)->name('customers.contracts');
    });

    // Cài đặt
    Route::prefix('cai-dat')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
    });

    // Công văn nội bộ
    Route::get('cong-van-noi-bo', \App\Livewire\Admin\InternalDocs\InternalDocManager::class)->name('internal-docs.index')->middleware('permission:internal-docs.view');

    // Nhật ký công việc
    Route::get('nhat-ky-cong-viec', \App\Livewire\Admin\DailyReports\DailyReportManager::class)->name('daily-reports.index')->middleware('permission:daily-reports.view');

    // Báo cáo hàng ngày Marketing
    Route::get('marketing/bao-cao-hang-ngay', \App\Livewire\Admin\Marketing\MarketingReportManager::class)->name('marketing.daily-report.index')->middleware('permission:marketing-reports.view');

    // Hoa hồng
    Route::prefix('hoa-hong')->name('commissions.')->middleware('permission:commissions.view')->group(function () {
        Route::get('/', \App\Livewire\Admin\Commissions\CommissionRequestManager::class)->name('index');
        Route::get('/tao-moi', \App\Livewire\Admin\Commissions\CommissionRequestForm::class)->name('create');
        Route::get('/{id}/chinh-sua', \App\Livewire\Admin\Commissions\CommissionRequestForm::class)->name('edit');
    });

    // Hợp đồng
    Route::prefix('hop-dong')->name('contracts.')->group(function () {
        Route::get('chat-thai-va-tieng-on', \App\Livewire\Admin\Contracts\ContractWasteManager::class)->name('waste.index')->middleware('permission:contracts-waste.view');
        Route::get('phap-ly-va-ho-so-mt', \App\Livewire\Admin\Contracts\ContractConsultingManager::class)->name('consulting.index')->middleware('permission:contracts-consulting.view');
        Route::get('ky-thuat-va-ung-pho-sc', \App\Livewire\Admin\Contracts\ContractProjectManager::class)->name('project.index')->middleware('permission:contracts-project.view');
        Route::get('nc-va-chuyen-doi-cong-nghe', \App\Livewire\Admin\Contracts\ContractCommercialManager::class)->name('commercial.index')->middleware('permission:contracts-commercial.view');
        Route::get('tv-va-bao-cao-ptbv', \App\Livewire\Admin\Contracts\ContractSustainabilityManager::class)->name('sustainability.index')->middleware('permission:contracts-sustainability.view');
        Route::get('phat-thai-va-nang-luong', \App\Livewire\Admin\Contracts\ContractEnergyManager::class)->name('energy.index')->middleware('permission:contracts-energy.view');
    });

    // Doanh số
    Route::prefix('doanh-so')->name('sales.')->group(function () {
        Route::get('tai-ky', \App\Livewire\Admin\Sales\RenewalSalesManager::class)->name('renewal.index')->middleware('permission:sales-renewal.view');
        Route::get('tien-do-thanh-toan', \App\Livewire\Admin\Sales\ProgressiveSalesManager::class)->name('progressive.index')->middleware('permission:sales-progressive.view');
        Route::get('dang-ky-muc-tieu', \App\Livewire\Admin\Sales\SalesTargetRegistration::class)
            ->name('target-registration')
            ->middleware(['permission:sales-renewal.view', 'role:kinh-doanh,tp-kinh-doanh']);
    });

    // Chuyển phát
    Route::get('chuyen-phat-nhanh', \App\Livewire\Admin\PostalDeliveries\PostalDeliveryManager::class)->name('postal-deliveries.index')->middleware('permission:mail-delivery.view');

    // Theo dõi báo giá
    Route::get('theo-doi-bao-gia', \App\Livewire\Admin\Quotations\QuotationManager::class)
        ->name('quotation-tracking.index')
        ->middleware(['permission:quotation-tracking.view', 'role:kinh-doanh,tp-kinh-doanh,giam-doc']);

    // Báo cáo Kinh doanh
    Route::prefix('bao-cao/kinh-doanh')->name('reports.sales.')->middleware('permission:reports-sales.view')->group(function () {
        Route::get('tong-hop',           \App\Livewire\Admin\Reports\Sales\SalesSummaryReport::class)->name('summary');
        Route::get('chi-tieu',           \App\Livewire\Admin\Reports\Sales\SalesTargetReport::class)->name('target');
        Route::get('tong-quan',          \App\Livewire\Admin\Reports\Sales\SalesOverviewReport::class)->name('overview');
        Route::get('ca-nhan',            \App\Livewire\Admin\Reports\Sales\PersonalSalesReport::class)->name('personal');
        Route::get('tai-ky-ca-nhan',     \App\Livewire\Admin\Reports\Sales\PersonalRenewalReport::class)->name('renewal-personal');
        Route::get('thanh-tich',         \App\Livewire\Admin\Reports\Sales\SalesAchievementReport::class)->name('achievement');
        Route::get('theo-doi',           \App\Livewire\Admin\Reports\Sales\SalesTrackingReport::class)->name('tracking');
        Route::get('doanh-thu',          \App\Livewire\Admin\Reports\Sales\SalesRevenueReport::class)->name('revenue');
    });

    // Quản lý hóa đơn
    Route::get('hoa-don/bao-chau',    \App\Livewire\Admin\Invoices\InvoiceBaoChauManager::class)->name('invoices.bao-chau')->middleware('permission:invoices.view');
    Route::get('hoa-don/chu-xu-ly',   \App\Livewire\Admin\Invoices\InvoiceHandlerManager::class)->name('invoices.handlers')->middleware('permission:handler-invoices.view');

    // Báo cáo Tư vấn
    Route::prefix('bao-cao/tu-van')->name('reports.consulting-work.')->middleware('permission:reports-consulting.view')->group(function () {
        Route::get('chat-thai-va-tieng-on',       \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('waste');
        Route::get('phap-ly-va-ho-so-mt',         \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('consulting');
        Route::get('ky-thuat-va-ung-pho-sc',      \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('project');
        Route::get('nc-va-chuyen-doi-cong-nghe',  \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('commercial');
        Route::get('tv-va-bao-cao-ptbv',          \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('sustainability');
        Route::get('phat-thai-va-nang-luong',     \App\Livewire\Admin\Reports\Consulting\ConsultingContractReport::class)->name('energy');
        Route::get('duong-dua',                   \App\Livewire\Admin\Reports\Consulting\ConsultingAchievementReport::class)->name('achievement');
    });

    // Báo cáo Kỹ thuật
    Route::prefix('bao-cao/ky-thuat')->name('reports.technical.')->middleware('permission:reports-technical.view')->group(function () {
        Route::get('chat-thai-va-tieng-on',      \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('waste');
        Route::get('phap-ly-va-ho-so-mt',        \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('consulting');
        Route::get('ky-thuat-va-ung-pho-sc',     \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('project');
        Route::get('nc-va-chuyen-doi-cong-nghe', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('commercial');
        Route::get('tv-va-bao-cao-ptbv',         \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('sustainability');
        Route::get('phat-thai-va-nang-luong',    \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('energy');
        Route::get('duong-dua',                  \App\Livewire\Admin\Reports\Technical\TechnicalAchievementReport::class)->name('achievement');
    });

    // Báo cáo Marketing
    Route::prefix('bao-cao/marketing')->name('reports.marketing.')->middleware('permission:reports.view')->group(function () {
        Route::get('tong-hop', \App\Livewire\Admin\Reports\Marketing\MarketingSummaryReport::class)->name('summary');
        Route::get('chi-tieu', \App\Livewire\Admin\Reports\Marketing\MarketingTargetReport::class)->name('target');
    });

    // Bảng thống kê & Bảng xếp hạng
    Route::get('thong-ke',    \App\Livewire\Admin\StatisticsBoard::class)->name('statistics')->middleware('permission:statistics.view');
    Route::get('xep-hang',    \App\Livewire\Admin\RankingsBoard::class)->name('rankings')->middleware('permission:rankings.view');

    // Nhật ký hoạt động
    Route::get('nhat-ky-hoat-dong', \App\Livewire\Admin\ActivityLogViewer::class)->name('activity-log')->middleware('permission:activity-log.view');
});
