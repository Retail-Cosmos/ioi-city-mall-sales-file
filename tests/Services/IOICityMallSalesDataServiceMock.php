<?php

namespace RetailCosmos\IoiCityMallSalesFile\Tests\Services;

class IOICityMallSalesDataServiceMock
{
    private $salesData;

    public function __construct($salesData)
    {
        $this->salesData = $salesData;
    }

    public function handle()
    {
        return collect($this->salesData);
    }
}
