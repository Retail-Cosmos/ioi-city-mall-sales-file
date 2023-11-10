<?php

dataset('sales_data_x2', [
    [
        fakeSalesData(2),
    ],
]);

dataset('sales_data_x5', [
    [
        fakeSalesData(5),
    ],
]);

function fakeSalesData($times = 1): array
{
    $fakeDataArray = [];

    for ($i = 0; $i < $times; $i++) {
        $fakeDataArray[] = [
            'happened_at' => now()->subDay()->format('Y-m-d').' '.fake()->time(),
            'net_amount' => fake()->numberBetween(1, 1000),
            'discount' => fake()->numberBetween(0, 100),
            'SST' => fake()->numberBetween(0, 100),
            'payments' => [
                'cash' => fake()->numberBetween(0, 100),
                'tng' => fake()->numberBetween(0, 100),
                'visa' => fake()->numberBetween(0, 100),
                'mastercard' => fake()->numberBetween(0, 100),
                'amex' => fake()->numberBetween(0, 100),
                'voucher' => fake()->numberBetween(0, 100),
                'others' => fake()->numberBetween(0, 100),
            ],
        ];
    }

    return $fakeDataArray;
}

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
                'cash' => 50,
                'tng' => 0,
                'visa' => 30,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 10,
                'others' => 9.80,
            ],
        ],
    ];
}

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
                'tng' => 5,
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
                'tng' => 5,
                'visa' => 30,
                'mastercard' => 0,
                'amex' => 0,
                'voucher' => 0,
                'others' => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 12:30 PM',
            'net_amount' => 120,
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
            'net_amount' => 150,
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
            'net_amount' => 95,
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
