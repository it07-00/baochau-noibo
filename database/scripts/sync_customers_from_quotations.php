<?php

/**
 * Script đồng bộ thông tin khách hàng từ bảng quotations
 * Chạy: php artisan tinker --execute="require base_path('database/scripts/sync_customers_from_quotations.php');"
 *
 * Mapping:
 *   quotation.province       → customer.province     (nếu đang trống)
 *   quotation.address        → customer.address      (nếu đang trống)
 *   quotation.contact_person → customer.representative (nếu đang trống)
 *
 * Match: tìm quotation có company_name chứa tên khách hàng (case-insensitive)
 *        ưu tiên quotation mới nhất
 */

use App\Models\Customer;
use App\Models\Quotation;

$customers = Customer::withTrashed(false)->get();
$updatedCount = 0;
$skippedCount = 0;

foreach ($customers as $customer) {
    // Tìm quotation khớp tên (ưu tiên chính xác, sau đó LIKE)
    $quotation = Quotation::whereRaw('LOWER(company_name) LIKE ?', ['%' . strtolower($customer->name) . '%'])
        ->whereNotNull('province')
        ->orderByDesc('date')
        ->first();

    if (!$quotation) {
        $quotation = Quotation::whereRaw('LOWER(company_name) LIKE ?', ['%' . strtolower(trim($customer->name)) . '%'])
            ->orderByDesc('date')
            ->first();
    }

    if (!$quotation) {
        echo "[SKIP] {$customer->name}: không tìm thấy báo giá phù hợp\n";
        $skippedCount++;
        continue;
    }

    $changes = [];

    if (empty($customer->province) && !empty($quotation->province)) {
        $changes['province'] = $quotation->province;
    }

    if (empty($customer->address) && !empty($quotation->address)) {
        $changes['address'] = $quotation->address;
    }

    if (empty($customer->representative) && !empty($quotation->contact_person)) {
        $changes['representative'] = $quotation->contact_person;
    }

    if (!empty($changes)) {
        $customer->update($changes);
        $updatedCount++;
        $changeStr = implode(', ', array_map(fn($k, $v) => "$k = \"$v\"", array_keys($changes), $changes));
        echo "[OK]   {$customer->name}: cập nhật $changeStr\n";
    } else {
        echo "[SKIP] {$customer->name}: đã đầy đủ thông tin hoặc báo giá không có thêm dữ liệu\n";
        $skippedCount++;
    }
}

echo "\n=== KẾT QUẢ ===\n";
echo "Cập nhật: $updatedCount khách hàng\n";
echo "Bỏ qua:   $skippedCount khách hàng\n";
