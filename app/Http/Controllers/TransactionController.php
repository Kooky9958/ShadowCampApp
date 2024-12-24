<?php

namespace App\Http\Controllers;

class TransactionController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized action.'); // Ensure the user is authenticated
        }

        $account      = $user->account; // Get the account related to the user
        $transactions = $account ? $account->transactions : collect(); // Fetch transactions if the account exists

        // Determine the next upcoming payment date
        $nextSubscriptionProducts = $account->getNextSubscriptionProducts();
        $active_subs              = $account->getActiveSubs();
        $mr_sub_start_date        = isset($active_subs) && !empty($active_subs) ? end($active_subs)['start_date'] : "";

        return view('profile.show', ['transactions' => $transactions, 'mr_sub_start_date' => $mr_sub_start_date]);
    }

}
