<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Account;

use App\Models\Transaction;

class StripeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle received Stripe webhooks.
     *
     * @param  \Laravel\Cashier\Events\WebhookReceived  $event
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        // Handle successful resolution of payment intent
        if($event->payload['type'] == 'payment_intent.succeeded') {
            // Init
            $paymentIntent = $event->payload['data']['object'];
            $paymentIntent_charge = $paymentIntent['charges']['data']['0'];

            // Fetch associated Transaction
            $transaction = Transaction::where('payment_provider_id', $paymentIntent['id'])->get()->pop();
            if($transaction == null)
                throw new \Exception("No Transaction matched the payment intent id provided by Stripe via webhook");

            $account = Account::find($transaction->account_id);

            // If Stripe payment matches amount and currency update the Transaction
            if(
                $paymentIntent_charge['amount_captured'] == ($transaction->amount*100) 
                && $paymentIntent_charge['currency'] == $transaction->currency
            ) {
                $transaction->payment_method = $paymentIntent_charge['payment_method_details']['type'];
                $transaction->payment_success = true;
                $transaction->payment_provider_data = json_encode($paymentIntent);
                $transaction->save();
            }

            Log::notice("Received successful payment notification for Transaction {$transaction->id} (via Stripe webhook: payment_intent.succeeded)");

            // Send confirmation email
            $email_product = (stripos($transaction->lineitems, 'a-2') !== false) ? 'precall' : 'delta' ;
            $email_product = (stripos($transaction->lineitems, 'a-6') !== false) ? 'delta_migrate' : $email_product ;
            // Mail::to($account->email)->send(new \App\Mail\PaymentConfirmed($email_product));
            Mail::to('shadowcamp@southinc.co.nz')->send(new \App\Mail\PaymentConfirmed($email_product));
        }
    }
}
