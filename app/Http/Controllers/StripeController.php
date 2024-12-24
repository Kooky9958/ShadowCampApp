<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;

class StripeController extends Controller
{
    /**
     * Create a Stripe payment intent
     * 
     * @param Illuminate\Http\Request $request
     * @return string JSON encoded Stripe clientSecret
     */
    public static function create_pay_intent(Request $request) {
        $session_account = Account::getSessionAccount();
        
        switch ($request->input('product')) {
            case 'camp_delta_migrate':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-6', 'description' => 'Delta Camp subscription (migrate from Precall)', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription ('.date('My').' + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta9':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-11', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (FEB24 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta8':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-10', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (DEC23 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta7':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-9', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (OCT23 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta6':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-8', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (AUG23 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta5':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-7', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (JUN23 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta4':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-5', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (MAR23 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta3':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-4', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (JAN23 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;
                
            case 'camp_delta2':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-3', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (DEC22 + 8 weeks)';
                break;

            case 'camp_delta':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-1', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (OCT22 + 8 weeks)';
                break;

            case 'camp_precall':
                $amount = 140.00;
                $lineitems = json_encode([['product_id' => 'a-2', 'description' => 'Precall Camp subscription', 'amount' => $amount, 'currency' => 'nzd']]);
                $transaction_description = 'Precall Camp subscription';
                $reference = 'PRECALL-CAMP';
                break;
            
            default:
                $amount = 0;
                break;
        }

        //// Create payment intent
        if($request->input('payment_method_type') == 'afterpay_clearpay') {
            $paymentIntent = $session_account['account']->payWith($amount*100, ['afterpay_clearpay'], ['shipping' => [
                'name' => $session_account['account']->name,
                'address' => [
                'line1' => '999 Waterloo Quay',
                'city' => 'Wellington',
                'country' => 'NZ',
                'postal_code' => '6011',
            ]]]);
        } else if($request->input('payment_method_type') == 'card') {
            $paymentIntent = $session_account['account']->payWith($amount*100, ['card']);
        } else {
            $paymentIntent = $session_account['account']->pay($amount*100, ['shipping' => [
                'name' => $session_account['account']->name,
                'address' => [
                  'line1' => '999 Waterloo Quay',
                  'city' => 'Wellington',
                  'country' => 'NZ',
                  'postal_code' => '6011',
            ]]]);    
        }

        $jsonObj = [
            'clientSecret' => $paymentIntent->client_secret,
        ];

        // Create Transaction
        $transaction = Transaction::create([
            'date' => date('Y-m-d H:i:s'),
            'payment_method' => null,
            'payment_provider' => 'stripe',
            'payment_provider_data' => null,
            'description' => $transaction_description
        ]);
        $transaction->amount = $amount;
        $transaction->currency = 'nzd';
        $transaction->payment_provider_id = $paymentIntent->id;
        $transaction->account_id = $session_account['account']->id;
        $transaction->lineitems = $lineitems;
        $transaction->save();

        return json_encode($jsonObj);
    }
}
