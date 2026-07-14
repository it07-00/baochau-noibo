# Hướng dẫn cho AI agent

Đây là hệ thống quản trị nội bộ của Công ty Bảo Châu, xây dựng bằng Laravel và Livewire. Trước khi sửa code, phải đọc [CONTEXT.md](CONTEXT.md). Tài liệu này là bản đồ tổng thể và bộ nhớ dài hạn của dự án.

## Thứ tự nguồn sự thật

1. Code, migrations và automated tests hiện tại.
2. `CONTEXT.md`.
3. `docs/BUSINESS_WORKFLOW_DIAGRAMS.md` chỉ là tài liệu tham khảo. Một số sơ đồ mô tả mục tiêu nghiệp vụ chưa được triển khai đầy đủ.

Nếu tài liệu mâu thuẫn với code, làm theo code và cập nhật lại tài liệu trong cùng thay đổi.

## Quy tắc làm việc

- Giữ nguyên tiếng Việt trong giao diện và thông báo validation.
- Dùng `App\Enums\Role`, `App\Enums\Permission`, `App\Enums\ContractType`; không rải chuỗi role, permission hoặc model mapping mới.
- Kiểm tra đồng thời route middleware, authorization trong action và phạm vi dữ liệu trong query.
- Các thao tác tài chính, phân quyền, notification và workflow phải có Feature/Livewire test.
- Không giả định sáu loại hợp đồng dùng cùng bảng. Chúng là sáu model riêng, kết nối qua quan hệ polymorphic.
- Khi thay đổi một luồng hợp đồng, kiểm tra cả manager riêng và `AbstractContractGenericManager`.
- Không tạo QR VietQR nếu số tài khoản trùng số điện thoại. `EIB` là mã Eximbank hợp lệ; QR không tra được người nhận thường do số tài khoản không hợp lệ.
- Bước workflow cuối có key `finished`. Khi hoàn thành bước này, kế toán nhận thông báo để xử lý hồ sơ; IT không nhận thông báo tiến độ hợp đồng.
- Notification được lưu database và chuông kiểm tra mỗi 15 giây. Browser popup chỉ hoạt động khi người dùng đã cấp quyền và đang mở hệ thống.
- Tôn trọng thay đổi chưa commit của người dùng. Không reset hoặc ghi đè file ngoài phạm vi.

## Lệnh kiểm tra chuẩn

```powershell
php -l path\to\file.php
php artisan test tests\Feature\Livewire\Admin\RelevantTest.php
vendor\bin\pint --dirty
git diff --check
```

Chỉ chạy toàn bộ test suite khi phạm vi/rủi ro yêu cầu. Dự án dùng PHP 8.3+, Laravel 13, Livewire 4.2 và PHPUnit 12.

## Khi hoàn thành thay đổi

- Cập nhật `CONTEXT.md` nếu thay đổi kiến trúc, thuật ngữ, quyền, workflow, notification, tích hợp hoặc quyết định quan trọng.
- Cập nhật `docs/BUSINESS_WORKFLOW_DIAGRAMS.md` nếu thay đổi một luồng nghiệp vụ đã được vẽ.
- Ghi rõ migration cần chạy và test đã chạy.
