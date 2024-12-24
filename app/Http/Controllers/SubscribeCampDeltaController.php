<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Account;

class SubscribeCampDeltaController extends Controller
{
    /**
     * Show Delta Camp subscribe from
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('subscribe_camp_delta', Account::getSessionAccount());
    }

    public function subscribe_migrate(Request $request) {
        // Init
        $session = Account::getSessionAccount();
        $id_jdcode = json_decode($session['account']->identity_verification, true);
        if($id_jdcode != null)
            return;

        $request->validate([
            'identity_file' => 'image|max:32678'
        ]);

        // Store files
        $store_coverimage_file = $request->file('identity_file')->store('identity_files');

        $session = Account::getSessionAccount();
        $session['account']->identity_verification = json_encode(['isVerified' => false, 'uploaded_identity_file' => $store_coverimage_file]);
        $session['account']->save();
    }
}
