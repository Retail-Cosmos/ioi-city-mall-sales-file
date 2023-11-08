<?php

use App\Services\IOICityMallSalesDataService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use RetailCosmos\IoiCityMallSalesFile\Tests\Services\IOICityMallSalesDataServiceMock;

it('throws an error if the configuration file is missing or empty', function () {

    config()->offsetUnset('ioi-city-mall-sales-file');

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('The configuration file is either missing or empty. Please ensure it is properly configured.');

});

it('throws an error if stores is missing or empty', function () {

    config()->offsetUnset('ioi-city-mall-sales-file.stores');

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('The stores array in configuration file is either missing or empty. Please ensure it is properly configured.');

});

it('throws an error if duplicate identifier is found', function () {

    config()->set('ioi-city-mall-sales-file.stores', [
        [
            'identifier' => 'store_1',
            'machine_id' => 11,
            'sst_registered' => true,
        ],
        [
            'identifier' => 'store_1',
            'machine_id' => 22,
            'sst_registered' => true,
        ],
    ]);

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('Duplicate Store identifiers found. Please ensure that each store has a unique identifier.');

});

it('throws an error if duplicate Machine ID is found', function () {

    config()->set('ioi-city-mall-sales-file.stores', [
        [
            'identifier' => 'store_1',
            'machine_id' => 22,
            'sst_registered' => true,
        ],
        [
            'identifier' => 'store_2',
            'machine_id' => 22,
            'sst_registered' => true,
        ],
    ]);

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('Duplicate Machine IDs found. Please ensure that each store has a unique Machine ID.');

});

it('throws an error if disk_to_use is missing or empty', function ($storesData) {

    config()->set('ioi-city-mall-sales-file.stores', $storesData);

    config()->offsetUnset('ioi-city-mall-sales-file.disk_to_use');

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('The disk_to_use key in configuration file is not set. Please ensure it is properly configured.');

})->with('stores_data_x2');

it('throws an error if first_file_generation_date is missing or empty', function ($storesData) {

    config()->set('ioi-city-mall-sales-file.stores', $storesData);

    config()->offsetUnset('ioi-city-mall-sales-file.first_file_generation_date');

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.');

})->with('stores_data_x2');

it('throws an error if first_file_generation_date date format is mis-configured', function ($storesData) {

    config()->set('ioi-city-mall-sales-file.stores', $storesData);

    config()->set('ioi-city-mall-sales-file.first_file_generation_date', '2022-22-10');

    Artisan::call('generate:ioi-city-mall-sales-files');

    expect(Artisan::output())->toContain('Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.');

})->with('stores_data_x2');

it('throws an error if undefined identifier is used', function ($storesData) {

    config()->set('ioi-city-mall-sales-file.stores', $storesData);

    Artisan::call('generate:ioi-city-mall-sales-files', ['--identifier' => $store = 'store_22']);

    expect(Artisan::output())->toContain("No stores found with the identifier {$store}");

})->with('stores_data_x2');

it('generates successful text file', function ($salesData, $storesData) {

    config()->set('ioi-city-mall-sales-file.stores', $storesData);

    app()->bind(IOICityMallSalesDataService::class, function () use ($salesData) {
        return new IOICityMallSalesDataServiceMock($salesData);
    });

    $date = now()->subDay()->format('Ymd');

    Artisan::call('generate:ioi-city-mall-sales-files');

    $output = Artisan::output();

    foreach ($storesData as $store) {
        $fileName = 'H'.$store['machine_id'].'_'.$date;

        $fileExists = Storage::disk(config('ioi-city-mall-sales-file.disk_to_use'))->exists('pending_to_upload/'.$fileName);

        expect($fileExists)->toBeTrue();

        expect($output)->toContain("{$fileName} has been created");
    }

    expect($output)->toContain('Sales files generated successfully.');

})->with('sales_data_x2')->with('stores_data_x2');
