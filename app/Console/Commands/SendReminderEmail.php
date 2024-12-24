<?php

namespace App\Console\Commands;

use CraigPaul\Mail\TemplatedMailable;
use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendReminderEmail extends Command
{
    protected $signature = 'reminder:send';
    protected $description = 'Send reminder emails to users who have not completed their non-negotiables';

    private const TASK_TYPES = ['mood', 'water', 'sleep'];
    private const ALLOWED_EMAILS = ['@southinc.co.nz'];
    private const ACTION_URL = 'http://localhost:3000/dashboard';  // Replace with actual task URL
    private const HELP_URL = 'http://localhost/help';  // Replace with actual help URL

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $timezone = 'Pacific/Auckland';
        $today = Carbon::today();
        $now = Carbon::now($timezone);

        // Retrieve users who haven't completed any of their non-negotiables for today
        $users = $this->getIncompleteUsers($today);

        if ($users->isEmpty()) {
            $this->info("All users have completed their non-negotiables for today.");
        } else {
            $this->info("Users who haven't completed their non-negotiables:");

            foreach ($users as $user) {
                if ($this->isAllowedEmail($user->email)) {
                    $this->line("User ID: {$user->id}, Email: {$user->email}");

                    $statuses = $this->getTaskStatuses($user, $today);

                    // Send reminder email with task statuses
                    $this->sendReminderEmail($user, $statuses);
                }
            }
        }

        $this->info("Check completed at {$now->toTimeString()} NZT.");
    }

    private function getIncompleteUsers(Carbon $today)
    {
        return User::where(function ($query) use ($today) {
            foreach (self::TASK_TYPES as $taskType) {
                $query->orWhereDoesntHave('nonNegotiables', function ($query) use ($taskType, $today) {
                    $query->where('date', $today)
                        ->where('type', $taskType)
                        ->where('completed', true);
                });
            }
        })->get();
    }

    private function isAllowedEmail(string $email): bool
    {
        foreach (self::ALLOWED_EMAILS as $allowedEmail) {
            if (str_contains($email, $allowedEmail)) {
                return true;
            }
        }

        return false;
    }

    private function getTaskStatuses(User $user, Carbon $date): array
    {
        $statuses = [];

        foreach (self::TASK_TYPES as $taskType) {
            $statuses[$taskType] = $this->checkTaskStatus($user, $taskType, $date);
        }

        return $statuses;
    }

    private function checkTaskStatus(User $user, string $taskType, Carbon $date): string
    {
        $task = $user->nonNegotiables()
            ->where('date', $date)
            ->where('type', $taskType)
            ->first();

        return ($task && $task->completed) ? 'Completed' : 'Incomplete';
    }

    private function sendReminderEmail(User $user, array $statuses): void
    {
        $mailable = (new TemplatedMailable())
            ->identifier(37980954)  // Replace with your actual template identifier
            ->include([
                'name' => $user->name,
                'mood_status' => $statuses['mood'],
                'mood_status_color' => $this->getStatusColor($statuses['mood']),
                'water_status' => $statuses['water'],
                'water_status_color' => $this->getStatusColor($statuses['water']),
                'sleep_status' => $statuses['sleep'],
                'sleep_status_color' => $this->getStatusColor($statuses['sleep']),
                'action_url' => self::ACTION_URL,
                'help_url' => self::HELP_URL,
            ]);

        Mail::to($user->email)->send($mailable);

//        Mail::to('brent.agetro@gmail.com')->send($mailable); Used for testing
    }

    private function getStatusColor(string $status): string
    {
        return ($status == 'Completed') ? 'green' : 'red';
    }
}

