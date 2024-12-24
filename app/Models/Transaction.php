<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model implements \App\Interfaces\AdminCRUD, \App\Interfaces\AdminCRUDSearchable
{
    use HasFactory;

    /**
     * The attributes that are NOT mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [
        'amount',
        'currency',
        'lineitems',
        'payment_success',
        'payment_provider_id',
        'account_id'
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'amount' => 0,
        'currency' => 'nzd',
        'lineitems' => "{}",
        'payment_success' => false,
        'payment_provider_id' => null,
        'account_id' => 0
    ];

    /**
     * Get the account associated with the transaction.
     */
    public function account() :BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the human readable name of the implementing model
     * 
     * @return string human readable name of the implementing model
     */
    public static function getName() :string {
        return "Transaction";
    }

    /**
     * Get the human readable plural name of the implementing model
     * 
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string {
        return "Transactions";
    }

    /**
     * Get the list of attributes which should not be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which should not be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACHiddenAttributes() :array {
        return [];
    }

    /**
     * Get the list of attributes which MUST be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which MUST be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACForceDisplayAttributes() :array {
        return ['amount','currency','lineitems','payment_success','payment_provider_id'];
    }

    /**
     * Get the list of belongs-to relations which should be displayed by the AdminCRUD extension
     * 
     * @return array list of field which contain belongs-to relations  and should be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACDisplayBelongsToRelations() :array {
        return [
            'account'
        ];
    }

    /**
     * Get an ORM query which searches for the given search query string
     * 
     * @param string $search_query_string The search query string to search for
     * @return Illuminate\Database\Eloquent\Builder The ORM query to execute
     */
    public static function getSearchORMQuery(string $search_query_string) {
        $search_query_abstracted = '%'.preg_replace('/[^a-zA-Z0-9@]/', '%', trim($search_query_string)).'%';

        return self::where('date', 'like', $search_query_abstracted)
        ->orWhere('amount', 'like', $search_query_abstracted)
        ->orWhere('description', 'like', $search_query_abstracted)
        ->orWhere('payment_provider', 'like', $search_query_abstracted)
        ->orWhere('payment_provider_id', 'like', $search_query_abstracted)
        ->orWhere('lineitems', 'like', $search_query_abstracted)
        ->orWhere('id', 'like', $search_query_abstracted)
        ->orWhereIn('account_id', function ($query) use ($search_query_abstracted) {
            $query->select('accounts.id')
            ->from('accounts')
            ->where('email', 'like', $search_query_abstracted)
            ->orWhere('name', 'like', $search_query_abstracted);
        })
        ->orderBy('created_at', 'desc');
    }


    /**
     * Check is a referral code valid and claimable
     * 
     * @return boolean True if valid and claimable, false otherwise
     */
    public static function isClaimableReferralCode($referral_code) {
        // Init
        $return = false;
        $kvp = KVP::where('key', 'like', strtoupper(trim($referral_code)))
                        ->where('type', '=', 'promotion_code_referral')->first();

        // Check is the customer new
        $session_account = Account::getSessionAccount();
        if(self::where('account_id', '=', $session_account['account']->id)->where('payment_success', true)->count() > 0)
            abort(400, 'Ineligible customer.');

        // Check code exists
        if($kvp != null) {
            $kvp_v_jdcode = json_decode($kvp->value, true);
            
            // Check code is valid
            if($kvp_v_jdcode['active'] == true && strtotime($kvp_v_jdcode['date_expiry']) > time()) {
                // Enforce claim limit
                if($kvp_v_jdcode['claim_count'] < $kvp_v_jdcode['claim_limit']) {
                    $return = true;
                }
            }
        }

        return $return;
    }

    /**
     * Claim a referral code
     * 
     * @return boolean True on successful claim, false otherwise
     */
    public static function claimReferralCode($referral_code) {
        //Init
        $session_account = Account::getSessionAccount();

        if(self::isClaimableReferralCode($referral_code)) {
            $kvp = KVP::where('key', 'like', strtoupper(trim($referral_code)))
                        ->where('type', '=', 'promotion_code_referral')->first();
            $kvp_v_jdcode = json_decode($kvp->value, true);
            $kvp_v_jdcode['claim_count']++;
            $kvp->value = json_encode($kvp_v_jdcode);

            $transaction = Transaction::create([
                'date' => date('Y-m-d H:i:s'),
                'payment_method' => 'code_promo',
                'description' => 'Precall Camp subscription',
            ]);
            $transaction->payment_success = true;
            $transaction->account_id = $session_account['account']->id;
            $transaction->lineitems = json_encode([['product_id' => 'a-2', 'description' => 'Precall Camp subscription', 'amount' => 0, 'currency' => 'nzd']]);;

            $transaction->save();

            $kvp_v_jdcode['claim_transactions'][] = $transaction->id;
            $kvp->value = json_encode($kvp_v_jdcode);
            $kvp->save();

            return true;
        }
        else
            return false;
    }
}
