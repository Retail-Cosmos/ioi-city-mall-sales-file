<?php

namespace RetailCosmos\IoiCityMallSalesFile\Services;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;

class SalesFileUploaderService
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function uploadFile(string $filePath): void
    {
        $sftpConfig = $this->config['sftp'];

        $filesystem = new Filesystem(new SftpAdapter(
            new SftpConnectionProvider(
                host: $sftpConfig['ip_address'],
                username: $sftpConfig['username'],
                password: $sftpConfig['password'],
                port: $sftpConfig['port'],
            ),
            $sftpConfig['path'],
        ));

        $remoteFilePath = basename($filePath);

        $fileContent = Storage::disk($this->config['disk_to_use'])->get($filePath);

        $filesystem->write($remoteFilePath, $fileContent);
    }
}
