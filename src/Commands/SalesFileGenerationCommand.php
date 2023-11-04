<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use App\Services\IOICityMallSalesDataService;
use Carbon\Carbon;
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

        $this->validateConfigFile($config);

        $stores = $identifier ? collect($config['stores'])->where('identifier', $identifier) : collect($config['stores']);

        if ($stores->isEmpty()) {
            $this->error('No stores found with the identifier '.$identifier);
            exit;
        }

        $salesDataService = resolve(IOICityMallSalesDataService::class); // @phpstan-ignore-line

        $salesData = $salesDataService->handle($date, $identifier);

        $stores->each(function ($store) use ($config, $date, $salesData) {

            $file = $this->salesFileService->generate($config, $store, $date, $salesData);

            $this->info($file.' has been created');

        });

        $this->comment('Sales files generated successfully.');

        return 0;
    }

    private function validateConfigFile(array $config): void
    {
        if (empty($config)) {
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
