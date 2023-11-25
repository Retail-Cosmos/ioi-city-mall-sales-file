<?php

use App\Services\IOICityMallSalesDataService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RetailCosmos\IoiCityMallSalesFile\Notifications\SalesFileGenerationNotification;

beforeEach(function (): void {
    $this->email = config('ioi-city-mall-sales-file.notifications.email');
    Notification::fake();

    $this->serviceMock = Mockery::mock(IOICityMallSalesDataService::class);
    $this->app->instance(IOICityMallSalesDataService::class, $this->serviceMock);
});

describe('Configuration Checks', function () {
    it('throws an error if the configuration file is missing or empty', function () {

        config()->offsetUnset('ioi-city-mall-sales-file');

        Artisan::call('generate:ioi-city-mall-sales-files');

        Notification::assertNothingSent();

        expect(Artisan::output())->toContain('The configuration file is either missing or empty. Please ensure it is properly configured.');

    });

    it('throws an error if disk_to_use is missing or empty', function () {

        config()->offsetUnset('ioi-city-mall-sales-file.disk_to_use');

        Artisan::call('generate:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('The disk_to_use key in configuration file is not set. Please ensure it is properly configured.');

    });

    it('throws an error if first_file_generation_date is missing or empty', function () {

        config()->offsetUnset('ioi-city-mall-sales-file.first_file_generation_date');

        Artisan::call('generate:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('The first_file_generation_date key in configuration file is not set. Please ensure it is properly configured.');

    });

    it('throws an error if first_file_generation_date date format is mis-configured', function () {

        config()->set('ioi-city-mall-sales-file.first_file_generation_date', '2022-22-10');

        Artisan::call('generate:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.');

    });
});

describe('Error Scenarios', function () {

    it('throws an error if stores is missing or empty', function () {

        $this->serviceMock->shouldReceive('storesList')->andReturn(null);

        Artisan::call('generate:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('The stores array is either missing or empty. Please ensure it has proper values.');

    });

    it('throws an error if duplicate store_identifier is found', function () {

        $stores = collect([
            [
                'store_identifier' => 'store_1',
                'machine_id' => 11,
                'sst_registered' => true,
            ],
            [
                'store_identifier' => 'store_1',
                'machine_id' => 22,
                'sst_registered' => true,
            ],
        ]);

        $this->serviceMock->shouldReceive('storesList')->andReturn($stores);

        Artisan::call('generate:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('Duplicate Store identifiers found. Please ensure that each store has a unique store_identifier.');

    });

    it('throws an error if duplicate Machine ID is found', function () {

        $stores = collect([
            [
                'store_identifier' => 'store_1',
                'machine_id' => 22,
                'sst_registered' => true,
            ],
            [
                'store_identifier' => 'store_2',
                'machine_id' => 22,
                'sst_registered' => true,
            ],
        ]);

        $this->serviceMock->shouldReceive('storesList')->andReturn($stores);

        Artisan::call('generate:ioi-city-mall-sales-files');

        expect(Artisan::output())->toContain('Duplicate Machine IDs found. Please ensure that each store has a unique Machine ID.');

    });

    it('throws an error if undefined store_identifier is used', function ($stores) {

        $this->serviceMock->shouldReceive('storesList')->andReturn($stores);

        Artisan::call('generate:ioi-city-mall-sales-files', ['--store_identifier' => $store = 'store_no_5442']);

        expect(Artisan::output())->toContain("No Stores found with the store_identifier {$store}");

    })->with('stores_data_x2');

    it('throws an error if happened_at date and date argument is not same', function ($stores, $sales) {

        $this->serviceMock->shouldReceive('storesList')->andReturn($stores);
        $this->serviceMock->shouldReceive('salesData')->andReturn($sales);

        Artisan::call('generate:ioi-city-mall-sales-files');

        $output = Artisan::output();

        $previousDaysDate = now()->subDay()->toDateString();

        expect($output)->toContain("Sales data must have records of the date {$previousDaysDate} only. Sales from other dates are not allowed.");

    })->with('stores_data_x2')->with('static_sales_data_1');

    afterEach(function (): void {
        Notification::assertSentOnDemand(
            SalesFileGenerationNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] == $this->email
                && $notification->getStatus() === 'error';
            }
        );
    });
});

describe('Success Scenarios', function () {

    it('generates successful text file with static stores & sales data test', function () {

        $salesData = [sampleSalesData1(), sampleSalesData2()];

        $storesData = [sampleStoresData1(), sampleStoresData2()];

        $dates = ['2023-01-01', '2023-10-31'];

        for ($i = 0; $i < 2; $i++) {

            $this->serviceMock = Mockery::mock(IOICityMallSalesDataService::class);
            $this->app->instance(IOICityMallSalesDataService::class, $this->serviceMock);

            $this->serviceMock->shouldReceive('storesList')->andReturn(collect($storesData[$i]));

            $this->serviceMock->shouldReceive('salesData')->andReturn(collect($salesData[$i]));

            Artisan::call('generate:ioi-city-mall-sales-files', ['date' => $dates[$i]]);

            $output = Artisan::output();

            foreach ($storesData[$i] as $store) {

                $formattedDate = Carbon::parse($dates[$i])->format('Ymd');

                $fileName = 'H'.$store['machine_id'].'_'.$formattedDate.'.txt';

                $config = config('ioi-city-mall-sales-file.disk_to_use');

                $filePath = 'pending_to_upload/'.$fileName;

                $fileExists = Storage::disk($config)->exists($filePath);

                $fileContents = Storage::disk($config)->get($filePath);

                expect($fileContents)->toMatchSnapshot();

                expect($fileExists)->toBeTrue();

                expect($output)->toContain("{$fileName} has been created");
            }

            expect($output)->toContain('Sales files generated successfully.');
        }

    });

    it('generates successful text file with 0 sales', function () {

        $stores = sampleStoresData1();

        $this->serviceMock->shouldReceive('storesList')->andReturn(collect($stores));

        $this->serviceMock->shouldReceive('salesData')->andReturn(collect([]));

        $date = '2023-06-01';

        Artisan::call('generate:ioi-city-mall-sales-files', ['date' => $date]);

        $output = Artisan::output();

        foreach ($stores as $store) {

            $formattedDate = Carbon::parse($date)->format('Ymd');

            $fileName = 'H'.$store['machine_id'].'_'.$formattedDate.'.txt';

            $config = config('ioi-city-mall-sales-file.disk_to_use');

            $filePath = 'pending_to_upload/'.$fileName;

            $fileExists = Storage::disk($config)->exists($filePath);

            $fileContents = Storage::disk($config)->get($filePath);

            expect($fileContents)->toMatchSnapshot();

            expect($fileExists)->toBeTrue();

            expect($output)->toContain("{$fileName} has been created");
        }

        expect($output)->toContain('Sales files generated successfully.');

    });

    it('generates successful text file with specific store_identifier & date', function () {

        $salesData = sampleSalesData2();

        $storesData = [...sampleStoresData1(), ...sampleStoresData2()];

        $date = '2023-10-31';

        $this->serviceMock->shouldReceive('storesList')->andReturn(collect($storesData));

        $this->serviceMock->shouldReceive('salesData')->andReturn(collect($salesData));

        Artisan::call('generate:ioi-city-mall-sales-files', ['date' => $date, '--store_identifier' => 'store_22']);

        $output = Artisan::output();

        $formattedDate = Carbon::parse($date)->format('Ymd');

        $fileName = 'H'.$storesData[1]['machine_id'].'_'.$formattedDate.'.txt';

        $config = config('ioi-city-mall-sales-file.disk_to_use');

        $filePath = 'pending_to_upload/'.$fileName;

        $fileExists = Storage::disk($config)->exists($filePath);

        $fileContents = Storage::disk($config)->get($filePath);

        expect($fileContents)->toMatchSnapshot();

        expect($fileContents)->toContain($storesData[1]['machine_id']);

        expect($fileExists)->toBeTrue();

        expect($output)->toContain("{$fileName} has been created");

        expect($output)->toContain('Sales files generated successfully.');

    });

    afterEach(function (): void {
        Notification::assertSentOnDemand(
            SalesFileGenerationNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] == $this->email
                && $notification->getStatus() === 'success';
            }
        );
    });
});

afterEach(function (): void {
    Mockery::close();
});
