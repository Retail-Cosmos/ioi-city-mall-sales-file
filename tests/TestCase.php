<?php

namespace RetailCosmos\IoiCityMallSalesFile\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RetailCosmos\IoiCityMallSalesFile\IoiCityMallSalesFileServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            IoiCityMallSalesFileServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('ioi-city-mall-sales-file.disk_to_use', 'local');
        config()->set('ioi-city-mall-sales-file.first_file_generation_date', '2023-10-22');
        config()->set('database.default', 'testing');
    }
}
