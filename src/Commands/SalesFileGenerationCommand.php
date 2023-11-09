<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use App\Services\IOICityMallSalesDataService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use RetailCosmos\IoiCityMallSalesFile\Services\SalesFileService;

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

    protected SalesFileService $salesFileService;

    public function __construct(SalesFileService $salesFileService)
    {
        parent::__construct();
        $this->salesFileService = $salesFileService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->argument('date') ?? now()->subDay()->toDateString();

        $identifier = $this->option('identifier');

        $config = config('ioi-city-mall-sales-file');

        try {

            if (! isset($config) || empty($config)) {
                throw new Exception('The configuration file is either missing or empty. Please ensure it is properly configured.');
            }

            $this->validateConfigFile($config);

            $stores = $identifier ? collect($config['stores'])->where('identifier', $identifier) : collect($config['stores']);

            if ($stores->isEmpty()) {
                if (! empty($identifier)) {
                    throw new Exception("No stores found with the identifier {$identifier}");
                } else {
                    throw new Exception('No stores found');
                }
            }

            $stores->each(function ($store) use ($config, $date) {

                $salesDataService = resolve(IOICityMallSalesDataService::class); // @phpstan-ignore-line

                $salesData = $salesDataService->handle($date, $store['identifier']);

                $file = $this->salesFileService->generate($config, $store, $date, $salesData);

                $this->info($file.' has been created');

            });

            $this->comment('Sales files generated successfully.');

            return 0;

        } catch (Exception $e) {
            $this->error($e->getMessage(), 1);

            return 1;
        }

    }

    private function validateConfigFile(array $config): void
    {

        if (! isset($config['stores']) || empty($config['stores'])) {
            throw new Exception('The stores array in configuration file is either missing or empty. Please ensure it is properly configured.');
        }

        $stores = $config['stores'];
        $identifiers = array_column($stores, 'identifier');
        $machineIds = array_column($stores, 'machine_id');

        if (in_array(null, $machineIds)) {
            throw new Exception('Machine ID is either missing or empty in one of the items. Please ensure it is properly configured.');
        }

        if (in_array(null, $identifiers)) {
            throw new Exception('Identifier is either missing or empty in one of the items. Please ensure it is properly configured.');
        }

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
