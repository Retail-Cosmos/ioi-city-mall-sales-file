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

function fakeStoresData($times = 1)
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
