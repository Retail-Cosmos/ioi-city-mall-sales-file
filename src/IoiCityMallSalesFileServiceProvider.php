<?php

namespace RetailCosmos\IoiCityMallSalesFile;

use RetailCosmos\IoiCityMallSalesFile\Commands\IoiCityMallSalesFileCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IoiCityMallSalesFileServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('ioi-city-mall-sales-file')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_ioi-city-mall-sales-file_table')
            ->hasCommand(IoiCityMallSalesFileCommand::class);
    }
}
