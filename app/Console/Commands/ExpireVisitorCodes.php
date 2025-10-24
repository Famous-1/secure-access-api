<?php

namespace App\Console\Commands;

use App\Models\VisitorCode;
use Illuminate\Console\Command;

class ExpireVisitorCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitor-codes:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired visitor codes as expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCodes = VisitorCode::where('expires_at', '<=', now())
            ->whereIn('status', ['pending', 'active'])
            ->update(['status' => 'expired']);

        $this->info("Marked {$expiredCodes} visitor code(s) as expired.");

        return 0;
    }
}

