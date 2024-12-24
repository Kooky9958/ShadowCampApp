<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Models\Transaction;
use App\Models\KVP;
use App\Models\Account;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send batched emails';
    
    /**
     * Send 'delta_welcome' info pack emails
     * 
     * @return void
     */
    private static function send_delta_welcome_info_pack_emails() {
        // Check when we last ran
        $kvp_last_run = KVP::where('key', 'cron_delta_welcome_send_email_last_run')->first();
        if($kvp_last_run == null) {
            $kvp_last_run = KVP::create([           
                'type' => 'internal_state_tracker',
                'key' => 'cron_delta_welcome_send_email_last_run',
                'value' => 0
            ]);
        }

        // Get the transactions for which an email needs to be sent
        $transactions = Transaction::where('lineitems', 'like', '%a-6%')
                                    ->where('date', '>=', date('Y-m-d H:i:s', $kvp_last_run->value-600))
                                    ->where('payment_success', true)
                                    ->get();

        // Send the emails
        $sent_mail_count = 0;
        foreach($transactions as $transaction) {
            $account = Account::where('id', $transaction->account_id)->first();

            // Check and update communication record on account
            $accomms = json_decode($account->communication, true);
            if(isset($accomms['email_info_pack_delta_welcome']))
                continue;

            $accomms['email_info_pack_delta_welcome'] = time();
            $account->communication = json_encode($accomms);
            $account->save();

            // Send email
            // Mail::to($account->email)->send(new \App\Mail\InfoPack('delta_welcome', 'Welcome to Delta'));

            Mail::to('shadowcamp@southinc.co.nz')->send(new \App\Mail\InfoPack('delta_welcome', 'Welcome to Delta'));
            $sent_mail_count++;
        }

        // Update last run time
        $kvp_last_run->value = time();
        $kvp_last_run->save();

        Log::notice("Sent $sent_mail_count delta_welcome info pack emails");
    }

    /**
     * Send 'delta_migrate' invitation emails
     * 
     * @return void
     */
    private static function send_delta_migrate_invite_emails() {

        // Get the transactions for which an email needs to be sent
        $transactions = Transaction::where('lineitems', 'like', '%a-2%')
                                    ->where('date', '<=', date('Y-m-d H:i:s', strtotime('-49 days')))
                                    ->where('payment_success', true)
                                    ->get();

        // Send the emails
        $sent_mail_count = 0;
        foreach($transactions as $transaction) {
            $account = Account::where('id', $transaction->account_id)->first();

            // Check are we in the correct time window to send this email.
            $ac_gas = $account->getActiveSubs();
            if(isset($ac_gas['camp_precall'])) {
                $precall_start_date = $ac_gas['camp_precall']['start_date'];
                if($precall_start_date + (50*24*60*60) > time()) {
                    continue;
                }
            }

            // Check and update communication record on account
            $accomms = json_decode($account->communication, true);
            if(isset($accomms['email_invite_delta_migrate']))
                continue;

            $accomms['email_invite_delta_migrate'] = time();
            $account->communication = json_encode($accomms);
            $account->save();

            // Send email
            // Mail::to($account->email)->send(new \App\Mail\Invite('delta_migrate', 'Join us in our live community'));

            Mail::to('shadowcamp@southinc.co.nz')->send(new \App\Mail\Invite('delta_migrate', 'Join us in our live community'));
            $sent_mail_count++;
        }

        Log::notice("Sent $sent_mail_count delta_migrate invite emails");
    }

     /**
     * Send 'delta_migrate' renewal emails
     * 
     * @return void
     */
    private static function send_delta_migrate_renewal_emails() {

        // Get the transactions for which an email needs to be sent
        $transactions = Transaction::where('lineitems', 'like', '%a-6%')
                                    ->where('date', '<=', date('Y-m-d H:i:s', strtotime('-50 days')))
                                    ->where('payment_success', true)
                                    ->get();

        // Send the emails
        $sent_mail_count = 0;
        foreach($transactions as $transaction) {
            $account = Account::where('id', $transaction->account_id)->first();

            // Check are we in the correct time window to send this email.
            $ac_gas = $account->getActiveSubs();
            if(isset($ac_gas['camp_delta_migrate'])) {
                $deltamig_start_date = $ac_gas['camp_delta_migrate']['start_date'];
                if($deltamig_start_date + (50*24*60*60) > time()) {
                    continue;
                }
            }

            // Check and update communication record on account
            $accomms = json_decode($account->communication, true);
            if(isset($accomms['email_delta_migrate_renewal_1']))
                continue;

            $accomms['email_delta_migrate_renewal_1'] = time();
            $account->communication = json_encode($accomms);
            $account->save();

            // Send email
            // Mail::to($account->email)->send(new \App\Mail\Invite('delta_migrate_renewal', 'Resubscribe to Delta'));
            Mail::to('shadowcamp@southinc.co.nz')->send(new \App\Mail\Invite('delta_migrate_renewal', 'Resubscribe to Delta'));
            $sent_mail_count++;
        }

        Log::notice("Sent $sent_mail_count delta_migrate_renewal emails");
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        self::send_delta_welcome_info_pack_emails();
        self::send_delta_migrate_invite_emails();
        self::send_delta_migrate_renewal_emails();

        return 0;
    }
}
