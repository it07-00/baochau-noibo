<?php

return [
    'potential_report' => [
        'cache_ttl_seconds' => 300,

        'service_score_weights' => [
            'opportunities' => 30,
            'potential_value' => 30,
            'conversion_rate' => 25,
            'growth' => 15,
        ],

        'region_score_weights' => [
            'opportunities' => 35,
            'potential_value' => 25,
            'conversion_rate' => 20,
            'revenue' => 20,
        ],

        // Dữ liệu cũ từng dùng nhãn này trước khi chuẩn hóa bằng QuotationStatus.
        'legacy_lost_quotation_statuses' => ['Mất đơn'],
    ],
];
