<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Illuminate\Console\Command;

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
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $date = $this->argument('date') ?? now()->subDay()->toDateString();

        $this->comment('Sales files generated successfully.');

        return 0;
    }
}
