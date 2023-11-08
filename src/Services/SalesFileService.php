<?php

namespace RetailCosmos\IoiCityMallSalesFile\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SalesFileService
{
    public function generate(array $config, array $store, string $date, collection $salesData): string
    {

        $groupedSales = $this->groupSalesByHour($salesData);

        $date = Carbon::parse($date);

        $fileContent = $this->generateFileContent($config, $store, $groupedSales, $date);

        return $this->storeFile($config, $store, $date, $fileContent);
    }

    private function groupSalesByHour(collection $salesData): Collection
    {
        $allHours = array_fill_keys(range(0, 23), []);

        $groupedSales = $salesData->groupBy(function ($sale) {
            return Carbon::parse($sale['happened_at'])->format('H');
        });

        $mergedData = array_replace($allHours, $groupedSales->toArray());

        return collect($mergedData);
    }

    private function aggregateHourlySales(Collection $sales, string $hour, string $date)
    {
        $hourlySales = [
            'date' => date('dmY', strtotime($date)),
            'hour' => str_pad($hour, 2, '0', STR_PAD_LEFT),
            'receipt_count' => $sales->count(),
            'net_amount' => $sales->sum('net_amount'),
            'SST' => $sales->sum('SST'),
            'discount' => $sales->sum('discount'),
        ];

        $paymentTypes = [
            'cash', 'tng', 'visa', 'mastercard', 'amex', 'voucher', 'others',
        ];

        foreach ($paymentTypes as $paymentType) {
            $hourlySales['payments'][$paymentType] = $sales->sum("payments.$paymentType");
        }

        return $hourlySales;
    }

    private function generateFileContent(array $config, array $store, Collection $groupedSales, Carbon $date): string
    {
        $fileContent = '';

        $batchId = $date->diffInDays(Carbon::parse($config['first_file_generation_date']));

        $groupedSales->each(function ($sales, $hour) use (&$fileContent, $date, $store, $batchId) {

            $hourlySales = $this->aggregateHourlySales(collect($sales), $hour, $date);

            $fileContent .= $this->arrayToString($store, $hourlySales, $batchId)."\n";
        });

        return $fileContent;
    }

    private function arrayToString(array $store, array $hourlySales, string $batchId): string
    {
        $separator = '|';
        $fileContent = $store['machine_id'].$separator;
        $fileContent .= $batchId.$separator;
        $fileContent .= $hourlySales['date'].$separator;
        $fileContent .= $hourlySales['hour'].$separator;
        $fileContent .= $hourlySales['receipt_count'].$separator;
        $fileContent .= number_format($hourlySales['net_amount'], 2, '.', '').$separator;
        $fileContent .= number_format($hourlySales['SST'], 2, '.', '').$separator;
        $fileContent .= number_format($hourlySales['discount'], 2, '.', '').$separator;
        $fileContent .= '0'.$separator; // Service Charge
        $fileContent .= '0'.$separator; // Pax Count

        foreach ($hourlySales['payments'] as $paymentType) {
            $fileContent .= number_format($paymentType, 2, '.', '').$separator;
        }

        $fileContent .= ($store['sst_registered'] ? 'Y' : 'N');

        return $fileContent;
    }

    private function storeFile(array $config, array $store, Carbon $date, string $fileContent): string
    {
        $fileName = 'H'.$store['machine_id'].'_'.$date->format('Ymd').'.txt';

        $fileContent = mb_convert_encoding($fileContent, 'UTF-8');

        Storage::disk($config['disk_to_use'])->put('pending_to_upload/'.$fileName, $fileContent);

        Log::channel($config['log_channel_for_file_generation'])->info("Sales File {$fileName} for the date {$date} has been created & stored to pending_to_uploads folder");

        return $fileName;

    }
}
