<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('app.dashboard')
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
Route::middleware('auth')->get('/admin/{path?}', function (Request $request, ?string $path = null) {
    $target = '/' . ltrim($path ?: 'dashboard', '/');
    $query = $request->getQueryString();

    if ($query) {
        $target .= '?' . $query;
    }

    return redirect($target, 301);
})->where('path', '.*');

Route::middleware('auth')->name('app.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\StatisticsBoard::class)->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [SettingController::class, 'profile'])->name('index');
        Route::post('/', [SettingController::class, 'updateProfile'])->name('update');
    });

    Route::get('/password', [SettingController::class, 'password'])->name('password.index');
    Route::post('/password', [SettingController::class, 'updatePassword'])->name('password.update');

    // Users CRUD
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', \App\Livewire\Admin\Users\UserManager::class)->name('users.index');
        Route::resource('users', UserController::class)->except(['index']);
    });

    // Roles CRUD
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('roles', \App\Livewire\Admin\Roles\RoleManager::class)->name('roles.index');
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['index']);
    });

    // Departments CRUD
    Route::middleware('permission:departments.view')->group(function () {
        Route::get('departments', \App\Livewire\Admin\Departments\DepartmentManager::class)->name('departments.index');
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['index']);
    });

    // Handlers CRUD
    Route::middleware('permission:handlers.view')->group(function () {
        Route::get('handlers', \App\Livewire\Admin\Handlers\HandlerManager::class)->name('handlers.index');
    });

    // Customers CRUD
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('customers', \App\Livewire\Admin\Customers\CustomerManager::class)->name('customers.index');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
    });

    // Internal Docs
    Route::get('internal-docs', \App\Livewire\Admin\InternalDocs\InternalDocManager::class)->name('internal-docs.index')->middleware('permission:internal-docs.view');

    // Daily Reports
    Route::get('daily-reports', \App\Livewire\Admin\DailyReports\DailyReportManager::class)->name('daily-reports.index')->middleware('permission:daily-reports.view');

    // Marketing Daily Reports
    Route::get('marketing/bao-cao-hang-ngay', \App\Livewire\Admin\Marketing\MarketingReportManager::class)->name('marketing.daily-report.index')->middleware('permission:marketing-reports.view');

    // Commissions
    Route::prefix('commissions')->name('commissions.')->middleware('permission:commissions.view')->group(function () {
        Route::get('/', \App\Livewire\Admin\Commissions\CommissionRequestManager::class)->name('index');
        Route::get('/create', \App\Livewire\Admin\Commissions\CommissionRequestForm::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Admin\Commissions\CommissionRequestForm::class)->name('edit');
    });

    // Contracts
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('hop-dong-chat-thai-va-tieng-on', \App\Livewire\Admin\Contracts\ContractWasteManager::class)->name('waste.index')->middleware('permission:contracts-waste.view');
        Route::get('hop-dong-phap-ly-va-ho-so-mt', \App\Livewire\Admin\Contracts\ContractConsultingManager::class)->name('consulting.index')->middleware('permission:contracts-consulting.view');
        Route::get('hop-dong-ky-thuat-va-ung-pho-sc', \App\Livewire\Admin\Contracts\ContractProjectManager::class)->name('project.index')->middleware('permission:contracts-project.view');
        Route::get('hop-dong-nc-va-chuyen-doi-cong-nghe', \App\Livewire\Admin\Contracts\ContractCommercialManager::class)->name('commercial.index')->middleware('permission:contracts-commercial.view');
        Route::get('hop-dong-tv-va-bao-cao-ptbv', \App\Livewire\Admin\Contracts\ContractSustainabilityManager::class)->name('sustainability.index')->middleware('permission:contracts-sustainability.view');
        Route::get('hop-dong-phat-thai-va-nang-luong', \App\Livewire\Admin\Contracts\ContractEnergyManager::class)->name('energy.index')->middleware('permission:contracts-energy.view');
    });

    // Sales Department
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('renewal', \App\Livewire\Admin\Sales\RenewalSalesManager::class)->name('renewal.index')->middleware('permission:sales-renewal.view');
        Route::get('progressive', \App\Livewire\Admin\Sales\ProgressiveSalesManager::class)->name('progressive.index')->middleware('permission:sales-progressive.view');
    });

    // Postal Deliveries
    Route::get('postal-deliveries', \App\Livewire\Admin\PostalDeliveries\PostalDeliveryManager::class)->name('postal-deliveries.index')->middleware('permission:mail-delivery.view');

    // Quotation Tracking
    Route::get('quotation-tracking', \App\Livewire\Admin\Quotations\QuotationManager::class)->name('quotation-tracking.index')->middleware('permission:quotation-tracking.view');

    // Báo cáo Kinh doanh
    Route::prefix('reports/sales')->name('reports.sales.')->middleware('permission:reports.view')->group(function () {
        Route::get('summary',         \App\Livewire\Admin\Reports\Sales\SalesSummaryReport::class)->name('summary');
        Route::get('target',          \App\Livewire\Admin\Reports\Sales\SalesTargetReport::class)->name('target');
        Route::get('overview',        \App\Livewire\Admin\Reports\Sales\SalesOverviewReport::class)->name('overview');
        Route::get('personal',        \App\Livewire\Admin\Reports\Sales\PersonalSalesReport::class)->name('personal');
        Route::get('renewal-personal',\App\Livewire\Admin\Reports\Sales\PersonalRenewalReport::class)->name('renewal-personal');
        Route::get('achievement',     \App\Livewire\Admin\Reports\Sales\SalesAchievementReport::class)->name('achievement');
        Route::get('tracking',        \App\Livewire\Admin\Reports\Sales\SalesTrackingReport::class)->name('tracking');
        Route::get('revenue',         \App\Livewire\Admin\Reports\Sales\SalesRevenueReport::class)->name('revenue');
    });

    // Quản lý hóa đơn
    Route::get('invoices/bao-chau',  \App\Livewire\Admin\Invoices\InvoiceBaoChauManager::class)->name('invoices.bao-chau')->middleware('permission:invoices.view');
    Route::get('invoices/handlers',  \App\Livewire\Admin\Invoices\InvoiceHandlerManager::class)->name('invoices.handlers')->middleware('permission:handler-invoices.view');

    // Báo cáo Tư vấn
    Route::prefix('reports/consulting')->name('reports.consulting.')->middleware('permission:reports.view')->group(function () {
        Route::get('general',    \App\Livewire\Admin\Reports\Consulting\ConsultingGeneralReport::class)->name('general');
        Route::get('monitoring', \App\Livewire\Admin\Reports\Consulting\ConsultingMonitoringReport::class)->name('monitoring');
        Route::get('all',        \App\Livewire\Admin\Reports\Consulting\ConsultingServiceReport::class)->name('all');
        Route::get('gpmt',       \App\Livewire\Admin\Reports\Consulting\ConsultingServiceReport::class)->name('gpmt');
        Route::get('dkmt',       \App\Livewire\Admin\Reports\Consulting\ConsultingServiceReport::class)->name('dkmt');
        Route::get('vhtn',       \App\Livewire\Admin\Reports\Consulting\ConsultingServiceReport::class)->name('vhtn');
    });

    // Báo cáo Kỹ thuật
    Route::prefix('reports/technical')->name('reports.technical.')->middleware('permission:reports.view')->group(function () {
        Route::get('waste',          \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('waste');
        Route::get('consulting',     \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('consulting');
        Route::get('project',        \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('project');
        Route::get('commercial',     \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('commercial');
        Route::get('sustainability', \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('sustainability');
        Route::get('energy',         \App\Livewire\Admin\Reports\Technical\TechnicalContractReport::class)->name('energy');
    });

    // Báo cáo Marketing
    Route::prefix('reports/marketing')->name('reports.marketing.')->middleware('permission:reports.view')->group(function () {
        Route::get('summary', \App\Livewire\Admin\Reports\Marketing\MarketingSummaryReport::class)->name('summary');
        Route::get('target',  \App\Livewire\Admin\Reports\Marketing\MarketingTargetReport::class)->name('target');
    });

    // Bảng thống kê & Bảng xếp hạng
    Route::get('statistics', \App\Livewire\Admin\StatisticsBoard::class)->name('statistics')->middleware('permission:statistics.view');
    Route::get('rankings',   \App\Livewire\Admin\RankingsBoard::class)->name('rankings')->middleware('permission:rankings.view');

    // Nhật ký hoạt động (Activity Log)
    Route::get('activity-log', \App\Livewire\Admin\ActivityLogViewer::class)->name('activity-log')->middleware('permission:activity-log.view');
});
