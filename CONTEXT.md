# Bối cảnh tổng thể dự án Bảo Châu

Cập nhật lần cuối: 2026-07-18.

## 1. Mục tiêu hệ thống

Ứng dụng quản trị nội bộ hợp nhất các nghiệp vụ:

- khách hàng, báo giá và tạo tài liệu báo giá;
- sáu nhóm hợp đồng và tiến độ triển khai;
- phân công nhân sự, file theo mốc và ghi chú tiến độ;
- doanh số, chỉ tiêu, báo cáo và bảng xếp hạng;
- dòng tiền, lịch thanh toán và yêu cầu chi hoa hồng;
- nhân sự, chấm công, lịch công tác và báo cáo ngày;
- marketing, công văn/phần mềm nội bộ và chuyển phát.

Giao diện chính là Livewire server-driven, dùng Blade, Bootstrap hiện hữu và một phần Tailwind/Vite. Ngôn ngữ người dùng là tiếng Việt.

## 2. Công nghệ và cấu trúc

| Thành phần | Công nghệ / vị trí |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| UI động | Livewire 4.2, `app/Livewire/Admin` |
| View | Blade, `resources/views/livewire/admin` |
| Database | Eloquent models, migrations trong `database/migrations` |
| Quyền | Spatie Permission, enum `Role` và `Permission` |
| Tài liệu | PHPWord, PhpSpreadsheet, Dompdf |
| File | disk cấu hình qua `upload_disk`, `avatar_disk` |
| Audit | Spatie Activity Log trên các model quan trọng |
| Frontend build | Vite 8, Tailwind 4, Font Awesome |
| Test | PHPUnit Feature/Livewire, `tests/Feature/Livewire/Admin` |

Luồng request thông thường:

`routes/web.php` -> middleware auth/role/permission -> Livewire component -> Eloquent model/service -> Blade view -> database notification hoặc toast.

## 3. Vai trò và phân quyền

Role chuẩn nằm tại `app/Enums/Role.php`:

| Key | Nghĩa |
|---|---|
| `it` | IT / quản trị |
| `giam-doc` | Giám đốc |
| `tp-kinh-doanh` | Trưởng phòng kinh doanh |
| `kinh-doanh` | Nhân viên kinh doanh |
| `tu-van` | Nhân viên tư vấn |
| `ky-thuat` | Nhân viên kỹ thuật |
| `ke-toan` | Kế toán |
| `marketing` | Marketing |
| `hcns` | Hành chính nhân sự |
| `thuc-tap` | Thực tập |

Quyền chi tiết nằm tại `app/Enums/Permission.php`. Route thường kiểm tra permission; một số route còn giới hạn role. Action nhạy cảm phải kiểm tra lại quyền ở server, không chỉ ẩn nút.

Middleware tùy chỉnh:

- `active`: chặn tài khoản không hoạt động.
- `role`: kiểm tra một trong các role.
- `permission`: kiểm tra permission.
- `intern.daily-report`: giới hạn thực tập sinh theo nghĩa vụ báo cáo ngày.

## 4. Bản đồ module

| Module | Entry point chính | Model trung tâm |
|---|---|---|
| Trang chủ/thống kê | `HomeBoard`, `StatisticsBoard`, `RankingsBoard` | nhiều nguồn tổng hợp |
| Khách hàng | `Customers/CustomerManager` | `Customer` |
| Theo dõi báo giá | `Quotations/QuotationManager` | `Quotation`, `QuotationFile` |
| Tạo báo giá | `QuotationDocuments/QuotationDocumentManager` | `QuotationDocument` và section/item/row |
| Hợp đồng | `Contracts/*Manager` | sáu model hợp đồng |
| Workflow hợp đồng | `Contracts/ContractWorkflowPanel` | `ContractWorkflowStep`, `ContractMilestoneFile` |
| Phân công | các contract manager | `ContractAssignment` |
| Hoa hồng | `Commissions/CommissionRequestForm/Manager` | `CommissionRequest` |
| Dòng tiền | `Finance/CashFlowDashboard` | hợp đồng, `ContractPaymentSchedule` |
| Báo cáo | `Reports/{Sales,Consulting,Technical,Marketing}` | query tổng hợp |
| Báo cáo xu hướng | `Reports/PotentialTrendsReport`, route `/bao-cao` | `PotentialTrendsReportService`, sáu model hợp đồng và `Quotation` |
| Báo cáo ngày | `DailyReports/DailyReportManager` | `DailyReport` |
| Lịch công tác | `WorkSchedules/WorkScheduleManager` | `WorkSchedule` |
| Nhân sự | `Hr/HrProfileManager` | `User`, `EmployeeContract`, `EmployeeDocument` |
| Chấm công | `Attendance/*` | `AttendanceEmployee`, `AttendanceLog`, `AttendanceImport` |
| Marketing | `Marketing/MarketingContentManager` | `MarketingContent` |
| Chuyển phát | `PostalDeliveries/PostalDeliveryManager` | `PostalDelivery` |
| Nội bộ | `InternalDocs/*`, `InternalNotifications/*` | `InternalDoc`, `InternalSoftware` |
| Quản trị | `Users`, `Roles`, `Departments`, `Handlers` | `User`, `Department`, `Handler` |

### Hỗ trợ từ báo cáo ngày

- Báo cáo có trạng thái `Gặp vấn đề, cần hỗ trợ` hoặc có nội dung `issues` được đưa vào hàng đợi hỗ trợ.
- Người có phạm vi quản lý báo cáo ngày xử lý yêu cầu trong tab `Hỗ trợ`; phạm vi nhân sự dùng chung `DailyReportVisibility`, vì vậy TPKD chỉ xử lý nhân sự kinh doanh còn Giám đốc/IT hoặc người có quyền xem toàn bộ xử lý theo phạm vi được cấp.
- Workflow hỗ trợ gồm `pending` (Chờ xử lý), `in_progress` (Đang xử lý) và `resolved` (Đã xử lý). Báo cáo lưu người tiếp nhận, nội dung phản hồi, thời điểm bắt đầu và hoàn tất.
- Nhân viên gửi báo cáo nhận database notification khi yêu cầu được tiếp nhận, hoàn tất hoặc mở lại.

### Báo cáo xu hướng tiềm năng

- Route `/bao-cao` dùng permission `reports-sales.view`. `IT`, `Giám đốc` và `TP Kinh doanh` được xem toàn bộ nhân sự; tài khoản khác luôn bị khóa phạm vi query về chính mình, kể cả khi sửa query string.
- Cơ hội và tỷ lệ chuyển đổi lấy từ `quotations.date`; trạng thái thành công là `QuotationStatus::KY_HOP_DONG`. Giá trị tiềm năng ưu tiên `total_value`, rồi `value_inc_vat`, rồi `original_value` ở các trạng thái đang theo dõi/hẹn/tiềm năng.
- Doanh thu chỉ cộng `revenue` của hợp đồng đã có `submitted_at`; số hợp đồng ký và giá trị hợp đồng dùng `signed_at`. Sáu loại hợp đồng vẫn được tổng hợp từ sáu model/bảng riêng.
- Điểm tiềm năng dịch vụ dùng trọng số chuyển đổi/tăng trưởng/giá trị/quy mô `30/30/25/15`; điểm khu vực dùng `35/25/20/20`. Các trọng số nằm trong `config/analytics.php` để có thể hiệu chỉnh mà không đổi query.
- Báo cáo cache 5 phút theo người xem và bộ lọc. Bộ lọc, CSV export và dữ liệu biểu đồ dùng cùng một service để không lệch số liệu.
- Dữ liệu lịch sử có thể còn trạng thái `Mất đơn`; báo cáo xem đây là trạng thái rớt cùng với enum chuẩn `Rớt báo giá` và hiển thị cảnh báo chất lượng khi thiếu dịch vụ/khu vực.

## 5. Mô hình hợp đồng

Hệ thống có sáu loại nghiệp vụ, mỗi loại dùng một model/bảng riêng. Mapping chuẩn nằm tại `App\Enums\ContractType`:

| Key | Model | Ý nghĩa giao diện |
|---|---|---|
| `waste` | `ContractWaste` | Chất thải |
| `consulting` | `ContractLegal` | Hồ sơ môi trường |
| `project` | `ContractTechnical` | Ứng phó sự cố |
| `commercial` | `ContractResearch` | Nghiên cứu và chuyển đổi công nghệ |
| `sustainability` | `ContractSustainability` | Phát triển bền vững |
| `energy` | `ContractEmission` | Phát thải và tiết kiệm năng lượng |

Các model chia sẻ hành vi qua `App\Models\Concerns\HasContractBehavior`, gồm customer, handler, staff, department, assignments, workflow steps, milestone files và payment schedules.

Quan hệ đa hình dùng **FQCN model** trong `contract_type`/`assignable_type`, ví dụ `App\Models\ContractWaste`. Các key ngắn chỉ dùng cho route và notification. Luôn dùng `ContractType` để chuyển đổi.

Hai manager có nhiều logic riêng:

- `ContractWasteManager` cho chất thải.
- `ContractConsultingManager` cho hồ sơ môi trường.

Các nhóm còn lại chủ yếu dùng `AbstractContractGenericManager` qua manager con. Khi sửa hành vi chung, kiểm tra xem hai manager riêng có cần cập nhật tương ứng hay không.

## 6. Workflow triển khai hợp đồng

Workflow gồm sáu key cố định trong `ContractWorkflowStep`:

1. `receiving`: xác nhận tiếp nhận.
2. `survey`: khảo sát/thu thập số liệu.
3. `processing`: đang thực hiện.
4. `waiting_client`: chờ khách hàng duyệt.
5. `client_confirmed`: khách hàng xác nhận.
6. `finished`: đã hoàn thành.

Tư vấn và kỹ thuật dùng cùng key nhưng label bước 2-3 khác nhau. Người có role `tu-van` hoặc `ky-thuat` mới được hoàn thành bước.

Khi hoàn thành bước:

- validate và lưu file vào `contract-files/{type}/{step}` nếu bước yêu cầu;
- tạo `ContractMilestoneFile` và `ContractWorkflowStep`;
- cập nhật `workflow_status` trên hợp đồng;
- gửi `ContractWorkflowUpdatedNotification`.

Người nhận tiến độ thông thường: Giám đốc, Trưởng phòng kinh doanh, người được phân công, người phân công và nhân viên kinh doanh phụ trách. **IT không nhận thông báo tiến độ.** Khi step là `finished`, toàn bộ role **kế toán** được thêm vào để tiếp nhận xử lý hồ sơ thanh toán. Kế toán không nhận các bước trung gian.

- Báo cáo `Tiến độ dự án TV-KT` lọc theo tháng và năm của `signed_at`; tháng mặc định là tháng hiện tại. KPI, phân trang và sáu cột pipeline dùng chung phạm vi thời gian này.

## 7. Khách hàng, báo giá và nhân viên phụ trách

`Customer` liên kết báo giá bằng `Quotation.company_name -> Customer.name`, trong khi hợp đồng liên kết bằng `customer_id`. Đây là khác biệt quan trọng khi đổi tên khách hàng hoặc viết query.

Danh sách khách hàng tổng hợp số báo giá/hợp đồng từ cả sáu model. Bộ lọc nhân viên khớp khi nhân viên là `staff_id` trên ít nhất một báo giá hoặc một hợp đồng của khách hàng. Các số liệu tổng quan phải dùng cùng query lọc với danh sách.

Tên dịch vụ lịch sử có alias và khác biệt dấu/case. `CustomerManager::canonicalizeService()` và `getServiceVariants()` đang chịu trách nhiệm tương thích dữ liệu cũ.

Trong form tạo tài liệu báo giá, ô “Chỉ tiêu / Nội dung chi tiết” là input có danh sách gợi ý. Người dùng có thể chọn chỉ tiêu trong catalog để tự áp giá hoặc nhập tay chỉ tiêu mới rồi tự nhập đơn vị, số lượng và đơn giá. Giá trị cuối cùng luôn được lưu dưới dạng chuỗi `description` và được dùng trực tiếp khi xuất Word/PDF.

## 8. Hoa hồng và VietQR

`CommissionRequest` có thể liên kết hợp đồng thật bằng `contract_type + contract_id`, hoặc lưu số hợp đồng nhập tay ở `manual_contract_number` với `contract_id = NULL`.

Trong trang hợp đồng của từng nhà thầu phụ (`HandlerContractsView`), trường `commission` trên sáu model hợp đồng được hiểu là khoản chi trả nhà thầu phụ. Tổng tiền và cột chi tiết của trang này phải cộng `commission`, không dùng trường `value` (giá trị hợp đồng).

Luồng hiện tại:

- kinh doanh tạo yêu cầu;
- TPKD xem được toàn bộ yêu cầu chi hoa hồng và có thể sửa yêu cầu chưa duyệt/đã bị từ chối theo quyền `commissions.edit`; nhân viên kinh doanh chỉ xem yêu cầu do chính mình tạo;
- kế toán nhận notification yêu cầu mới;
- kế toán duyệt, từ chối hoặc xác nhận đã chi theo quyền;
- người tạo nhận notification khi trạng thái thay đổi;
- chứng từ thanh toán có thể được upload.

VietQR dùng URL `img.vietqr.io/image/{bank_code}-{bank_number}-compact2.png`. Danh sách ngân hàng lấy từ API VietQR và có fallback tĩnh.

Quy tắc dữ liệu QR:

- `bank_code` dùng mã VietQR, ví dụ `VCB`, `EIB`, `TCB`;
- `bank_number` chỉ gồm chữ số và có thể trùng `receiver_phone` để hỗ trợ tài khoản alias bằng số điện thoại;
- khi `bank_number` trùng `receiver_phone`, form cảnh báo người dùng kiểm tra ngân hàng có hỗ trợ alias nhưng vẫn cho lưu và tạo QR;
- VietQR tạo được ảnh không đồng nghĩa tài khoản tồn tại. Ngân hàng/NAPAS xác thực người nhận khi quét.
- Mã QR không chứa tham số `addInfo` (nội dung thanh toán). Điều này để trống nội dung trên ứng dụng ngân hàng khi quét, cho phép kế toán tự nhập tay nội dung chuyển khoản theo nghiệp vụ thực tế.

## 9. Notification

Notification nghiệp vụ hiện dùng channel `database`. Chuông nằm tại `NotificationBell`:

- đọc notification được phép của người đang đăng nhập;
- poll mỗi 15 giây;
- cập nhật badge và nhóm thông báo;
- phát event `browser-notification` cho notification mới.

Blade đăng ký `public/browser-notification-sw.js` và dùng `ServiceWorkerRegistration.showNotification()` để hiện popup, có fallback về Web Notification API cũ nếu trình duyệt không hỗ trợ service worker. Popup chỉ hoạt động khi:

- trình duyệt hỗ trợ API và người dùng cấp quyền;
- người dùng đang mở hệ thống, kể cả tab nền;
- trình duyệt chưa bị đóng hoàn toàn.

Service worker giúp việc hiển thị notification ổn định hơn nhưng đây chưa phải web push: notification mới vẫn được phát hiện bằng polling khi hệ thống đang mở, nên không gửi được khi trình duyệt đã đóng hoàn toàn.

Notification contract phải có `contract_type` key ngắn để chuông phân nhóm và URL route tương ứng. Notification hoa hồng dùng `contract_type = commission`.

## 10. File, scheduler và tích hợp

File nghiệp vụ dùng `config('filesystems.upload_disk', 'public')`; avatar dùng `avatar_disk`. Không hard-code disk khi code hiện hữu đã dùng config.

Scheduler trong `routes/console.php`:

- 16:30 ngày thường: nhắc báo cáo ngày;
- mỗi giờ: đồng bộ chi phí nhà cung cấp từ Google Sheets;
- 02:00 hàng ngày: backup database, giữ 30 ngày.

Các tích hợp đáng chú ý:

- VietQR API/dịch vụ ảnh QR;
- Google Sheets cho chi phí nhà cung cấp;
- S3-compatible storage qua Flysystem;
- Viettel Post trong module chuyển phát;
- Tích hợp lịch công tác chéo Bảo Châu (`noibobaochau.me`) <-> Greeco (`noibo.greeco.vn`) qua HTTP API, cho phép thêm nhân sự chéo giữa 2 hệ thống;
- xuất Word/PDF/Excel.

## 11. Quy ước phát triển

- Component Livewire giữ state public, action phải authorize và validate.
- Giao diện admin ưu tiên Bootstrap và các class CSS hiện có của dự án; không tự thêm CSS tùy biến cho từng màn nếu chưa có yêu cầu rõ ràng. Tránh dùng `border-left`/`border-start` có màu để biểu thị trạng thái; ưu tiên icon, badge hoặc màu nền Bootstrap nhẹ.
- Nền canvas của ứng dụng admin dùng `--bs-secondary-bg`; card, form và nội dung có bề mặt riêng tiếp tục dùng màu nền mặc định để duy trì phân cấp.
- Reset pagination khi filter thay đổi.
- Query phạm vi người dùng phải được kiểm tra theo role/permission, không dựa vào UI.
- Dùng eager loading cho bảng/list để tránh N+1.
- Dùng service khi một hành động có side effect như notification (`CommissionService`).
- Model tài chính dùng integer cho số tiền; input tiền phải được làm sạch bằng concern hiện có.
- Dữ liệu xóa mềm được dùng ở nhiều model. Kiểm tra `SoftDeletes` trước khi viết query hoặc unique rule.
- Migration mới phải có `down()` an toàn và test chạy với `RefreshDatabase`.
- Test Livewire đặt cạnh module tương ứng trong `tests/Feature/Livewire/Admin`.
- Không sửa chuỗi tiếng Việt thành tiếng Anh trong UI.

## 12. Điểm dễ nhầm và nợ kỹ thuật

- `README.md` hiện vẫn là README mặc định của Laravel, không phải tài liệu nghiệp vụ.
- `docs/BUSINESS_WORKFLOW_DIAGRAMS.md` rất rộng và có phần mô tả ý tưởng tương lai; xác minh với source trước khi triển khai.
- Có cả Bootstrap và Tailwind. Màn admin hiện hữu chủ yếu dùng Bootstrap; không thay design system cục bộ nếu không có yêu cầu redesign.
- Một số tên model lịch sử không trùng tên nghiệp vụ (`ContractLegal` là consulting, `ContractResearch` là commercial).
- Quan hệ quotation-customer dựa trên tên công ty, không phải foreign key.
- `IT` có nhiều quyền quản trị nhưng không mặc định là người nhận mọi notification nghiệp vụ.
- Trạng thái text lịch sử có nhiều biến thể tiếng Việt/in hoa; workflow mới nên dựa vào key chuẩn thay vì so sánh label.
- Báo cáo ngày chỉ được coi là trễ (nộp trễ / chậm) sau khi quá hạn từ 3 ngày trở lên (tính từ ngày cần báo cáo đến thời điểm tạo/gửi thực tế).

## 13. Checklist cho AI khi nhận task

1. Xác định module, route, component, model, view và test liên quan.
2. Kiểm tra enum role/permission/contract type trước khi thêm chuỗi mới.
3. Tìm caller và consumer của dữ liệu, nhất là notification, report và accessor.
4. Viết hoặc cập nhật test tái hiện hành vi.
5. Chạy test hẹp, lint PHP và `git diff --check`.
6. Nếu thay đổi quyết định dài hạn, cập nhật file này.

## 14. Tài liệu liên quan

- `AGENTS.md`: chỉ dẫn bắt buộc cho AI agent.
- `docs/BUSINESS_WORKFLOW_DIAGRAMS.md`: sơ đồ nghiệp vụ tham khảo.
- `routes/web.php`: bản đồ chức năng và middleware thực tế.
- `app/Support/SidebarMenu.php`: cấu trúc menu theo vai trò.
- `app/Enums`: nguồn chuẩn cho role, permission và contract type.
- `tests/Feature/Livewire/Admin`: đặc tả thực thi đáng tin cậy nhất sau source.
