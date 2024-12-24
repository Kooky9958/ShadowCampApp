<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Account;
use App\Models\KVP;
use App\Models\Transaction;

class SubscribeCampPrecallController extends Controller
{
    /**
     * Show Precall Camp subscribe from
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $tmp_precall_full = true; // DISABLED limited spaces: !(DB::scalar("select COUNT(*) from transactions where payment_success = 1 and lineitems like '%\"product_id\":\"a-2\"%'") > 50);
        
        return view('subscribe_camp_precall', array_merge(Account::getSessionAccount(), ['spaces_available' => $tmp_precall_full]));
    }

    /**
     * Show Delta Camp (_migrate) subscribe from
     *
     * @return \Illuminate\View\View
     */
    public function show_delta_migrate()
    {
        return view('subscribe_camp_delta_migrate', Account::getSessionAccount());
    }

    public function subscribe(Request $request) {
        if(Transaction::isClaimableReferralCode($request->input('discount_code'))) {
            if(Transaction::claimReferralCode($request->input('discount_code')))
                echo "SUCCESS";
            else
                abort('400', 'Code claim failed');
        }
        else
            abort('400', 'Invalid discount code');
    }
}
