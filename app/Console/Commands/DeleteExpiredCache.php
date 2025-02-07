<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteExpiredCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:delete-expired';

    protected $description = 'Delete expired cache items';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('cache')
            ->where('expiration', '<', now()->timestamp)
            ->delete();

        $this->info('Expired cache items deleted.');
    }
}
