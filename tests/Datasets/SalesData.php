<?php

use RetailCosmos\IoiCityMallSalesFile\Enums\PaymentType;

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
                PaymentType::CASH->value => 50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 30,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 0,
            ],
        ],
        [
            'happened_at' => '2023-01-01 18:03:00',
            'net_amount' => 19.80,
            'discount' => 0,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => 0,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 0,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 10,
                PaymentType::OTHERS->value => 9.80,
            ],
        ],
    ];
}

// sales data of date 2023-10-31
function sampleSalesData2(): array
{
    return [
        [
            'happened_at' => '2023-10-31 11:15:00',
            'net_amount' => 80,
            'discount' => 20,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => 50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 30,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 11:40:00',
            'net_amount' => 80,
            'discount' => 20,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => 50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 30,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 12:30:00',
            'net_amount' => 130,
            'discount' => 10,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => 80,
                PaymentType::TNG->value => 10,
                PaymentType::VISA->value => 40,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 14:45:00',
            'net_amount' => 165,
            'discount' => 25,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => 100,
                PaymentType::TNG->value => 15,
                PaymentType::VISA->value => 50,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => 0,
            ],
        ],
        [
            'happened_at' => '2023-10-31 16:00:00',
            'net_amount' => 110,
            'discount' => 15,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => 60,
                PaymentType::TNG->value => 5,
                PaymentType::VISA->value => 30,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 10,
                PaymentType::OTHERS->value => 5,
            ],
        ],
    ];

}

function refundSalesData(): array
{
    return [
        [
            'happened_at' => '2023-10-31 08:15:00',
            'net_amount' => -100,
            'discount' => -20,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => -50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 0,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => -50,
            ],
        ],
        [
            'happened_at' => '2023-10-31 18:15:00',
            'net_amount' => -100,
            'discount' => -20,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => -50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 0,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => -50,
            ],
        ],
        [
            'happened_at' => '2023-10-31 18:45:00',
            'net_amount' => -100,
            'discount' => -20,
            'SST' => 0,
            'payments' => [
                PaymentType::CASH->value => -50,
                PaymentType::TNG->value => 0,
                PaymentType::VISA->value => 0,
                PaymentType::MASTERCARD->value => 0,
                PaymentType::AMEX->value => 0,
                PaymentType::VOUCHER->value => 0,
                PaymentType::OTHERS->value => -50,
            ],
        ],
    ];

}
