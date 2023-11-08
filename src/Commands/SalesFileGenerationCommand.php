<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Carbon\Carbon;
use Exception;
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

        try {
            $this->validateConfigFile($config);

            $stores = $identifier ? collect($config['stores'])->where('identifier', $identifier) : collect($config['stores']);

            if ($stores->isEmpty()) {
                if (! empty($identifier)) {
                    throw new Exception("No stores found with the identifier {$identifier}");
                } else {
                    throw new Exception('No stores found');
                }
            }

            $this->comment('Sales files generated successfully.');

            return 0;
        } catch (Exception $e) {
            $this->error($e->getMessage(), 1);

            return 1;
        }
    }

    private function validateConfigFile($config)
    {
        if (! isset($config) || empty($config)) {
            throw new Exception('The configuration file is either missing or empty. Please ensure it is properly configured.');
        }

        if (! isset($config['stores']) || empty($config['stores'])) {
            throw new Exception('The stores array in configuration file is either missing or empty. Please ensure it is properly configured.');
        }

        $stores = $config['stores'];
        $identifiers = array_column($stores, 'identifier');
        $machineIds = array_column($stores, 'machine_id');

        if (count($identifiers) > count(array_unique($identifiers))) {
            throw new Exception('Duplicate Store identifiers found. Please ensure that each store has a unique identifier.');
        }

        if (count($machineIds) > count(array_unique($machineIds))) {
            throw new Exception('Duplicate Machine IDs found. Please ensure that each store has a unique Machine ID.');
        }

        if (! isset($config['disk_to_use']) || empty($config['disk_to_use'])) {
            throw new Exception('The disk_to_use key in configuration file is not set. Please ensure it is properly configured.');
        }

        try {
            if (! isset($config['first_file_generation_date']) || ! strtotime($config['first_file_generation_date'])) {
                throw new Exception();
            }

            $firstFileDate = Carbon::createFromFormat('Y-m-d', $config['first_file_generation_date']);
            if ($firstFileDate->format('Y-m-d') !== $config['first_file_generation_date']) {
                throw new Exception();
            }
        } catch (\Throwable $th) {
            throw new Exception('Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.');
        }
    }
}
