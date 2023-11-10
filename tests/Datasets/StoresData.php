<?php

dataset('stores_data_x2', [
    [
        fakeStoresData(2),
    ],
]);

dataset('stores_data_x5', [
    [
        fakeStoresData(5),
    ],
]);

function fakeStoresData($times = 1): array
{
    $fakeDataArray = [];

    for ($i = 0; $i < $times; $i++) {
        $fakeDataArray[] = [
            'identifier' => 'store_'.fake()->unique()->randomNumber(3),
            'machine_id' => fake()->unique()->randomNumber(7),
            'sst_registered' => fake()->boolean(),
        ];
    }

    return $fakeDataArray;
}

function sampleStoresData1(): array
{
    return [
        [
            'identifier' => 'store_1',
            'machine_id' => 87654321,
            'sst_registered' => false,
        ],
    ];
}

function sampleStoresData2(): array
{
    return [
        [
            'identifier' => 'store_22',
            'machine_id' => 39193343,
            'sst_registered' => true,
        ],
    ];
}
