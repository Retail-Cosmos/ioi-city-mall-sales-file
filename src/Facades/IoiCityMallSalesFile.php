<?php

namespace RetailCosmos\IoiCityMallSalesFile\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RetailCosmos\IoiCityMallSalesFile\IoiCityMallSalesFile
 */
class IoiCityMallSalesFile extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \RetailCosmos\IoiCityMallSalesFile\IoiCityMallSalesFile::class;
    }
}
