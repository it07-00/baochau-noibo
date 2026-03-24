<?php

namespace App\Livewire\Admin\PostalDeliveries;

use App\Models\Department;
use App\Models\PostalDelivery;
use App\Services\ViettelPostService;
use Livewire\Component;
use Livewire\WithPagination;

class PostalDeliveryManager extends Component
{
    use WithPagination;

    // Filter states
    public $search = '';
    public $departmentIdFilter = '';
    public $perPage = 10;

    // Form states
    public $deliveryId;
    public $customer_name;
    public $customer_phone;
    public $customer_email;
    public $address;
    public $receiver_province;
    public $receiver_district;
    public $receiver_ward;
    public $sender_name;
    public $bill_viettel;
    public $bill_247;
    public $content;
    public $department_id;
    public $status = 'sent';

    // VTP fields
    public $vtp_service = 'VCN';
    public $vtp_weight = 100;
    public $vtp_money_collection = 0;
    public $create_vtp_order = false;

    // VTP location data
    public $provinces = [];
    public $districts = [];
    public $wards = [];

    // Tracking modal
    public $trackingDelivery = null;
    public $trackingData = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadProvinces();
    }

    public function loadProvinces()
    {
        $vtp = app(ViettelPostService::class);
        $this->provinces = $vtp->getProvinces();
    }

    public function updatedReceiverProvince($value)
    {
        $this->receiver_district = null;
        $this->receiver_ward = null;
        $this->districts = [];
        $this->wards = [];

        if ($value) {
            $vtp = app(ViettelPostService::class);
            $this->districts = $vtp->getDistricts((int) $value);
        }
    }

    public function updatedReceiverDistrict($value)
    {
        $this->receiver_ward = null;
        $this->wards = [];

        if ($value) {
            $vtp = app(ViettelPostService::class);
            $this->wards = $vtp->getWards((int) $value);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->deliveryId = null;
        $this->customer_name = '';
        $this->customer_phone = '';
        $this->customer_email = '';
        $this->address = '';
        $this->receiver_province = null;
        $this->receiver_district = null;
        $this->receiver_ward = null;
        $this->sender_name = '';
        $this->bill_viettel = '';
        $this->bill_247 = '';
        $this->content = '';
        $this->department_id = auth()->user()->department_id;
        $this->status = 'sent';
        $this->vtp_service = 'VCN';
        $this->vtp_weight = 100;
        $this->vtp_money_collection = 0;
        $this->create_vtp_order = false;
        $this->districts = [];
        $this->wards = [];
    }

    public function save()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'sender_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'bill_viettel' => 'nullable|string|max:50',
            'bill_247' => 'nullable|string|max:50',
            'receiver_province' => $this->create_vtp_order ? 'required|integer' : 'nullable|integer',
            'receiver_district' => $this->create_vtp_order ? 'required|integer' : 'nullable|integer',
            'receiver_ward' => $this->create_vtp_order ? 'required|integer' : 'nullable|integer',
            'address' => $this->create_vtp_order ? 'required|string' : 'nullable|string',
            'customer_phone' => $this->create_vtp_order ? 'required|string' : 'nullable|string',
        ], [
            'receiver_province.required' => 'Vui lòng chọn Tỉnh/TP để tạo đơn VTP.',
            'receiver_district.required' => 'Vui lòng chọn Quận/Huyện để tạo đơn VTP.',
            'receiver_ward.required' => 'Vui lòng chọn Phường/Xã để tạo đơn VTP.',
            'address.required' => 'Vui lòng nhập địa chỉ để tạo đơn VTP.',
            'customer_phone.required' => 'Vui lòng nhập SĐT khách để tạo đơn VTP.',
        ]);

        $data = [
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'address' => $this->address,
            'receiver_province' => $this->receiver_province,
            'receiver_district' => $this->receiver_district,
            'receiver_ward' => $this->receiver_ward,
            'sender_name' => $this->sender_name,
            'bill_viettel' => $this->bill_viettel,
            'bill_247' => $this->bill_247,
            'content' => $this->content,
            'department_id' => $this->department_id,
            'user_id' => auth()->id(),
            'status' => $this->status,
            'vtp_service' => $this->vtp_service,
            'vtp_weight' => $this->vtp_weight,
            'vtp_money_collection' => $this->vtp_money_collection,
        ];

        if ($this->deliveryId) {
            $delivery = PostalDelivery::find($this->deliveryId);
            $delivery->update($data);
            $this->dispatch('swal:success', ['message' => 'Cập nhật thành công!']);
        } else {
            $delivery = PostalDelivery::create($data);

            // Tạo đơn VTP tự động nếu được chọn
            if ($this->create_vtp_order) {
                $this->createVtpOrder($delivery);
                return;
            }

            $this->dispatch('swal:success', ['message' => 'Thêm mới thành công!']);
        }

        $this->resetFields();
        $this->dispatch('closeModal');
    }

    /**
     * Tạo đơn Viettel Post từ postal delivery.
     */
    public function createVtpOrder(PostalDelivery $delivery)
    {
        $vtp = app(ViettelPostService::class);

        $result = $vtp->createOrder([
            'receiver_name' => $delivery->customer_name,
            'receiver_phone' => $delivery->customer_phone ?? '',
            'receiver_email' => $delivery->customer_email ?? '',
            'receiver_address' => $delivery->address ?? '',
            'receiver_province' => $delivery->receiver_province ?? 0,
            'receiver_district' => $delivery->receiver_district ?? 0,
            'receiver_ward' => $delivery->receiver_ward ?? 0,
            'product_name' => $delivery->content ?? 'Tài liệu',
            'product_description' => $delivery->content ?? '',
            'product_weight' => $delivery->vtp_weight ?? 100,
            'order_service' => $delivery->vtp_service ?? 'VCN',
            'order_note' => "Người gửi: {$delivery->sender_name}",
            'money_collection' => $delivery->vtp_money_collection ?? 0,
        ]);

        if ($result['success']) {
            $delivery->update([
                'bill_viettel' => $result['bill_code'],
                'vtp_order_code' => $result['bill_code'],
                'vtp_total_fee' => $result['money_total_fee'] ?? 0,
                'vtp_status' => 'created',
                'vtp_status_name' => 'Đã tạo đơn',
            ]);

            $this->resetFields();
            $this->dispatch('closeModal');
            $this->dispatch('swal:success', ['message' => "Tạo đơn VTP thành công! Mã: {$result['bill_code']}"]);
        } else {
            $this->dispatch('swal:error', ['message' => $result['message']]);
        }
    }

    /**
     * Tạo đơn VTP cho delivery đã tồn tại (chưa có mã VTP).
     */
    public function createVtpForExisting($deliveryId)
    {
        $delivery = PostalDelivery::findOrFail($deliveryId);

        if ($delivery->vtp_order_code) {
            $this->dispatch('swal:error', ['message' => 'Đơn này đã có mã VTP rồi!']);
            return;
        }

        if (!$delivery->address || !$delivery->customer_phone || !$delivery->receiver_province) {
            $this->dispatch('swal:error', ['message' => 'Thiếu thông tin (địa chỉ, SĐT, tỉnh/huyện/xã) để tạo đơn VTP.']);
            return;
        }

        $this->createVtpOrder($delivery);
    }

    /**
     * Tra cứu tracking Viettel Post.
     */
    public function trackVtp($deliveryId)
    {
        $delivery = PostalDelivery::findOrFail($deliveryId);
        $billCode = $delivery->vtp_order_code ?? $delivery->bill_viettel;

        if (!$billCode) {
            $this->dispatch('swal:error', ['message' => 'Không có mã bill Viettel Post để tra cứu.']);
            return;
        }

        $vtp = app(ViettelPostService::class);
        $result = $vtp->trackOrder($billCode);

        if ($result['success'] && !empty($result['data'])) {
            $trackingItems = $result['data'];
            $latestStatus = $trackingItems[0] ?? null;

            $delivery->update([
                'vtp_status' => $latestStatus['STATUS_ID'] ?? $delivery->vtp_status,
                'vtp_status_name' => $latestStatus['STATUS_NAME'] ?? $delivery->vtp_status_name,
                'vtp_tracking_data' => $trackingItems,
                'vtp_last_tracked_at' => now(),
            ]);

            $this->trackingDelivery = $delivery->fresh();
            $this->trackingData = $trackingItems;
            $this->dispatch('openTrackingModal');
        } else {
            $this->dispatch('swal:error', ['message' => $result['message'] ?? 'Không thể tra cứu đơn hàng.']);
        }
    }

    public function edit($id)
    {
        $delivery = PostalDelivery::findOrFail($id);
        $this->deliveryId = $delivery->id;
        $this->customer_name = $delivery->customer_name;
        $this->customer_phone = $delivery->customer_phone;
        $this->customer_email = $delivery->customer_email;
        $this->address = $delivery->address;
        $this->receiver_province = $delivery->receiver_province;
        $this->receiver_district = $delivery->receiver_district;
        $this->receiver_ward = $delivery->receiver_ward;
        $this->sender_name = $delivery->sender_name;
        $this->bill_viettel = $delivery->bill_viettel;
        $this->bill_247 = $delivery->bill_247;
        $this->content = $delivery->content;
        $this->department_id = $delivery->department_id;
        $this->status = $delivery->status;
        $this->vtp_service = $delivery->vtp_service ?? 'VCN';
        $this->vtp_weight = $delivery->vtp_weight ?? 100;
        $this->vtp_money_collection = $delivery->vtp_money_collection ?? 0;

        // Load districts & wards nếu đã có province/district
        if ($this->receiver_province) {
            $vtp = app(ViettelPostService::class);
            $this->districts = $vtp->getDistricts((int) $this->receiver_province);
            if ($this->receiver_district) {
                $this->wards = $vtp->getWards((int) $this->receiver_district);
            }
        }

        $this->dispatch('openModal');
    }

    public function delete($id)
    {
        PostalDelivery::destroy($id);
        $this->dispatch('swal:success', ['message' => 'Xóa thành công!']);
    }

    public function render()
    {
        $deliveries = PostalDelivery::query()
            ->with(['department', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('bill_viettel', 'like', '%' . $this->search . '%')
                        ->orWhere('bill_247', 'like', '%' . $this->search . '%')
                        ->orWhere('vtp_order_code', 'like', '%' . $this->search . '%')
                        ->orWhere('sender_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->departmentIdFilter, function ($query) {
                $query->where('department_id', $this->departmentIdFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.postal-deliveries.postal-delivery-manager', [
            'deliveries' => $deliveries,
            'departments' => Department::all(),
        ])->layout('admin.layouts.app', ['title' => 'Quản lý Chuyển phát thư']);
    }
}
