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
use RetailCosmos\IoiCityMallSalesFile\Enums\PaymentType;
use RetailCosmos\IoiCityMallSalesFile\Notifications\SalesFileGenerationNotification;
use RetailCosmos\IoiCityMallSalesFile\Services\SalesFileService;

class SalesFileGenerationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ioi-city-mall-sales-files {date?} {--store_identifier=}';

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
        if (! config('ioi-city-mall-sales-file.enable_file_generation')) {
            $this->warn('File generation is disabled. Please check your .env file.');

            return 1;
        }

        try {
            [$notificationConfig, $logChannel] = $this->validateCommunicationChannels();

            [$date] = $this->validateArguments();

            $config = $this->validateAndGetConfig();

            $stores = $this->validateAndGetStores();

            if ($stores->isEmpty()) {
                $message = 'No stores returned. Command completes without file generation.';

                $this->comment($message);
                Log::channel($logChannel)->info($message);

                return 0;
            }

            $this->generateSalesFiles($config, $stores, $date);

            $message = 'Sales files generated successfully.';

            Log::channel($logChannel)->info($message);

            if (! empty($notificationConfig['email'])) {
                Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileGenerationNotification(status: 'success', messages: "Sales File Generated Successfully for the date of {$date} & has been stored to specified disk"));
            }

            $this->comment($message);

            return 0;

        } catch (Exception $e) {
            $message = "An Error Encountered while generating the Sales file - {$e->getMessage()}";

            if (! empty($logChannel)) {
                Log::channel($logChannel)->error($message);
            }

            if (! empty($notificationConfig['email']) && ! empty($date)) {
                Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileGenerationNotification(status: 'error', messages: "Sales File Generation Failed for the date of {$date} - {$e->getMessage()}"));
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
            'notifications.name' => ['nullable'],
            'notifications.email' => ['nullable', 'email'],
            'log_channel_for_file_generation' => ['required'],
        ], [
            'notifications.email.email' => 'Please set valid e-mail in config notifications array',
            'log_channel_for_file_generation.required' => 'Please set the log channel for file generation',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return [
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

    protected function validateAndGetStores(): Collection
    {
        $salesDataService = resolve(IOICityMallSalesDataService::class); // @phpstan-ignore-line

        $storeIdentifier = $this->option('store_identifier');

        $stores = $salesDataService->storesList($storeIdentifier);

        if (! isset($stores)) {
            throw new Exception('The stores array is either missing or empty. Please ensure it has proper values.');
        }
        $input = [
            'stores' => $stores->values()->all(),
            'store_identifier' => $storeIdentifier,
        ];
        $validator = Validator::make($input, [
            'stores' => ['nullable', 'array'],
            'stores.*.store_identifier' => ['required', 'distinct'],
            'stores.*.machine_id' => ['required', 'distinct'],
            'stores.*.sst_registered' => ['required', 'boolean'],
            'store_identifier' => ['nullable', Rule::in(array_column($input['stores'], 'store_identifier'))],
        ], [
            'stores.*.store_identifier.distinct' => 'Duplicate Store identifiers found. Please ensure that each store has a unique store_identifier.',
            'stores.*.store_identifier.required' => 'store_identifier is either missing or empty in one of the items. Please ensure it is properly configured.',
            'stores.*.machine_id.required' => 'Machine ID is either missing or empty in one of the items. Please ensure it is properly configured.',
            'stores.*.machine_id.distinct' => 'Duplicate Machine IDs found. Please ensure that each store has a unique Machine ID.',
            'stores.required' => 'The Stores array cannot be empty.',
            'store_identifier.in' => "No Stores found with the store_identifier {$this->option('store_identifier')}",
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $stores;
    }

    private function validateAndGetConfig(): array
    {
        $validator = Validator::make(config('ioi-city-mall-sales-file'), [
            'disk_to_use' => ['required'],
            'sftp' => ['required'],
            'log_channel_for_file_upload' => ['required'],
            'first_file_generation_date' => ['required', 'date_format:"Y-m-d"'],

        ], [
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
            $salesData = $salesDataService->salesData($store['store_identifier'], $date);

            if (! $salesData instanceof Collection) {
                throw new Exception('A collection must be returned from the handle() method of the class.');
            }

            $salesData = $this->validateAndGetSales($salesData, $date, $config);

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

    protected function validateAndGetSales(Collection $sales, string $date, array $config): Collection
    {
        $afterCurrentDate = Carbon::parse($date)->startOfDay()->toDateTimeString();
        $beforeCurrentDate = Carbon::parse($date)->endOfDay()->toDateTimeString();

        $paymentTypes = PaymentType::values();
        $paymentTypesString = implode(',', $paymentTypes);

        $validations = [
            '*.happened_at' => ['required', 'date', 'date_format:Y-m-d H:i:s', "after_or_equal:{$afterCurrentDate}", "before_or_equal:{$beforeCurrentDate}"],
            '*.net_amount' => ['required', 'numeric', 'decimal:0,2'],
            '*.discount' => ['required', 'decimal:0,2'],
            '*.SST' => ['required', 'decimal:0,2'],
            '*.payments' => ['required', "array:{$paymentTypesString}"],
        ];

        foreach ($paymentTypes as $paymentType) {
            $validations['*.payments.'.$paymentType] = ['required', 'decimal:0,2'];
        }

        $validator = Validator::make($sales->values()->all(), $validations, [
            '*.happened_at.date_equals' => "Sales data :index.happened_at must be the date {$date} only. Sales from other dates are not allowed.",
            '*.happened_at.after_or_equal' => "Sales data :index.happened_at must be the date {$date} only. Sales from other dates are not allowed.",
            '*.happened_at.before_or_equal' => "Sales data :index.happened_at must be the date {$date} only. Sales from other dates are not allowed.",
            '*.payments.array' => "The :attribute must contain only the keys - {$paymentTypesString}.",
        ]);

        $validator->after(function () use ($sales, $config) {
            $sales->each(function ($sale, $index) use ($config) {
                if (isset($sale['payments']) && array_sum($sale['payments']) != $sale['net_amount']) {
                    $sumOfPayments = array_sum($sale['payments']);
                    Log::channel($config['log_channel_for_file_upload'])
                        ->info("The sum of {$index}.payments ({$sumOfPayments}) must be equal to the {$index}.net_amount{$sale['net_amount']}.");
                }
            });
        });

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $sales;

    }
}
