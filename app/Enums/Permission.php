<?php

namespace App\Enums;

enum Permission: string
{
    // Users
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_EDIT = 'users.edit';
    case USERS_DELETE = 'users.delete';

    // Roles
    case ROLES_VIEW = 'roles.view';
    case ROLES_CREATE = 'roles.create';
    case ROLES_EDIT = 'roles.edit';
    case ROLES_DELETE = 'roles.delete';

    // Departments
    case DEPARTMENTS_VIEW = 'departments.view';
    case DEPARTMENTS_CREATE = 'departments.create';
    case DEPARTMENTS_EDIT = 'departments.edit';
    case DEPARTMENTS_DELETE = 'departments.delete';

    // Settings
    case SETTINGS_VIEW = 'settings.view';
    case SETTINGS_EDIT = 'settings.edit';

    // Handlers
    case HANDLERS_VIEW = 'handlers.view';
    case HANDLERS_CREATE = 'handlers.create';
    case HANDLERS_EDIT = 'handlers.edit';
    case HANDLERS_DELETE = 'handlers.delete';

    // Customers
    case CUSTOMERS_VIEW = 'customers.view';
    case CUSTOMERS_CREATE = 'customers.create';
    case CUSTOMERS_EDIT = 'customers.edit';
    case CUSTOMERS_DELETE = 'customers.delete';

    // Contracts
    case CONTRACTS_WASTE_VIEW = 'contracts-waste.view';
    case CONTRACTS_WASTE_CREATE = 'contracts-waste.create';
    case CONTRACTS_WASTE_EDIT = 'contracts-waste.edit';
    case CONTRACTS_WASTE_DELETE = 'contracts-waste.delete';
    case CONTRACTS_CONSULTING_VIEW = 'contracts-consulting.view';
    case CONTRACTS_CONSULTING_CREATE = 'contracts-consulting.create';
    case CONTRACTS_CONSULTING_EDIT = 'contracts-consulting.edit';
    case CONTRACTS_CONSULTING_DELETE = 'contracts-consulting.delete';
    case CONTRACTS_PROJECT_VIEW = 'contracts-project.view';
    case CONTRACTS_PROJECT_CREATE = 'contracts-project.create';
    case CONTRACTS_PROJECT_EDIT = 'contracts-project.edit';
    case CONTRACTS_PROJECT_DELETE = 'contracts-project.delete';
    case CONTRACTS_COMMERCIAL_VIEW = 'contracts-commercial.view';
    case CONTRACTS_COMMERCIAL_CREATE = 'contracts-commercial.create';
    case CONTRACTS_COMMERCIAL_EDIT = 'contracts-commercial.edit';
    case CONTRACTS_COMMERCIAL_DELETE = 'contracts-commercial.delete';
    case CONTRACTS_SUSTAINABILITY_VIEW = 'contracts-sustainability.view';
    case CONTRACTS_SUSTAINABILITY_CREATE = 'contracts-sustainability.create';
    case CONTRACTS_SUSTAINABILITY_EDIT = 'contracts-sustainability.edit';
    case CONTRACTS_SUSTAINABILITY_DELETE = 'contracts-sustainability.delete';
    case CONTRACTS_ENERGY_VIEW = 'contracts-energy.view';
    case CONTRACTS_ENERGY_CREATE = 'contracts-energy.create';
    case CONTRACTS_ENERGY_EDIT = 'contracts-energy.edit';
    case CONTRACTS_ENERGY_DELETE = 'contracts-energy.delete';

    // Payment & Invoices
    case PAYMENT_SCHEDULES_VIEW = 'payment-schedules.view';
    case PAYMENT_SCHEDULES_CREATE = 'payment-schedules.create';
    case PAYMENT_SCHEDULES_EDIT = 'payment-schedules.edit';
    case PAYMENT_SCHEDULES_DELETE = 'payment-schedules.delete';
    // Sales
    case SALES_PROGRESSIVE_VIEW = 'sales-progressive.view';
    case SALES_PROGRESSIVE_CREATE = 'sales-progressive.create';
    case SALES_PROGRESSIVE_EDIT = 'sales-progressive.edit';
    case SALES_PROGRESSIVE_DELETE = 'sales-progressive.delete';
    case QUOTATION_TRACKING_VIEW = 'quotation-tracking.view';
    case QUOTATION_TRACKING_CREATE = 'quotation-tracking.create';
    case QUOTATION_TRACKING_EDIT = 'quotation-tracking.edit';
    case QUOTATION_TRACKING_DELETE = 'quotation-tracking.delete';

    // Finance
    case COMMISSIONS_VIEW = 'commissions.view';
    case COMMISSIONS_CREATE = 'commissions.create';
    case COMMISSIONS_EDIT = 'commissions.edit';
    case COMMISSIONS_DELETE = 'commissions.delete';
    case ADVANCE_REQUESTS_VIEW = 'advance-requests.view';
    case ADVANCE_REQUESTS_CREATE = 'advance-requests.create';
    case ADVANCE_REQUESTS_EDIT = 'advance-requests.edit';
    case ADVANCE_REQUESTS_DELETE = 'advance-requests.delete';

    // Cash Flow
    case CASH_FLOW_VIEW = 'cash-flow.view';
    case CASH_FLOW_EXPORT = 'cash-flow.export';

    // Operations Requests
    case WASTE_REQUESTS_VIEW = 'waste-requests.view';
    case WASTE_REQUESTS_CREATE = 'waste-requests.create';
    case WASTE_REQUESTS_EDIT = 'waste-requests.edit';
    case WASTE_REQUESTS_DELETE = 'waste-requests.delete';
    case CONSULTING_REQUESTS_VIEW = 'consulting-requests.view';
    case CONSULTING_REQUESTS_CREATE = 'consulting-requests.create';
    case CONSULTING_REQUESTS_EDIT = 'consulting-requests.edit';
    case CONSULTING_REQUESTS_DELETE = 'consulting-requests.delete';
    case PROJECT_REQUESTS_VIEW = 'project-requests.view';
    case PROJECT_REQUESTS_CREATE = 'project-requests.create';
    case PROJECT_REQUESTS_EDIT = 'project-requests.edit';
    case PROJECT_REQUESTS_DELETE = 'project-requests.delete';
    case COMMERCIAL_REQUESTS_VIEW = 'commercial-requests.view';
    case COMMERCIAL_REQUESTS_CREATE = 'commercial-requests.create';
    case COMMERCIAL_REQUESTS_EDIT = 'commercial-requests.edit';
    case COMMERCIAL_REQUESTS_DELETE = 'commercial-requests.delete';
    case TECHNICAL_REQUESTS_VIEW = 'technical-requests.view';
    case TECHNICAL_REQUESTS_CREATE = 'technical-requests.create';
    case TECHNICAL_REQUESTS_EDIT = 'technical-requests.edit';
    case TECHNICAL_REQUESTS_DELETE = 'technical-requests.delete';

    // Mail Delivery
    case MAIL_DELIVERY_VIEW = 'mail-delivery.view';
    case MAIL_DELIVERY_CREATE = 'mail-delivery.create';
    case MAIL_DELIVERY_EDIT = 'mail-delivery.edit';
    case MAIL_DELIVERY_DELETE = 'mail-delivery.delete';
    case MAIL_DELIVERY_ADMIN_VIEW = 'mail-delivery-admin.view';

    // Analytics
    case RANKINGS_VIEW = 'rankings.view';
    case STATISTICS_VIEW = 'statistics.view';

    // Reports
    case REPORTS_VIEW = 'reports.view';
    case REPORTS_SALES_VIEW = 'reports-sales.view';
    case REPORTS_CONSULTING_VIEW = 'reports-consulting.view';
    case REPORTS_TECHNICAL_VIEW = 'reports-technical.view';

    // Content
    case INTERNAL_DOCS_VIEW = 'internal-docs.view';
    case INTERNAL_DOCS_CREATE = 'internal-docs.create';
    case INTERNAL_DOCS_EDIT = 'internal-docs.edit';
    case INTERNAL_DOCS_DELETE = 'internal-docs.delete';
    case ARTICLES_VIEW = 'articles.view';
    case ARTICLES_CREATE = 'articles.create';
    case ARTICLES_EDIT = 'articles.edit';
    case ARTICLES_DELETE = 'articles.delete';

    // Daily Reports
    case DAILY_REPORTS_VIEW = 'daily-reports.view';
    case DAILY_REPORTS_VIEW_ALL = 'daily-reports.view-all';
    case DAILY_REPORTS_CREATE = 'daily-reports.create';
    case DAILY_REPORTS_EDIT = 'daily-reports.edit';
    case DAILY_REPORTS_DELETE = 'daily-reports.delete';

    // Marketing Reports
    case MARKETING_REPORTS_VIEW = 'marketing-reports.view';
    case MARKETING_REPORTS_VIEW_ALL = 'marketing-reports.view-all';
    case MARKETING_REPORTS_CREATE = 'marketing-reports.create';
    case MARKETING_REPORTS_EDIT = 'marketing-reports.edit';
    case MARKETING_REPORTS_DELETE = 'marketing-reports.delete';

    // Activity Log
    case ACTIVITY_LOG_VIEW = 'activity-log.view';

    // Attendance
    case CHAM_CONG_VIEW = 'cham-cong.view';
    case CHAM_CONG_EDIT = 'cham-cong.edit';
    case CHAM_CONG_EXPORT = 'cham-cong.export';

    // HR
    case HR_PROFILES_VIEW = 'hr-profiles.view';
    case HR_PROFILES_CREATE = 'hr-profiles.create';
    case HR_PROFILES_EDIT = 'hr-profiles.edit';
    case HR_PROFILES_DELETE = 'hr-profiles.delete';

    // Granular Action Permissions
    case COMMISSIONS_APPROVE = 'commissions.approve';
    case COMMISSIONS_CONFIRM_PAYMENT = 'commissions.confirm-payment';
    case COMMISSIONS_VIEW_ALL = 'commissions.view-all';
    case INTERNAL_SOFTWARE_MANAGE = 'internal-software.manage';
    case WORK_SCHEDULES_MANAGE_ALL = 'work-schedules.manage-all';
    case CONTRACTS_EDIT_FINANCE = 'contracts.edit-finance';
    case INTERNAL_NOTIFICATIONS_MANAGE = 'internal-notifications.manage';
    case MARKETING_TARGETS_EDIT = 'marketing-targets.edit';

    /** Dùng cho route middleware: Permission::toMiddleware(Permission::USERS_VIEW) */
    public static function toMiddleware(self ...$perms): string
    {
        return 'permission:'.implode('|', array_map(fn ($p) => $p->value, $perms));
    }
}
