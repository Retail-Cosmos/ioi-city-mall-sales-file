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

function fakeSalesData($times = 1)
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
