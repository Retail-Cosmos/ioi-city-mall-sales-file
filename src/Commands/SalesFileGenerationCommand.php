<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class SalesFileGenerationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ioi-city-mall-sales-files {date?} {--identifier=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sales files for IOI City Mall';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->argument('date') ?? now()->subDay()->toDateString();

        $identifier = $this->option('identifier');

        $config = config('ioi-city-mall-sales-file');

        $this->validateConfigFile($config);

        $stores = $identifier ? collect($config['stores'])->where('identifier', $identifier) : collect($config['stores']);

        if ($stores->isEmpty()) {
            $this->error('No stores found with the identifier '.$identifier);
            exit;
        }

        $this->comment('Sales files generated successfully.');

        return 0;
    }

    private function validateConfigFile($config)
    {
        if (! isset($config) || empty($config)) {
            $this->error('The configuration file is either missing or empty. Please ensure it is properly configured.');
            exit;
        }

        if (! isset($config['stores']) || empty($config['stores'])) {
            $this->error('The stores array in configuration file is either missing or empty. Please ensure it is properly configured.');
            exit;
        }

        if (! isset($config['first_file_generation_date']) || ! strtotime($config['first_file_generation_date'])) {
            $this->error('Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.');
            exit;
        }

        try {
            $firstFileDate = Carbon::createFromFormat('Y-m-d', $config['first_file_generation_date']);
            if ($firstFileDate->format('Y-m-d') !== $config['first_file_generation_date']) {
                throw new \Exception();
            }
        } catch (\Throwable $th) {
            $this->error('Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.');
            exit;
        }
    }
}
