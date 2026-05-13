<?php

namespace App\Livewire\Concerns;

use App\Models\Department;

use App\Enums\ContractRenewalStatus;
use App\Enums\ContractVoucherStatus;

trait ContractValidation
{
    /**
     * Đảm bảo luôn có phòng ban mặc định cho form hợp đồng
     * (đặc biệt khi mở form từ báo giá với department_id đang rỗng).
     */
    protected function ensureDepartmentId(): void
    {
        if (!property_exists($this, 'formData') || !is_array($this->formData)) {
            return;
        }

        if (!empty($this->formData['department_id'])) {
            return;
        }

        $defaultDepartmentId = Department::query()
            ->where('slug', 'kinh-doanh')
            ->value('id');

        if (!$defaultDepartmentId) {
            $defaultDepartmentId = Department::query()->value('id');
        }

        if ($defaultDepartmentId) {
            $this->formData['department_id'] = $defaultDepartmentId;
        }
    }

    protected function normalizeContractEnumFields(): void
    {
        if (!property_exists($this, 'formData') || !is_array($this->formData)) {
            return;
        }

        if (!isset($this->formData['renewal_status']) || trim((string) $this->formData['renewal_status']) === '') {
            return;
        }

        $current = trim((string) $this->formData['renewal_status']);

        foreach (ContractRenewalStatus::cases() as $status) {
            if (
                mb_strtoupper($current, 'UTF-8') === $status->value ||
                mb_strtolower($current, 'UTF-8') === mb_strtolower($status->label(), 'UTF-8')
            ) {
                $this->formData['renewal_status'] = $status->value;
                return;
            }
        }
    }

    /**
     * Rules chung cho 5 loại HĐ: consulting, commercial, project, sustainability, energy.
     */
    protected function baseContractRules(): array
    {
        return [
            'formData.shd_bc'          => 'nullable|string|max:255',
            'formData.customer_id'     => 'required|exists:customers,id',
            'formData.staff_id'        => 'required|exists:users,id',
            'formData.department_id'   => 'required|exists:departments,id',
            'formData.signed_at'       => 'nullable|date',
            'formData.submitted_at'    => 'nullable|date',
            'formData.value'           => 'required|numeric|min:0|max:999999999999999',
            'formData.commission'      => 'nullable|numeric|min:0|max:999999999999999',
            'formData.revenue'         => 'nullable|numeric|min:0|max:999999999999999',
            'formData.province'        => 'nullable|string|max:100',
            'formData.info_source'     => 'nullable|string|max:255',
            'formData.payment_method'  => 'nullable|string|max:100',
            'formData.loai_dich_vu'    => 'nullable|string|max:255',
            'formData.status'          => 'nullable|in:PTH đang kiểm tra,Đang trình BGĐ ký,Đã gửi khách hàng,Đã hoàn thành,Hợp đồng hủy,ĐANG THỰC HIỆN,HOÀN THÀNH,ĐÃ HỦY',
            'formData.renewal_status'  => 'nullable|in:' . implode(',', ContractRenewalStatus::values()),
            'formData.voucher_status'  => 'nullable|in:' . implode(',', ContractVoucherStatus::values()),
            'formData.notes'           => 'nullable|string|max:2000',
        ];
    }

    /**
     * Rules riêng cho HĐ chất thải (waste) – thêm nhiều field.
     */
    protected function wasteContractRules(): array
    {
        return [
            'formData.shd_cxl'           => 'nullable|string|max:255',
            'formData.shd_bc'            => 'nullable|string|max:255',
            'formData.customer_id'       => 'required|exists:customers,id',
            'formData.handler_id'        => 'required|exists:handlers,id',
            'formData.staff_id'          => 'required|exists:users,id',
            'formData.department_id'     => 'required|exists:departments,id',
            'formData.content'           => 'nullable|string|max:2000',
            'formData.value'             => 'required|numeric|min:0|max:999999999999999',
            'formData.commission'        => 'nullable|numeric|min:0|max:999999999999999',
            'formData.revenue'           => 'nullable|numeric|min:0|max:999999999999999',
            'formData.payment_method'    => 'nullable|string|max:100',
            'formData.source'            => 'nullable|string|max:255',
            'formData.signed_at'         => 'nullable|date',
            'formData.effective_at'      => 'nullable|date|after_or_equal:formData.signed_at',
            'formData.end_at'            => 'nullable|date|after_or_equal:formData.effective_at',
            'formData.submitted_at'      => 'nullable|date',
            'formData.billing_address'   => 'nullable|string|max:500',
            'formData.execution_address' => 'nullable|string|max:500',
            'formData.mailing_address'   => 'nullable|string|max:500',
            'formData.status'            => 'nullable|in:Đã trình ký nhà thầu phụ,Nhà thầu phụ đã gửi về,Đã gửi khách hàng,Đã hoàn thành KH ký trước,Đã hoàn thành,Hợp đồng hủy,ĐANG THỰC HIỆN,HOÀN THÀNH,ĐÃ HỦY,TẠM DỪNG,HỦY BỎ',
            'formData.renewal_status'    => 'nullable|in:' . implode(',', ContractRenewalStatus::values()),
            'formData.voucher_status'    => 'nullable|in:' . implode(',', ContractVoucherStatus::values()),
            'formData.province'          => 'nullable|string|max:100',
            'formData.loai_dich_vu'      => 'nullable|string|max:255',
            'formData.note'              => 'nullable|string|max:2000',
        ];
    }

    /**
     * Messages tiếng Việt dùng chung.
     */
    protected function contractValidationMessages(): array
    {
        return [
            'formData.customer_id.required'     => 'Vui lòng chọn khách hàng.',
            'formData.customer_id.exists'        => 'Khách hàng không tồn tại.',
            'formData.handler_id.required'       => 'Vui lòng chọn nhà thầu phụ.',
            'formData.handler_id.exists'          => 'Nhà thầu phụ không tồn tại.',
            'formData.staff_id.required'          => 'Vui lòng chọn nhân viên phụ trách.',
            'formData.staff_id.exists'            => 'Nhân viên không tồn tại.',
            'formData.department_id.required'       => 'Vui lòng chọn phòng ban.',
            'formData.department_id.exists'        => 'Phòng ban không tồn tại.',
            'formData.value.required'             => 'Vui lòng nhập giá trị hợp đồng.',
            'formData.value.numeric'              => 'Giá trị hợp đồng phải là số.',
            'formData.value.min'                  => 'Giá trị hợp đồng không được âm.',
            'formData.commission.numeric'          => 'Hoa hồng phải là số.',
            'formData.commission.min'              => 'Hoa hồng không được âm.',
            'formData.revenue.numeric'             => 'Doanh số phải là số.',
            'formData.revenue.min'                 => 'Doanh số không được âm.',
            'formData.effective_at.after_or_equal' => 'Ngày hiệu lực phải sau hoặc bằng ngày ký.',
            'formData.end_at.after_or_equal'       => 'Ngày kết thúc phải sau hoặc bằng ngày hiệu lực.',
            'formData.content.max'                 => 'Nội dung không được vượt quá 2000 ký tự.',
            'formData.note.max'                    => 'Ghi chú không được vượt quá 2000 ký tự.',
            'formData.notes.max'                   => 'Ghi chú không được vượt quá 2000 ký tự.',
            'formData.billing_address.max'         => 'Địa chỉ xuất HĐ không được vượt quá 500 ký tự.',
            'formData.execution_address.max'       => 'Địa chỉ thực hiện không được vượt quá 500 ký tự.',
            'formData.mailing_address.max'         => 'Địa chỉ gửi thư không được vượt quá 500 ký tự.',
            'formData.status.in'                    => 'Tình trạng hợp đồng không hợp lệ.',
            'formData.renewal_status.in'            => 'Tình trạng tái ký không hợp lệ.',
            'formData.voucher_status.in'            => 'Tình trạng chứng từ không hợp lệ.',
            'formData.value.max'                    => 'Giá trị hợp đồng vượt quá giới hạn.',
            'formData.commission.max'               => 'Hoa hồng vượt quá giới hạn.',
            'formData.revenue.max'                  => 'Doanh số vượt quá giới hạn.',
        ];
    }
}
