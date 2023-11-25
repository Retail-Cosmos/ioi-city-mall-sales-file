<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RetailCosmos\IoiCityMallSalesFile\Notifications\SalesFileUploadNotification;
use RetailCosmos\IoiCityMallSalesFile\Services\SalesFileUploaderService;

class SalesFileUploadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:ioi-city-mall-sales-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload generated Sales files for IOI City Mall';

    public SalesFileUploaderService $salesFileUploaderService;

    public function __construct(SalesFileUploaderService $salesFileUploaderService)
    {
        parent::__construct();

        $this->salesFileUploaderService = $salesFileUploaderService;

    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('ioi-city-mall-sales-file.enable_file_upload')) {
            return 1;
        }

        try {
            [$notificationConfig, $logChannel] = $this->validateCommunicationChannels();

            $config = $this->validateAndGetConfig();

            $disk = $config['disk_to_use'];

            $pendingFiles = Storage::disk($disk)->files('pending_to_upload');

            if (! empty($pendingFiles)) {

                foreach ($pendingFiles as $file) {

                    $this->comment("Uploading File {$file} to SFTP Server");
                    $this->salesFileUploaderService->uploadFile($config, $file);
                    $this->comment("File {$file} has been uploaded to SFTP Server");

                    $this->comment("Moving file to {$file} uploaded folder");
                    $this->moveFileToUploadedFolder($disk, $file);
                    $this->info("File {$file} uploaded successfully");
                }

                $message = 'Sales files uploaded successfully.';

                Log::channel($logChannel)->info($message);

                Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileUploadNotification(status: 'success', messages: 'Sales File Uploaded Successfully to the SFTP Server'));

                $this->comment($message);
            } else {
                $message = 'No sales files found for upload.';

                Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileUploadNotification(status: 'info', messages: $message));

                Log::channel($logChannel)->info($message);

                $this->comment($message);
            }

            return 0;
        } catch (Exception $e) {
            $message = "An error occurred while uploading sales files: {$e->getMessage()}";

            if (! empty($logChannel)) {
                Log::channel($logChannel)->error($message);
            }

            if (! empty($notificationConfig)) {
                Notification::route('mail', $notificationConfig['email'])->notify(new SalesFileUploadNotification(status: 'error', messages: $message));
            }

            $this->error($message, 1);

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
            'log_channel_for_file_upload' => ['required'],
        ], [
            'notifications.name.required' => 'Please set name in config notifications array',
            'notifications.email.required' => 'Please set e-mail in config notifications array',
            'notifications.email.email' => 'Please set valid e-mail in config notifications array',
            'log_channel_for_file_upload.required' => 'Please set the log channel for file upload',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return [
            ...array_values($validator->validated()),
        ];

    }

    private function validateAndGetConfig(): array
    {
        $validator = Validator::make(config('ioi-city-mall-sales-file'), [
            'disk_to_use' => ['required'],
            'sftp' => ['required'],
            'sftp.ip_address' => ['required', 'ip'],
            'sftp.port' => ['required'],
            'sftp.username' => ['required'],
            'sftp.password' => ['required'],
            'sftp.path' => ['required'],
            'notifications' => [],
            'log_channel_for_file_upload' => [],
            'enable_file_upload' => [],
        ], [
            'disk_to_use.required' => 'The disk_to_use key in configuration file is not set. Please ensure it is properly configured.',
            'sftp' => 'SFTP Config array is required.',
            'sftp.ip_address' => 'SFTP Config array must have a valid IP Address as host.',
            'sftp.port' => 'SFTP Config array must have a Port Configured Properly.',
            'sftp.username' => 'SFTP Config array must have a valid username.',
            'sftp.password' => 'SFTP Config array must have a valid password.',
            'sftp.path' => 'SFTP Config array must have a valid file(s) upload path.',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        return $validator->validated();
    }

    private function moveFileToUploadedFolder(string $disk, string $filePath): void
    {
        $uploadedPath = str_replace('pending_to_upload', 'uploaded', $filePath);
        Storage::disk($disk)->move($filePath, $uploadedPath);
    }
}
