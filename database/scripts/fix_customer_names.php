<?php
// Tìm và fix tên khách hàng có ký tự xuống dòng / tab / khoảng trắng thừa

use App\Models\Customer;

$customers = Customer::all();
$dirty = $customers->filter(function ($c) {
    return str_contains($c->name, "\n")
        || str_contains($c->name, "\r")
        || str_contains($c->name, "\t");
});

echo "Tìm thấy " . $dirty->count() . " khách hàng có tên bẩn:\n\n";

foreach ($dirty as $c) {
    $oldName = $c->name;
    $newName = trim(preg_replace('/[\r\n\t]+/', ' ', $c->name));
    $newName = preg_replace('/ {2,}/', ' ', $newName); // dọn khoảng trắng đôi
    echo "ID={$c->id}\n";
    echo "  Cũ: " . json_encode($oldName) . "\n";
    echo "  Mới: " . json_encode($newName) . "\n";
    $c->name = $newName;
    $c->saveQuietly(); // không trigger activity log
    echo "  → Đã fix\n\n";
}

echo "=== XONG ===\n";
