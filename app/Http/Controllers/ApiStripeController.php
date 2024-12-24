<?php

namespace App\Http\Controllers;
use App\models\Account;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Transaction;
use Illuminate\Http\Request;
class ApiStripeController extends Controller
{
    //    // Function use for stripe payment gateway integration
    public static function create_pay_intent(Request $request)
    {

        // using the stripe credentials for authentication
        // Stripe::setApiKey(env('STRIPE_SECRET'));

        Stripe::setApiKey(env('STRIPE_SECRET'));

       
        //getting the data from frontend
        $requestData = $request->all();
        // Getting the payment amount from request payload
        $pay_amount = $requestData['amount'];
        // $userId = $requestData['user_id'];
        $account_id = Account::where('user_id', $requestData['user_id'])->value('id');
        // in future if any extra information is required we can use this variable. but it is not important
        $account = Account::where('user_id', $requestData['user_id'])->firstOrFail();

        //switch case starts
        switch ($request->input('product')) {
            case 'camp_delta_migrate':
                $amount = 160.00;
                $lineitems = json_encode([['product_id' => 'a-6', 'description' => 'Delta Camp subscription (migrate from Precall)', 'amount' => 160.00, 'currency' => 'nzd']]);
                $transaction_description = 'Delta Camp subscription (' . date('My') . ' + 8 weeks)';
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
                $amount = $pay_amount;
                $lineitems = json_encode([['product_id' => 'a-2', 'description' => 'Precall Camp subscription', 'amount' => $amount, 'currency' => 'nzd']]);
                $transaction_description = 'Precall Camp subscription';
                $reference = 'PRECALL-CAMP';
                break;

            default:
                $amount = 0;
                break;
        }
        // Switch case end

        // here we can integrate the afterpay payment integration for now i have added the
        if ($request->input('payment_method_type') == 'afterpay_clearpay') {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Stripe expects amount in cents which is required by Stripe (since Stripe expects the smallest currency unit).
                'currency' => 'nzd',
                'automatic_payment_methods' => ['enabled' => true],
                // In the Documentation of stripe it support after pay For any other payment methods, you could add additional cases in the future.
                // below line is hardcoded but need to be done in dynamic way
                'shipping' => [
                    'name' => $account->name,
                    'address' => [
                        'line1' => '999 Waterloo Quay',
                        'city' => 'Wellington',
                        'country' => 'NZ',
                        'postal_code' => '6011',
                    ]
                ]
            ]);
        } else if ($request->input('payment_method_type') == 'card') {
            // Creating 
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100,
                'currency' => 'nzd',
                'description'=> $transaction_description ,
                'shipping' => [
                    'name' => $account->name,
                    'address' => [
                        'line1' => '999 Waterloo Quay',
                        'city' => 'Wellington',
                        'country' => 'NZ',
                        'postal_code' => '6011',
                    ]
                    ],
                'automatic_payment_methods' => ['enabled' => true],
            ]);
        }



        // Create Transaction
        // Create transaction record in database
        $transaction = Transaction::create([
            'date' => now(),
            'payment_method' => $request->input('payment_method_type'),
            'payment_provider' => 'stripe',
            'payment_provider_data' => json_encode($paymentIntent),
            'description' => $transaction_description,
            'amount' => $amount,
            'currency' => 'nzd',
            'payment_provider_id' => $paymentIntent->id,
            'account_id' => $account->id,
            'lineitems' => $lineitems,
        ]);
        $transaction->amount = $amount;
        $transaction->currency = 'nzd';
        $transaction->payment_provider_id = $paymentIntent->id;
        $transaction->account_id = $account_id;
        $transaction->lineitems = $lineitems;
        //saving the values in database
        $transaction->save();

        // // need to be modify according to the response comming from stripe response 
        $jsonObj = [
            'clientSecret' => $paymentIntent->client_secret,
            'payment_intent'=>$paymentIntent,
        ];
   

        return json_encode($jsonObj);
    }
}
