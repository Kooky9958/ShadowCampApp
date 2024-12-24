<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class DeleteUnvalidatedUsers extends Command
{
    protected $signature = 'users:delete-unvalidated';
    protected $description = 'Delete users who have not verified their email within 72 hours.';

    public function handle()
    {
        $deleted = User::whereNull('email_verified_at')
            ->where('created_at', '<', Carbon::now()->subHours(72))
            ->delete();

        $this->info("Deleted $deleted unvalidated users.");
    }
}
