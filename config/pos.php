<?php

return [
    'store_name' => env('POS_STORE_NAME', 'Kopi Kita'),
    'products' => [
        'kopi-susu' => [
            'name' => 'Kopi Susu',
            'price' => 18000,
        ],
        'americano' => [
            'name' => 'Americano',
            'price' => 15000,
        ],
        'roti-bakar' => [
            'name' => 'Roti Bakar',
            'price' => 12000,
        ],
    ],
    'receipt' => [
        'driver' => env('RECEIPT_DRIVER', 'pdf'),
        'connector' => env('RECEIPT_CONNECTOR', 'file'),
        'printer_name' => env('RECEIPT_PRINTER_NAME', 'POS-58'),
        'output_path' => storage_path('app/private/receipts'),
    ],
];
