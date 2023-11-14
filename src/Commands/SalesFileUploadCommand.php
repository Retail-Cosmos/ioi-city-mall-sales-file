<?php

namespace RetailCosmos\IoiCityMallSalesFile\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logChannel = config('ioi-city-mall-sales-file.log_channel_for_file_upload');

        try {
            $config = $this->validateAndGetConfig();

            $disk = $config['disk_to_use'];

            $pendingFiles = Storage::disk($disk)->files('pending_to_upload');

            if (! empty($pendingFiles)) {
                $uploader = new SalesFileUploaderService($config);

                foreach ($pendingFiles as $file) {

                    $this->comment("Uploading File {$file} to SFTP Server");
                    $uploader->uploadFile($file);
                    $this->comment("File {$file} has been uploaded to SFTP Server");

                    $this->comment("Moving file to {$file} uploaded folder");
                    $this->moveFileToUploadedFolder($disk, $file);
                    $this->info("File {$file} uploaded successfully");
                }

                $message = 'Sales files uploaded successfully.';
                Log::channel($logChannel)->info($message);
                $this->comment($message);
            } else {
                $message = 'No sales files found for upload.';
                Log::channel($logChannel)->info($message);
                $this->comment($message);
            }

            return 0;
        } catch (Exception $e) {
            Log::channel($logChannel)->error("An error occurred while uploading sales files: {$e->getMessage()}");
            $this->error($e->getMessage(), 1);

            return 1;
        }
    }

    private function validateAndGetConfig(): array
    {
        $config = config('ioi-city-mall-sales-file');

        if (! isset($config) || empty($config)) {
            throw new Exception('The configuration file is either missing or empty. Please ensure it is properly configured.');
        }

        $validator = Validator::make($config, [
            'disk_to_use' => ['required'],
            'sftp' => ['required'],
            'sftp.ip_address' => ['required', 'ip'],
            'sftp.port' => ['required'],
            'sftp.username' => ['required'],
            'sftp.password' => ['required'],
            'log_channel_for_file_upload' => ['required'],
            'notifications' => ['required'],
        ], [
            'disk_to_use.required' => 'The disk_to_use key in configuration file is not set. Please ensure it is properly configured.',
            'sftp' => 'SFTP Config array is required.',
            'sftp.ip_address' => 'SFTP Config array must have a valid IP Address as host.',
            'sftp.port' => 'SFTP Config array must have a Port Configured Properly.',
            'sftp.username' => 'SFTP Config array must have a valid username.',
            'sftp.password' => 'SFTP Config array must have a valid password.',
            'first_file_generation_date.required' => 'The first_file_generation_date key in configuration file is not set. Please ensure it is properly configured.',
            'first_file_generation_date.date_format' => 'Invalid date format for first_file_generation_date. Please ensure it is properly configured in the "YYYY-MM-DD" format.',
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
