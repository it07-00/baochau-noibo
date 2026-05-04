<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViettelPostService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.viettelpost.base_url');
        $this->username = config('services.viettelpost.username');
        $this->password = config('services.viettelpost.password');
    }

    /**
     * Lấy token xác thực từ Viettel Post API.
     * Token được cache 20 giờ (hết hạn sau 24h).
     */
    public function getToken(): ?string
    {
        $encrypted = Cache::remember('viettelpost_token', now()->addHours(20), function () {
            $response = Http::post("{$this->baseUrl}/user/Login", [
                'USERNAME' => $this->username,
                'PASSWORD' => $this->password,
            ]);

            if ($response->successful() && $response->json('status') == 200) {
                return Crypt::encryptString($response->json('data.token'));
            }

            Log::error('ViettelPost login failed', [
                'status' => $response->status(),
            ]);

            return null;
        });

        return $encrypted ? Crypt::decryptString($encrypted) : null;
    }

    /**
     * Tạo đơn hàng trên Viettel Post.
     */
    public function createOrder(array $data): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Không thể xác thực với Viettel Post.'];
        }

        $sender = config('services.viettelpost');

        $payload = [
            'ORDER_NUMBER' => $data['order_number'] ?? '',
            'GROUPADDRESS_ID' => $data['group_address_id'] ?? 0,
            'CUS_ID' => $data['cus_id'] ?? 0,
            'DELIVERY_DATE' => $data['delivery_date'] ?? now()->format('d/m/Y H:i:s'),
            'SENDER_FULLNAME' => $sender['sender_name'],
            'SENDER_ADDRESS' => $sender['sender_address'],
            'SENDER_PHONE' => $sender['sender_phone'],
            'SENDER_EMAIL' => '',
            'SENDER_WARD' => (int) $sender['sender_ward'],
            'SENDER_DISTRICT' => (int) $sender['sender_district'],
            'SENDER_PROVINCE' => (int) $sender['sender_province'],
            'RECEIVER_FULLNAME' => $data['receiver_name'],
            'RECEIVER_ADDRESS' => $data['receiver_address'],
            'RECEIVER_PHONE' => $data['receiver_phone'] ?? '',
            'RECEIVER_EMAIL' => $data['receiver_email'] ?? '',
            'RECEIVER_WARD' => (int) ($data['receiver_ward'] ?? 0),
            'RECEIVER_DISTRICT' => (int) ($data['receiver_district'] ?? 0),
            'RECEIVER_PROVINCE' => (int) ($data['receiver_province'] ?? 0),
            'PRODUCT_NAME' => $data['product_name'] ?? 'Tài liệu',
            'PRODUCT_DESCRIPTION' => $data['product_description'] ?? '',
            'PRODUCT_QUANTITY' => (int) ($data['product_quantity'] ?? 1),
            'PRODUCT_PRICE' => (int) ($data['product_price'] ?? 0),
            'PRODUCT_WEIGHT' => (int) ($data['product_weight'] ?? 100),
            'PRODUCT_LENGTH' => (int) ($data['product_length'] ?? 0),
            'PRODUCT_WIDTH' => (int) ($data['product_width'] ?? 0),
            'PRODUCT_HEIGHT' => (int) ($data['product_height'] ?? 0),
            'ORDER_PAYMENT' => (int) ($data['order_payment'] ?? 3), // 3 = người gửi trả phí
            'ORDER_SERVICE' => $data['order_service'] ?? 'VCN', // VCN = chuyển phát nhanh
            'ORDER_SERVICE_ADD' => $data['order_service_add'] ?? '',
            'ORDER_VOUCHER' => '',
            'ORDER_NOTE' => $data['order_note'] ?? '',
            'MONEY_COLLECTION' => (int) ($data['money_collection'] ?? 0),
            'MONEY_TOTALFEE' => 0,
            'MONEY_FEECOD' => 0,
            'MONEY_FEEVAS' => 0,
            'MONEY_FEEINSURANCE' => 0,
            'MONEY_FEE' => 0,
            'MONEY_FEEOTHER' => 0,
            'MONEY_TOTALVAT' => 0,
            'MONEY_TOTAL' => 0,
        ];

        $response = Http::withHeaders(['Token' => $token])
            ->post("{$this->baseUrl}/order/createOrder", $payload);

        if ($response->successful() && $response->json('status') == 200) {
            $orderData = $response->json('data');
            return [
                'success' => true,
                'bill_code' => $orderData['ORDER_NUMBER'] ?? null,
                'money_total' => $orderData['MONEY_TOTAL'] ?? 0,
                'money_total_fee' => $orderData['MONEY_TOTALFEE'] ?? 0,
                'expected_delivery' => $orderData['EXPECTED_DELIVERY'] ?? null,
                'data' => $orderData,
            ];
        }

        Log::error('ViettelPost createOrder failed', [
            'status' => $response->status(),
            'body' => $response->json(),
            'payload' => $payload,
        ]);

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Tạo đơn thất bại.',
        ];
    }

    /**
     * Tra cứu trạng thái đơn hàng theo mã bill.
     */
    public function trackOrder(string $billCode): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Không thể xác thực với Viettel Post.'];
        }

        $response = Http::withHeaders(['Token' => $token])
            ->get("{$this->baseUrl}/order/trackingByBillCode", [
                'billcode' => $billCode,
            ]);

        if ($response->successful() && $response->json('status') == 200) {
            return [
                'success' => true,
                'data' => $response->json('data'),
            ];
        }

        Log::error('ViettelPost tracking failed', [
            'bill_code' => $billCode,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Không thể tra cứu đơn hàng.',
        ];
    }

    /**
     * Tính phí vận chuyển.
     */
    public function getPricing(array $data): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['success' => false, 'message' => 'Không thể xác thực với Viettel Post.'];
        }

        $sender = config('services.viettelpost');

        $payload = [
            'PRODUCT_WEIGHT' => (int) ($data['weight'] ?? 100),
            'PRODUCT_PRICE' => (int) ($data['price'] ?? 0),
            'MONEY_COLLECTION' => (int) ($data['money_collection'] ?? 0),
            'ORDER_SERVICE_ADD' => $data['service_add'] ?? '',
            'ORDER_SERVICE' => $data['service'] ?? 'VCN',
            'SENDER_DISTRICT' => (int) $sender['sender_district'],
            'SENDER_PROVINCE' => (int) $sender['sender_province'],
            'RECEIVER_DISTRICT' => (int) ($data['receiver_district'] ?? 0),
            'RECEIVER_PROVINCE' => (int) ($data['receiver_province'] ?? 0),
            'PRODUCT_TYPE' => $data['product_type'] ?? 'HH', // HH=hàng hóa, TL=tài liệu
            'NATIONAL_TYPE' => 1, // Nội địa
        ];

        $response = Http::withHeaders(['Token' => $token])
            ->post("{$this->baseUrl}/order/getPriceAll", $payload);

        if ($response->successful() && $response->json('status') == 200) {
            return [
                'success' => true,
                'data' => $response->json('data'),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Không thể tính phí.',
        ];
    }

    /**
     * Lấy danh sách tỉnh/thành phố.
     */
    public function getProvinces(): array
    {
        return Cache::remember('vtp_provinces', now()->addDays(30), function () {
            $token = $this->getToken();
            if (!$token) {
                return [];
            }

            $response = Http::withHeaders(['Token' => $token])
                ->get("{$this->baseUrl}/categories/listProvinceById", ['provinceId' => -1]);

            if ($response->successful() && $response->json('status') == 200) {
                return $response->json('data') ?? [];
            }

            return [];
        });
    }

    /**
     * Lấy danh sách quận/huyện theo tỉnh.
     */
    public function getDistricts(int $provinceId): array
    {
        return Cache::remember("vtp_districts_{$provinceId}", now()->addDays(30), function () use ($provinceId) {
            $token = $this->getToken();
            if (!$token) {
                return [];
            }

            $response = Http::withHeaders(['Token' => $token])
                ->get("{$this->baseUrl}/categories/listDistrict", ['provinceId' => $provinceId]);

            if ($response->successful() && $response->json('status') == 200) {
                return $response->json('data') ?? [];
            }

            return [];
        });
    }

    /**
     * Lấy danh sách phường/xã theo quận.
     */
    public function getWards(int $districtId): array
    {
        return Cache::remember("vtp_wards_{$districtId}", now()->addDays(30), function () use ($districtId) {
            $token = $this->getToken();
            if (!$token) {
                return [];
            }

            $response = Http::withHeaders(['Token' => $token])
                ->get("{$this->baseUrl}/categories/listWards", ['districtId' => $districtId]);

            if ($response->successful() && $response->json('status') == 200) {
                return $response->json('data') ?? [];
            }

            return [];
        });
    }

    /**
     * Xóa cached token (khi token hết hạn).
     */
    public function clearToken(): void
    {
        Cache::forget('viettelpost_token');
    }
}
