<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AdminCRUDReports
{
    /**
     * Generate "Delta Lapsed Customers Report": All customers which have paid for a Delta Migrate or Delta OG subscription where their most recent payment was 9 weeks ago or more.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function lapsed_delta() 
    {
        // Init
        $return = [];
        $date_9weeksago = date('Y-m-d', strtotime('-9 weeks'));

        $db_query = DB::table('accounts')
        ->select('accounts.id', 'name', 'email', 'ig_user', 'transactions.date as Last Payment Date', 'transactions.description as Last Payment Description', 'transactions.id as Last Payment ID')
        ->join('transactions', 'account_id', '=', 'accounts.id')
        ->where(function ($query) {
            $query->Where('transactions.lineitems', 'like', '%a-6%')
                ->orWhere('transactions.lineitems', 'like', '%a-10%');
        })
        ->where('transactions.date', '<=', $date_9weeksago)
        ->where('payment_success', 1)
        ->whereNotIn('accounts.id', function ($query) use ($date_9weeksago) {
            $query->select('accounts.id')
                ->from('accounts')
                ->join('transactions', 'account_id', '=', 'accounts.id')
                ->where('transactions.date', '>', $date_9weeksago)
                ->where('payment_success', 1);
        })
        ->orderBy('transactions.id', 'desc')
        ->paginate(50);

        $return['db_query'] = $db_query;

        return $return;
    }

    /**
     * Generate "Precall Lost Customers Report": All customers which have paid for a Precall subscription where they have failed to signup for Delta.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function lost_precall() 
    {
        // Init
        $return = [];
        $date_9weeksago = date('Y-m-d', strtotime('-9 weeks'));

        $db_query = DB::table('accounts')
        ->select('accounts.id', 'name', 'email', 'ig_user', 'transactions.date as Last Payment Date', 'transactions.description as Last Payment Description', 'transactions.id as Last Payment ID')
        ->join('transactions', 'account_id', '=', 'accounts.id')
        ->where('transactions.lineitems', 'like', '%a-2%')
        ->where('transactions.date', '<=', $date_9weeksago)
        ->where('payment_success', 1)
        ->whereNotIn('accounts.id', function ($query) use ($date_9weeksago) {
            $query->select('accounts.id')
                ->from('accounts')
                ->join('transactions', 'account_id', '=', 'accounts.id')
                ->where(function ($query) {
                    $query->Where('transactions.lineitems', 'like', '%a-6%')
                        ->orWhere('transactions.lineitems', 'like', '%a-11%');
                })
                ->where('payment_success', 1);
        })
        ->orderBy('transactions.id', 'desc')
        ->paginate(50);

        $return['db_query'] = $db_query;

        return $return;
    }

    /**
     * Generate  "New Delta Migrate Customers": All customers which have moved from Precall to Delta Migrate in the past 9 weeks. (This report will include their identification photo)
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function new_delta_migrate() 
    {
        // Init
        $return = [];

        // Define raw HTML fields
        $return['raw_html_fields'] = ['identity_verification'];

        //// Fetch transactions
        $subquery0 = DB::table('transactions')
        ->select('id', DB::raw('MAX(date) as max_date'), 'account_id')
        ->where('payment_success', 1)
        ->where('lineitems', 'like', '%a-6%')
        ->groupBy('id', 'account_id')
        ->havingRaw('COUNT(*) = 1');

        $db_query = DB::table('accounts')
        ->joinSub($subquery0, 'transactions', function ($join) {
            $join->on('accounts.id', '=', 'transactions.account_id');
        })
        ->select('transactions.id', 'transactions.max_date', 'accounts.name', 'accounts.email', 'accounts.ig_user', 'accounts.identity_verification')
        ->orderBy('transactions.max_date', 'desc')
        ->paginate(50);
    
        // Substitute in links for identity_verification images
        foreach ($db_query as $item) {
            $identity_verification_jdcode = json_decode($item->identity_verification, true);
            
            if($identity_verification_jdcode != null && isset($identity_verification_jdcode['uploaded_identity_file']))
                $item->identity_verification = '<a href="/admin/download/'.str_replace('/', '~~', $identity_verification_jdcode['uploaded_identity_file']).'" class="underline">View ID Verification</a>';
        }

        $return['db_query'] = $db_query;

        return $return;
    }

    /**
     * Generate  "Current Delta Customers":  All customers which have paid for a Delta Migrate or Delta OG subscription in the past 9 weeks
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function current_delta() 
    {
        // Init
        $return = [];
        $date_9weeksago = date('Y-m-d', strtotime('-9 weeks'));

        // Fetch accounts
        $db_query = DB::table('accounts')
        ->select('accounts.id', 'name', 'email', 'ig_user', 'transactions.date as Last Payment Date', 'transactions.description as Last Payment Description', 'transactions.id as Last Payment ID')
        ->join('transactions', 'account_id', '=', 'accounts.id')
        ->where(function ($query) {
            $query->where('transactions.lineitems', 'like', '%a-6%')
                ->orWhere('transactions.lineitems', 'like', '%a-11%');
        })
        ->where('transactions.date', '>', $date_9weeksago)
        ->where('payment_success', 1)
        ->orderBy('transactions.id', 'desc')
        ->paginate(50);

        $return['db_query'] = $db_query;

        return $return;
    }

    /**
     * Generate  Current Precall Customers":  All customers which have paid for a Delta Migrate or Delta OG subscription in the past 9 weeks
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function current_precall() 
    {
        // Init
        $return = [];
        $date_9weeksago = date('Y-m-d', strtotime('-9 weeks'));

        $db_query = DB::table('accounts')
        ->select('accounts.id', 'name', 'email', 'ig_user', 'transactions.date as Last Payment Date', 'transactions.description as Last Payment Description', 'transactions.id as Last Payment ID')
        ->join('transactions', 'account_id', '=', 'accounts.id')
        ->where('transactions.lineitems', 'like', '%a-2%')
        ->where('transactions.date', '>', $date_9weeksago)
        ->where('payment_success', 1)
        ->orderBy('transactions.id', 'desc')
        ->paginate(50);

        $return['db_query'] = $db_query;

        return $return;
    }
}
