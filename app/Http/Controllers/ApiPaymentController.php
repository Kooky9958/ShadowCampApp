<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;

class ApiPaymentController extends Controller
{
    public static function initiate_transaction(Request $request)
    {
        $requestData = $request->all();
        $pay_amount = $requestData['amount'];
        $account_id = Account::where('user_id', $requestData['user_id'])->value('id');

        switch ($request->input('product')) {
            case 'camp_delta_migrate':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-6', 'description' => 'Delta Camp subscription (migrate from Precall)', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (' . date('My') . ' +8 weeks)';
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
                $reference = "DELTA-CAMP";
                break;

            case 'camp_delta':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-1', 'description' => 'Delta Camp subscription', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (OCT22 + 8 weeks)';
                $reference = "DELTA-CAMP";
                break;

            case 'camp_precall':
                $amount = $pay_amount;
                $lineitems = json_encode([['product_id' => 'a-2', 'description' => 'Precall Camp subscription', 'amount' => $amount, 'currency' => 'nzd']]);
                $transaction_description = 'Precall Camp subscription';
                $reference = "PRECALL-CAMP";
                break;

            default:
                $amount = 0;
                $reference = "";
                break;
        }

        $json_builder = '{
        "Amount":"' . $amount . '",
        "CurrencyCode":"NZD",
        "MerchantReference":"' . $reference . '",
        "MerchantHomepageURL":"https://shadowcamp.southinc.co.nz",
        "SuccessURL":"' . env('APP_URL') . 'poli/ack_success",
        "FailureURL":"' . env('APP_URL') . 'poli/ack_fail",
        "CancellationURL":"' . env('APP_URL') . 'poli/ack_cancel",
        "NotificationURL":"' . env('APP_URL') . 'poli/webhook/notice" 
    }';

        $auth = base64_encode(env('POLI_MERCHANTCODE') . ':' . env('POLI_AUTHCODE'));
        $header = array();
        $header[] = 'Content-Type: application/json';
        $header[] = 'Authorization: Basic ' . $auth;

        $ch = curl_init("https://poliapi.apac.paywithpoli.com/api/v2/Transaction/Initiate");
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_builder);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        //Create Transaction
        $response_ar = json_decode($response, true);
        $transaction = Transaction::create([
            'date' => date('Y-m-d H:i:s'),
            'payment_method' => null,
            'payment_provider' => 'poli',
            'payment_provider_data' => null,
            'description' => $transaction_description
        ]);
        $transaction->amount = $amount;
        $transaction->currency = 'nzd';
        $transaction->payment_provider_id = $response_ar['TransactionRefNo'];
        $transaction->account_id =  $account_id;
        $transaction->lineitems = $lineitems;
        $transaction->save();

        return $response;
    }


    
}
