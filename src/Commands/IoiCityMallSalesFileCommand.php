<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Illuminate\Console\Command;

class IoiCityMallSalesFileCommand extends Command
{
    public $signature = 'ioi-city-mall-sales-file';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
