<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Illuminate\Console\Command;
use RetailCosmos\IoiCityMallSalesFile\Interfaces\SalesDataInterface;

class SalesFileGenerationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ioi-city-mall-sales-files {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sales files for IOI City Mall';

    /**
     * Execute the console command.
     */

    protected $salesDataService;

    public function __construct(SalesDataInterface $salesDataService)
    {
        parent::__construct();
        $this->salesDataService = $salesDataService;
    }


    public function handle()
    {
        $date = $this->argument('date') ?? now()->subDay()->toDateString();

        $salesData = $this->salesDataService->handle("d", "d");
        // able to access the data from project's service's method here.

        $this->comment('Sales files generated successfully.');

        return 0;
    }
}
