<?php

namespace RetailCosmos\IoiCityMallSalesFile\Interfaces;

use Illuminate\Support\Collection;

interface SalesDataInterface
{
    public function handle(string $identifier, string $data): Collection;
}
