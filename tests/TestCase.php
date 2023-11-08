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
        config()->set('database.default', 'testing');
    }
}
