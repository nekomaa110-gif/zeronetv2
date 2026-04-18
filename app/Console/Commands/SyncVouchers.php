<?php

namespace App\Console\Commands;

use App\Services\VoucherService;
use Illuminate\Console\Command;

class SyncVouchers extends Command
{
    protected $signature   = 'vouchers:sync';
    protected $description = 'Aktifkan voucher baru dari radpostauth dan tandai yang sudah expired';

    public function handle(VoucherService $service): int
    {
        $result = $service->syncFromRadius();

        if ($result['activated'] > 0 || $result['expired'] > 0) {
            $this->info("Diaktifkan: {$result['activated']}, Expired: {$result['expired']}");
        }

        return Command::SUCCESS;
    }
}
