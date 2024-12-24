<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function showBillingView() {
        $session_account = Account::getSessionAccount()['account'];

        return view('billing',['transactions' => $session_account->getRecentTransactions()]);
    }

    public function showReferralView() {
        $session_account = Account::getSessionAccount()['account'];

        return view('referral',['referral_code' => $session_account->getReferralCode()]);
    }
}
