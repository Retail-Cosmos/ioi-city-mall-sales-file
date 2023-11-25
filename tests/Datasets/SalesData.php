<?php

dataset('static_sales_data_1', [
    [
        collect(sampleSalesData2()),
    ],
]);

// sales data of date 2023-01-01
function sampleSalesData1(): array
{
    return [
        [
            'happened_at' => '2023-01-01 11:24:00',
            'net_amount' => 80,
            'discount' => 20,
            'SST' => 0,
            'payments' => [
                'cash' => 50,
                'tng' => 0,
                'visa' => 30,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 0,
                'others' => 0,
            ],
        ],
        [
            'happened_at' => '2023-01-01 18:03:00',
            'net_amount' => 19.80,
            'discount' => 0,
            'SST' => 0,
            'payments' => [
                'cash' => 0,
                'tng' => 0,
                'visa' => 0,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 10,
                'others' => 9.80,
            ],
        ],
    ];
}

// sales data of date 2023-10-31
function sampleSalesData2(): array
{
    return [
        [
            'happened_at' => '2023-10-31 11:15 AM',
            'net_amount' => 80,
            'discount' => 20,
            'SST' => 0,
            'payments' => [
                'cash' => 50,
                'tng' => 0,
                'visa' => 30,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 0,
                'others' => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 11:40 AM',
            'net_amount' => 80,
            'discount' => 20,
            'SST' => 0,
            'payments' => [
                'cash' => 50,
                'tng' => 0,
                'visa' => 30,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 0,
                'others' => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 12:30 PM',
            'net_amount' => 130,
            'discount' => 10,
            'SST' => 0,
            'payments' => [
                'cash' => 80,
                'tng' => 10,
                'visa' => 40,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 0,
                'others' => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 02:45 PM',
            'net_amount' => 165,
            'discount' => 25,
            'SST' => 0,
            'payments' => [
                'cash' => 100,
                'tng' => 15,
                'visa' => 50,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 0,
                'others' => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 04:00 PM',
            'net_amount' => 110,
            'discount' => 15,
            'SST' => 0,
            'payments' => [
                'cash' => 60,
                'tng' => 5,
                'visa' => 30,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 10,
                'others' => 5,
            ],
        ],
    ];

}
