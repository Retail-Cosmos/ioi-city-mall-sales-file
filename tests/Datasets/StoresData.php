<?php

dataset('stores_data_x2', [
    [
        collect(fakeStoresData(2)),
    ],
]);

function fakeStoresData($times = 1): array
{
    $fakeDataArray = [];

    for ($i = 0; $i < $times; $i++) {
        $fakeDataArray[] = [
            'store_identifier' => 'store_'.fake()->unique()->randomNumber(3),
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
            'store_identifier' => 'store_1',
            'machine_id' => 87654321,
            'sst_registered' => false,
        ],
    ];
}

function sampleStoresData2(): array
{
    return [
        [
            'store_identifier' => 'store_22',
            'machine_id' => 39193343,
            'sst_registered' => true,
        ],
    ];
}
