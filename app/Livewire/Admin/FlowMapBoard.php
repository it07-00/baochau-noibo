<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class FlowMapBoard extends Component
{
    public string $activeMap = 'contract';

    public function setMap(string $key): void
    {
        if (! array_key_exists($key, $this->maps())) {
            return;
        }

        $this->activeMap = $key;
    }

    public function render()
    {
        $maps = $this->maps();

        return view('livewire.admin.flow-map-board', [
            'maps' => $maps,
            'current' => $maps[$this->activeMap],
        ])->layout('admin.layouts.app', ['title' => 'Sơ đồ luồng nghiệp vụ']);
    }

    private function maps(): array
    {
        return [
            'contract' => [
                'label' => 'Hợp đồng',
                'icon' => 'doc',
                'title' => 'Luồng hợp đồng & thanh toán',
                'summary' => 'Từ khách hàng, báo giá, hợp đồng đến lịch thanh toán, dòng tiền và báo cáo.',
                'metrics' => [
                    ['label' => 'Loại hợp đồng', 'value' => '6'],
                    ['label' => 'Điểm giao việc', 'value' => '2'],
                    ['label' => 'Báo cáo liên quan', 'value' => '3'],
                    ['label' => 'Đích tài chính', 'value' => 'Dòng tiền'],
                ],
                'ledger' => [
                    'Khách hàng',
                    'Báo giá',
                    'Hợp đồng',
                    'Lịch thanh toán',
                    'Dòng tiền',
                    'Báo cáo',
                ],
                'notes' => [
                    'Tư vấn và kỹ thuật dùng cùng nguồn hợp đồng nhưng khác góc nhìn xử lý.',
                    'Kế toán theo dõi thanh toán, hoa hồng và dòng tiền sau khi hợp đồng phát sinh.',
                ],
                'steps' => [
                    [
                        'phase' => '01',
                        'label' => 'Tiếp nhận',
                        'nodes' => [
                            $this->node('Khách hàng', 'Hồ sơ, liên hệ, lịch sử hợp đồng', 'app.customers.index', 'primary'),
                            $this->node('Nhà thầu phụ', 'Đối tác xử lý và hợp đồng liên quan', 'app.handlers.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '02',
                        'label' => 'Báo giá',
                        'nodes' => [
                            $this->node('Theo dõi báo giá', 'Pipeline kinh doanh trước hợp đồng', 'app.quotation-tracking.index', 'primary'),
                            $this->node('Mục tiêu doanh số', 'Cam kết và kế hoạch doanh thu', 'app.sales.target-registration', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '03',
                        'label' => 'Ký hợp đồng',
                        'nodes' => [
                            $this->node('Chất thải', 'Thu gom CTNH, CTCN, hủy hàng', 'app.contracts.waste.index', 'primary'),
                            $this->node('Quan trắc & hồ sơ MT', 'Quan trắc MT, QTMT, hồ sơ MT, lao động', 'app.contracts.consulting.index', 'primary'),
                            $this->node('Ứng phó sự cố', 'Ứng phó HC, MT, lập KH, diễn tập, hệ thống XLKT/NT, bản đồ ồn', 'app.contracts.project.index', 'primary'),
                            $this->node('Giảm phát thải, TKNL', 'Kiểm kê KNK, giảm phát thải, kiểm toán NL, solar', 'app.contracts.energy.index', 'muted', true),
                            $this->node('Phát triển bền vững', 'ESG, cảng xanh, CBAM, vòng đời SP, tín chỉ carbon', 'app.contracts.sustainability.index', 'muted', true),
                            $this->node('Nghiên cứu & CĐ CN', 'Nghiên cứu MT, giải pháp CĐ CN, hội thảo', 'app.contracts.commercial.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '04',
                        'label' => 'Thực hiện',
                        'nodes' => [
                            $this->node('Bộ phận tư vấn', 'Nhận và xử lý hồ sơ theo nhóm dịch vụ', 'app.reports.consulting-work.consulting', 'primary'),
                            $this->node('Bộ phận kỹ thuật', 'Khối lượng giao, tiến độ, hoàn tất', 'app.reports.technical.consulting', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '05',
                        'label' => 'Tài chính',
                        'nodes' => [
                            $this->node('Dòng tiền', 'Thu, chi, công nợ và kế hoạch tiền', 'app.finance.cash-flow', 'success'),
                            $this->node('Hoa hồng', 'Đề nghị chi và trạng thái duyệt', 'app.commissions.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '06',
                        'label' => 'Báo cáo',
                        'nodes' => [
                            $this->node('Tổng kết doanh số', 'Kết quả kinh doanh theo kỳ', 'app.reports.sales.summary', 'success'),
                            $this->node('Thống kê', 'Tổng quan điều hành', 'app.dashboard', 'success'),
                        ],
                    ],
                ],
            ],
            'sales' => [
                'label' => 'Kinh doanh',
                'icon' => 'chart',
                'title' => 'Luồng kinh doanh',
                'summary' => 'Từ mục tiêu, tạo khách hàng, báo giá, ký hợp đồng đến phân công xử lý và theo dõi kết quả.',
                'metrics' => [
                    ['label' => 'Điểm vào', 'value' => 'KH'],
                    ['label' => 'Chuyển đổi', 'value' => 'BG → HĐ'],
                    ['label' => 'Bộ phận nhận', 'value' => '4'],
                    ['label' => 'Báo cáo', 'value' => '3'],
                ],
                'ledger' => ['Mục tiêu', 'Khách hàng', 'Báo giá', 'Hợp đồng', 'Phân công', 'Kết quả'],
                'notes' => [
                    'Báo giá là điểm chuyển đổi trung tâm — khi khách chốt, báo giá trở thành hợp đồng.',
                    'Sau ký HĐ, công việc được giao tùy tính chất: tư vấn, kỹ thuật, kinh doanh hoặc đơn vị ngoài.',
                ],
                'steps' => [
                    [
                        'phase' => '01',
                        'label' => 'Lập mục tiêu',
                        'nodes' => [
                            $this->node('Đăng ký mục tiêu', 'Cam kết doanh số theo kỳ', 'app.sales.target-registration', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '02',
                        'label' => 'Khách hàng',
                        'nodes' => [
                            $this->node('Tạo & chăm sóc KH', 'Hồ sơ, liên hệ, lịch sử hợp đồng', 'app.customers.index', 'primary'),
                            $this->node('Lịch công tác', 'Gặp gỡ và chăm sóc thực địa', 'app.work-schedules.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '03',
                        'label' => 'Báo giá',
                        'nodes' => [
                            $this->node('Theo dõi báo giá', 'Pipeline cơ hội, trạng thái và chuyển đổi', 'app.quotation-tracking.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '04',
                        'label' => 'Ký hợp đồng',
                        'nodes' => [
                            $this->node('Hợp đồng dịch vụ', 'Báo giá được chốt chuyển thành hợp đồng', 'app.contracts.waste.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '05',
                        'label' => 'Phân công',
                        'nodes' => [
                            $this->node('Bộ phận tư vấn', 'Hồ sơ MT, quan trắc, PTBV, năng lượng', 'app.reports.consulting-work.consulting', 'primary'),
                            $this->node('Bộ phận kỹ thuật', 'Ứng phó sự cố, hệ thống XLKT/NT', 'app.reports.technical.consulting', 'primary'),
                            $this->node('Đơn vị ngoài', 'Nhà thầu phụ xử lý chất thải', 'app.handlers.index', 'muted', true),
                            $this->node('Kinh doanh', 'Hợp đồng tự thực hiện (R&D, hội thảo)', 'app.contracts.commercial.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '06',
                        'label' => 'Kết quả',
                        'nodes' => [
                            $this->node('Tổng kết doanh số', 'Kết quả kinh doanh theo kỳ', 'app.reports.sales.summary', 'success'),
                            $this->node('Doanh số cá nhân', 'Hiệu suất từng nhân sự', 'app.reports.sales.personal', 'success'),
                            $this->node('Đường đua', 'Điểm và thứ hạng thi đua', 'app.rankings', 'success'),
                        ],
                    ],
                ],
            ],
            'operations' => [
                'label' => 'Tư vấn/Kỹ thuật',
                'icon' => 'stack',
                'title' => 'Luồng xử lý chuyên môn',
                'summary' => 'Các hợp đồng sau khi ký được chuyển thành khối lượng xử lý, theo dõi tiến độ và báo cáo.',
                'metrics' => [
                    ['label' => 'Nhóm dịch vụ', 'value' => '6'],
                    ['label' => 'Bộ phận', 'value' => '2'],
                    ['label' => 'Báo cáo', 'value' => '12+'],
                    ['label' => 'Theo dõi', 'value' => 'Tiến độ'],
                ],
                'ledger' => ['Hợp đồng', 'Phân công', 'Thực hiện', 'Bàn giao', 'Báo cáo', 'Thống kê'],
                'notes' => [
                    'TPKD hoặc nhân sự kinh doanh giao việc dựa trên tính chất hợp đồng — không qua bộ phận nhân sự.',
                    'Nhật ký công việc ghi nhận bằng chứng thực hiện hằng ngày.',
                ],
                'steps' => [
                    [
                        'phase' => '01',
                        'label' => 'Nguồn việc',
                        'nodes' => [
                            $this->node('Quan trắc & hồ sơ MT', 'Nguồn việc tư vấn chính', 'app.contracts.consulting.index', 'primary'),
                            $this->node('Ứng phó sự cố', 'Nguồn việc kỹ thuật chính', 'app.contracts.project.index', 'primary'),
                            $this->node('Các nhóm còn lại', 'Chất thải, PTBV, năng lượng, R&D', 'app.contracts.waste.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '02',
                        'label' => 'Phân công',
                        'nodes' => [
                            $this->node('TPKD / Kinh doanh', 'Giao việc theo từng hợp đồng', 'app.contracts.consulting.index', 'primary'),
                            $this->node('Phòng ban', 'Cơ cấu tổ chức phụ trách', 'app.departments.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '03',
                        'label' => 'Thực hiện',
                        'nodes' => [
                            $this->node('Nhật ký công việc', 'Ghi nhận việc hằng ngày', 'app.daily-reports.index', 'primary'),
                            $this->node('Lịch công tác', 'Kế hoạch đi hiện trường', 'app.work-schedules.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '04',
                        'label' => 'Bàn giao',
                        'nodes' => [
                            $this->node('Chuyển phát', 'Gửi hồ sơ và kết quả cho khách hàng', 'app.postal-deliveries.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '05',
                        'label' => 'Báo cáo',
                        'nodes' => [
                            $this->node('Báo cáo tư vấn', 'Theo hợp đồng và nhóm dịch vụ', 'app.reports.consulting-work.consulting', 'success'),
                            $this->node('Báo cáo kỹ thuật', 'Theo khối lượng kỹ thuật', 'app.reports.technical.consulting', 'success'),
                        ],
                    ],
                    [
                        'phase' => '06',
                        'label' => 'Điều hành',
                        'nodes' => [
                            $this->node('Thống kê', 'Tổng quan tiến độ', 'app.dashboard', 'success'),
                        ],
                    ],
                ],
            ],
            'finance' => [
                'label' => 'Kế toán',
                'icon' => 'money',
                'title' => 'Luồng tài chính',
                'summary' => 'Kế toán cập nhật số hóa đơn trên hợp đồng, theo dõi thanh toán nhà thầu phụ và cập nhật trạng thái dòng tiền.',
                'metrics' => [
                    ['label' => 'Cập nhật', 'value' => 'Hóa đơn'],
                    ['label' => 'NCC', 'value' => 'Nếu có'],
                    ['label' => 'Trung tâm', 'value' => 'Dòng tiền'],
                    ['label' => 'Chi trả', 'value' => 'Hoa hồng'],
                ],
                'ledger' => ['Hợp đồng', 'Hóa đơn', 'NCC', 'Dòng tiền', 'Hoa hồng', 'Báo cáo'],
                'notes' => [
                    'Kế toán vào từng hợp đồng để cập nhật số hóa đơn và trạng thái thanh toán nhà thầu phụ (nếu có).',
                    'Sau khi cập nhật hóa đơn, dòng tiền phản ánh trạng thái thu chi thực tế.',
                ],
                'steps' => [
                    [
                        'phase' => '01',
                        'label' => 'Phát sinh',
                        'nodes' => [
                            $this->node('Hợp đồng dịch vụ', 'Nguồn phát sinh doanh thu và công nợ', 'app.contracts.waste.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '02',
                        'label' => 'Cập nhật hóa đơn',
                        'nodes' => [
                            $this->node('Quan trắc & hồ sơ MT', 'Cập nhật số HĐ và trạng thái thanh toán', 'app.contracts.consulting.index', 'primary'),
                            $this->node('Ứng phó sự cố', 'Cập nhật số HĐ và trạng thái thanh toán', 'app.contracts.project.index', 'primary'),
                            $this->node('Các loại còn lại', 'Chất thải, PTBV, năng lượng, R&D', 'app.contracts.waste.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '03',
                        'label' => 'Nhà thầu phụ',
                        'nodes' => [
                            $this->node('Thanh toán NCC', 'Cập nhật trạng thái chi NCC trên hợp đồng', 'app.handlers.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '04',
                        'label' => 'Dòng tiền',
                        'nodes' => [
                            $this->node('Cập nhật dòng tiền', 'Ghi nhận thu chi, công nợ thực tế', 'app.finance.cash-flow', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '05',
                        'label' => 'Hoa hồng',
                        'nodes' => [
                            $this->node('Yêu cầu hoa hồng', 'Xét duyệt và chi hoa hồng kinh doanh', 'app.commissions.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '06',
                        'label' => 'Báo cáo',
                        'nodes' => [
                            $this->node('Tổng kết doanh số', 'Doanh thu và hiệu suất theo kỳ', 'app.reports.sales.summary', 'success'),
                            $this->node('Bảng điều khiển', 'Tổng quan tài chính cho quản lý', 'app.dashboard', 'success'),
                        ],
                    ],
                ],
            ],
            'people' => [
                'label' => 'Nhân sự/Nội bộ',
                'icon' => 'users',
                'title' => 'Luồng vận hành nội bộ',
                'summary' => 'Tổ chức nhân sự, lịch làm việc, chấm công, tài liệu nội bộ và chuyển phát.',
                'metrics' => [
                    ['label' => 'Nhân sự', 'value' => 'HR'],
                    ['label' => 'Lịch', 'value' => 'Công tác'],
                    ['label' => 'Tài liệu', 'value' => 'Nội bộ'],
                    ['label' => 'Vận hành', 'value' => 'Thư'],
                ],
                'ledger' => ['Phòng ban', 'Nhân sự', 'Lịch', 'Chấm công', 'Tài liệu', 'Chuyển phát'],
                'notes' => [
                    'Các module này hỗ trợ vận hành hằng ngày, không nhất thiết đi theo một hợp đồng cụ thể.',
                    'Nội bộ và phần mềm là kho thông tin nền cho nhân sự sử dụng hệ thống.',
                ],
                'steps' => [
                    [
                        'phase' => '01',
                        'label' => 'Cơ cấu',
                        'nodes' => [
                            $this->node('Phòng ban', 'Cơ cấu tổ chức', 'app.departments.index', 'primary'),
                            $this->node('Người dùng', 'Tài khoản hệ thống', 'app.users.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '02',
                        'label' => 'Hồ sơ',
                        'nodes' => [
                            $this->node('Nhân sự', 'Hồ sơ và thông tin nhân viên', 'app.hr.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '03',
                        'label' => 'Lịch làm việc',
                        'nodes' => [
                            $this->node('Lịch công tác', 'Kế hoạch làm việc', 'app.work-schedules.index', 'primary'),
                            $this->node('Chấm công', 'Ghi nhận công', 'app.attendance.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '04',
                        'label' => 'Ghi nhận',
                        'nodes' => [
                            $this->node('Nhật ký công việc', 'Việc làm trong ngày', 'app.daily-reports.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '05',
                        'label' => 'Tri thức',
                        'nodes' => [
                            $this->node('Quy định', 'Công văn và tài liệu nội bộ', 'app.internal-docs.index', 'success'),
                            $this->node('Phần mềm', 'Danh mục công cụ nội bộ', 'app.internal-software.index', 'muted', true),
                        ],
                    ],
                    [
                        'phase' => '06',
                        'label' => 'Hành chính',
                        'nodes' => [
                            $this->node('Chuyển phát', 'Theo dõi thư và bưu phẩm', 'app.postal-deliveries.index', 'success'),
                        ],
                    ],
                ],
            ],
            'system' => [
                'label' => 'IT',
                'icon' => 'gear',
                'title' => 'Luồng quản trị hệ thống',
                'summary' => 'Quyền, vai trò, cấu hình, nhật ký hoạt động và giám sát hệ thống.',
                'metrics' => [
                    ['label' => 'Quyền', 'value' => 'RBAC'],
                    ['label' => 'Theo dõi', 'value' => 'Log'],
                    ['label' => 'Cấu hình', 'value' => 'Settings'],
                    ['label' => 'Giám sát', 'value' => 'IT'],
                ],
                'ledger' => ['Người dùng', 'Vai trò', 'Cài đặt', 'Hoạt động', 'Log', 'Cache'],
                'notes' => [
                    'Luồng này dành cho quản trị viên và IT.',
                    'Nhật ký hoạt động giúp truy vết thao tác trên hệ thống.',
                ],
                'steps' => [
                    [
                        'phase' => '01',
                        'label' => 'Tài khoản',
                        'nodes' => [
                            $this->node('Người dùng', 'Tạo và quản lý tài khoản', 'app.users.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '02',
                        'label' => 'Phân quyền',
                        'nodes' => [
                            $this->node('Vai trò & quyền', 'Nhóm quyền truy cập', 'app.roles.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '03',
                        'label' => 'Cấu hình',
                        'nodes' => [
                            $this->node('Cài đặt', 'Thông số hệ thống', 'app.settings.index', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '04',
                        'label' => 'Theo dõi',
                        'nodes' => [
                            $this->node('Nhật ký hoạt động', 'Truy vết thao tác', 'app.activity-log', 'primary'),
                        ],
                    ],
                    [
                        'phase' => '05',
                        'label' => 'Vận hành',
                        'nodes' => [
                            $this->node('Quản trị hệ thống', 'Cache, log, phiên, tài nguyên', 'app.it-dashboard', 'success'),
                        ],
                    ],
                    [
                        'phase' => '06',
                        'label' => 'Điều hướng',
                        'nodes' => [
                            $this->node('Bảng điều khiển', 'Điểm nhìn tổng quan', 'app.dashboard', 'success'),
                        ],
                    ],
                ],
            ],
        ];
    }

    private function node(string $title, string $subtitle, string $routeName, string $tone = 'primary', bool $optional = false): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'href' => Route::has($routeName) ? route($routeName) : null,
            'tone' => $tone,
            'optional' => $optional,
        ];
    }
}
