<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use App\Services\IOICityMallSalesDataService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RetailCosmos\IoiCityMallSalesFile\Notifications\SalesFileNotification;
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
        try {
            [$date, $notificationConfig, $logChannel] = $this->validateCommunicationChannels();

            $config = $this->validateAndGetConfig();

            [$stores] = $this->validateOptions();

            $this->generateSalesFiles($config, collect($stores), $date);

            $message = 'Sales files generated successfully.';

            Log::channel($logChannel)->info($message);

            Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileNotification(status: 'success', messages: "Sales File Generated Successfully for the date of {$date} & has been stored to specified disk"));

            $this->comment($message);

            return 0;

        } catch (Exception $e) {
            $message = "An Error Encountered while generating the Sales file - {$e->getMessage()}";

            if (! empty($logChannel)) {
                Log::channel($logChannel)->error($message);
            }

            if (! empty($notificationConfig) && ! empty($date)) {
                Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileNotification(status: 'error', messages: "Sales File Generation Failed for the date of {$date} - {$e->getMessage()}"));
            }

            $this->error($e->getMessage(), 1);

            return 1;
        }
    }

    protected function validateCommunicationChannels(): array
    {
        $config = config('ioi-city-mall-sales-file');

        if (! isset($config) || empty($config)) {
            throw new Exception('The configuration file is either missing or empty. Please ensure it is properly configured.');
        }

        $validator = Validator::make($config, [
            'notifications' => ['required'],
            'notifications.name' => ['required'],
            'notifications.email' => ['required', 'email'],
            'log_channel_for_file_generation' => ['required'],
        ], [
            'notifications.name.required' => 'Please set name in config notifications array',
            'notifications.email.required' => 'Please set e-mail in config notifications array',
            'notifications.email.email' => 'Please set valid e-mail in config notifications array',
            'log_channel_for_file_generation.required' => 'Please set the log channel for file generation',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        [$date] = $this->validateArguments();

        return [
            $date,
            ...array_values($validator->validated()),
        ];

    }

    protected function validateArguments(): array
    {
        $validator = Validator::make($this->arguments(), [
            'date' => ['nullable', 'date', 'date_format:"Y-m-d"'],
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $date = $this->argument('date');

        if (is_null($date)) {
            $date = now()->subDay()->toDateString();
        }

        return [$date];
    }

    protected function validateOptions(): array
    {
        $config = config('ioi-city-mall-sales-file');

        $identifiers = array_column($config['stores'], 'identifier');

        $validator = Validator::make($this->options(), [
            'identifier' => ['nullable', Rule::in($identifiers)],
        ], [
            'identifier' => "No stores found with the identifier {$this->option('identifier')}",
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return [$config['stores']];
    }

    private function validateAndGetConfig(): array
    {
        $validator = Validator::make(config('ioi-city-mall-sales-file'), [
            'stores' => ['required', 'array'],
            'stores.*.identifier' => ['required', 'distinct'],
            'stores.*.machine_id' => ['required', 'distinct'],
            'stores.*.sst_registered' => ['required', 'boolean'],
            'disk_to_use' => ['required'],
            'sftp' => ['required'],
            'log_channel_for_file_upload' => ['required'],
            'first_file_generation_date' => ['required', 'date_format:"Y-m-d"'],

        ], [
            'stores.*.identifier.distinct' => 'Duplicate Store identifiers found. Please ensure that each store has a unique identifier.',
            'stores.*.identifier.required' => 'Identifier is either missing or empty in one of the items. Please ensure it is properly configured.',
            'stores.*.machine_id.required' => 'Machine ID is either missing or empty in one of the items. Please ensure it is properly configured.',
            'stores.*.machine_id.distinct' => 'Duplicate Machine IDs found. Please ensure that each store has a unique Machine ID.',
            'stores.required' => 'The stores array in configuration file is either missing or empty. Please ensure it is properly configured.',
            'disk_to_use.required' => 'The disk_to_use key in configuration file is not set. Please ensure it is properly configured.',
            'first_file_generation_date.required' => 'The first_file_generation_date key in configuration file is not set. Please ensure it is properly configured.',
            'first_file_generation_date.date_format' => 'Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    private function generateSalesFiles(array $config, Collection $stores, string $date): void
    {
        $salesDataService = resolve(IOICityMallSalesDataService::class); // @phpstan-ignore-line

        $stores->each(function ($store) use ($config, $date, $salesDataService) {
            $salesData = $salesDataService->handle($date, $store['identifier']);

            $validSalesDataCount = $salesData->where(function ($item) use ($date) {
                return Carbon::parse($item['happened_at'])->isSameDay($date);
            })->count();

            if ($validSalesDataCount < $salesData->count()) {
                throw new Exception("Sales data must have records of the date {$date} only. Sales from other dates are not allowed.");
            }

            $file = $this->salesFileService->generate($config, $store, $date, $salesData);

            $this->info($file.' has been created');
        });
    }
}
