<?php

namespace RetailCosmos\IoiCityMallSalesFile;

use RetailCosmos\IoiCityMallSalesFile\Commands\SalesFileGenerationCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class IoiCityMallSalesFileServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('ioi-city-mall-sales-file')
            ->hasConfigFile()
            ->hasCommand(SalesFileGenerationCommand::class);
    }
}
