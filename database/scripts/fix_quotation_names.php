<?php
// Fix tên công ty trong bảng quotations có ký tự xuống dòng / tab / khoảng trắng thừa

use App\Models\Quotation;

$quotations = Quotation::all();
$dirty = $quotations->filter(function ($q) {
    return str_contains($q->company_name ?? '', "\n")
        || str_contains($q->company_name ?? '', "\r")
        || str_contains($q->company_name ?? '', "\t");
});

echo "Tìm thấy " . $dirty->count() . " báo giá có company_name bẩn:\n\n";

foreach ($dirty as $q) {
    $old = $q->company_name;
    $new = trim(preg_replace('/[\r\n\t]+/', ' ', $q->company_name));
    $new = preg_replace('/ {2,}/', ' ', $new);
    echo "ID={$q->id} | " . json_encode($old) . "\n  → " . json_encode($new) . "\n";
    $q->company_name = $new;
    $q->saveQuietly();
}

echo "\n=== XONG: đã fix " . $dirty->count() . " báo giá ===\n";
