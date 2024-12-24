<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Laravel\Cashier\Billable;

use \App\Models\KVP;
use DateTime;

class Account extends Model implements \App\Interfaces\AdminCRUD, \App\Interfaces\AdminCRUDSearchable
{
    use HasFactory;
    use Billable;

    /**
     * The attributes that are NOT mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [
        'products',
        'audience',
        'user_id',
        'products_subscribed',
        'products_subscribed_override',
        'identity_verification',
        'override_general'
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'products' => null,
        'audience' => null,
        'questionnaire' => null,
        'industry_occupation' => null,
        'declared_healthy' => false,
        'accepted_tc' => false,
        'user_id' => null,
        'products_subscribed' => "{}",
        'products_subscribed_override' => "{}",
        'identity_verification' => null,
        'override_general' => "{}"
    ];

    public function __toString() {
        return "{$this->name}, {$this->email}";
    }

    /**
     * Get the human readable name of the implementing model
     * 
     * @return string human readable name of the implementing model
     */
    public static function getName() :string {
        return "Account";
    }

    /**
     * Get the human readable plural name of the implementing model
     * 
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural() :string {
        return "Accounts";
    }

    /**
     * Get the list of attributes which should not be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which should not be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACHiddenAttributes() :array {
        return ['stripe_id','pm_type','pm_last_four','trial_ends_at'];
    }

    /**
     * Get the list of attributes which MUST be displayed by the AdminCRUD extension
     * 
     * @return array list of attributes which MUST be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACForceDisplayAttributes() :array {
        return ['user_id'];
    }

    /**
     * Get the list of belongs-to relations which should be displayed by the AdminCRUD extension
     * 
     * @return array list of field which contain belongs-to relations  and should be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACDisplayBelongsToRelations() :array {
        return [];
    }

    /**
     * Get an ORM query which searches for the given search query string
     * 
     * @param string $search_query_string The search query string to search for
     * @return Illuminate\Database\Eloquent\Builder The ORM query to execute
     */
    public static function getSearchORMQuery(string $search_query_string) {
        $search_query_abstracted = '%'.preg_replace('/[^a-zA-Z0-9@]/', '%', trim($search_query_string)).'%';

        return self::where('email', 'like', $search_query_abstracted)
        ->orWhere('name', 'like', $search_query_abstracted)
        ->orWhere('ig_user', 'like', $search_query_abstracted)
        ->orWhere('id', 'like', $search_query_abstracted)
        ->orderBy('created_at', 'desc');
    }

    /**
     * Fetches Shadow Camp account associated with the user.
     * 
     * @param int user_id The database primary key of the user account
     * @return App/Models/Account
     */
    public static function haveUserGetAccount($user_id) {
        return Account::where('user_id', $user_id)->get()->pop();
    }

    /**
     * Get the Shadow Camp Account and User currently logged in
     * 
     * @return array 'account' => Account::haveUserGetAccount(), 'auth_user' => Auth::user()
     */
    public static function getSessionAccount()
    {
        $auth_user = Auth::user();

        if($auth_user == null || !isset($auth_user->id))
            return null;

        return ['account' => Account::haveUserGetAccount($auth_user->id), 'auth_user' => $auth_user];
    }

    /**
     * Get all active subscriptions for the account
     * 
     * @return mixed associative array containing active subscription(s) and associated meta data for this account, or empty array if none.
     */
    public function getActiveSubs($pastXdays = 0) {
        
        //Init
        $return = [];
        $products_subscribed_override_jdcode = null;

        // Check the override list
        if ($this->products_subscribed_override != null) {
            $products_subscribed_override_jdcode = json_decode($this->products_subscribed_override, true);
            $return = array_merge($return, $products_subscribed_override_jdcode);
        }

        // Check live products
        if($this->hasActiveSubTo('camp_delta4'))
            $return['camp_delta4'] = [];

        if($this->hasActiveSubTo('camp_delta6'))
            $return['camp_delta6'] = [];

        if($this->hasActiveSubTo('camp_delta7'))
            $return['camp_delta7'] = [];

        if($this->hasActiveSubTo('camp_delta8'))
            $return['camp_delta8'] = [];

        if($this->hasActiveSubTo('camp_delta9'))
            $return['camp_delta9'] = [];

        //// Check Precall
        $transactions = Transaction::where('account_id', $this->id)
        ->where('payment_success', true)
        ->where('lineitems','like','%product_id":"a-2%')
        ->get();

        foreach($transactions as $transaction) {
            if(
                $transaction != null 
                || 
                (
                    $this->products_subscribed_override != null 
                    && isset($products_subscribed_override_jdcode) 
                    && array_key_exists('camp_precall', $products_subscribed_override_jdcode)
                )
            ) {
                if(is_array($products_subscribed_override_jdcode) && array_key_exists('camp_precall', $products_subscribed_override_jdcode))
                    $start_date = strtotime($products_subscribed_override_jdcode['camp_precall']['start_date'].' '.env('TIMEZONE'));
                else if($transaction != null)
                    $start_date = strtotime($transaction->date.' '.env('TIMEZONE'));

                $start_date = ($start_date < strtotime('2023-01-23 06:00:00 '.env('TIMEZONE'))) ? strtotime('2023-01-23 06:00:00 '.env('TIMEZONE')) : $start_date ;



                if($start_date+((56+$pastXdays)*24*60*60) >= time())
                    $return['camp_precall'] = ['start_date' => $start_date];
                else
                    unset($return['camp_precall']);
            }
        }

        if(isset($return['camp_precall']) && !is_numeric($return['camp_precall']['start_date']))
            $return['camp_precall']['start_date'] = strtotime($return['camp_precall']['start_date']);

        //// Check Delta Migrate
        $transactions = Transaction::where('account_id', $this->id)
        ->where('payment_success', true)
        ->where('lineitems','like','%product_id":"a-6%')
        ->get();

        foreach($transactions as $transaction) {
            if(
                $transaction != null 
                || 
                (
                    $this->products_subscribed_override != null 
                    && isset($products_subscribed_override_jdcode) 
                    && array_key_exists('camp_delta_migrate', $products_subscribed_override_jdcode)
                )
            ) {
                if(is_array($products_subscribed_override_jdcode) && array_key_exists('camp_delta_migrate', $products_subscribed_override_jdcode))
                    $start_date = strtotime($products_subscribed_override_jdcode['camp_delta_migrate']['start_date'].' '.env('TIMEZONE'));
                else if($transaction != null)
                    $start_date = strtotime($transaction->date.' '.env('TIMEZONE'));

                $start_date = ($start_date < strtotime('2023-01-23 06:00:00 '.env('TIMEZONE'))) ? strtotime('2023-01-23 06:00:00 '.env('TIMEZONE')) : $start_date ;



                if($start_date+((63+$pastXdays)*24*60*60) >= time())
                    $return['camp_delta_migrate'] = ['start_date' => $start_date];
                else
                    unset($return['camp_delta_migrate']);
            }
        }

        if(isset($return['camp_delta_migrate'])) {
            if(!is_numeric($return['camp_delta_migrate']['start_date']))
                $return['camp_delta_migrate']['start_date'] = strtotime($return['camp_delta_migrate']['start_date']);

                $trans_dm_count = Transaction::where('account_id', $this->id)
                ->where('payment_success', true)
                ->where('lineitems','like','%product_id":"a-6%')
                ->count();

                $return['camp_delta_migrate']['sub_num'] = $trans_dm_count;
        }

        return $return;
    }

    /**
     * Check if the acount has an active subscription to specified product
     * 
     * @param string product The product to check for an active subscription
     * @return boolean
     */
    public function hasActiveSubTo($product) {
        // Special cases
        if($product == 'camp_precall') {
            $active_subs = $this->getActiveSubs();
            return isset($active_subs['camp_precall']);
        }
        else if($product == 'camp_delta_migrate') {
            $active_subs = $this->getActiveSubs();
            return isset($active_subs['camp_delta_migrate']);
        }

        // Check the override list
        $products_subscribed_override_jdcode = json_decode($this->products_subscribed_override, true);
        if(is_array($products_subscribed_override_jdcode) && array_key_exists($product, $products_subscribed_override_jdcode))
            return true;


        switch ($product) {

            case 'camp_delta9':
                $product_id = 'a-11';
                break;

            case 'camp_delta8':
                $product_id = 'a-10';
                break;

            case 'camp_delta7':
                $product_id = 'a-9';
                break;

            case 'camp_delta6':
                $product_id = 'a-8';
                break;

            case 'camp_delta5':
                $product_id = 'a-7';
                break;

            case 'camp_delta4':
                $product_id = 'a-5';
                break;

            case 'camp_delta3':
                $product_id = 'a-4';
                break;

            case 'camp_delta2':
                $product_id = 'a-3';
                break;

            case 'camp_delta':
                $product_id = 'a-1';
                break;
            
            default:
            $product_id = 'THISWILLNEVERMATCHASUB';
                break;
        }
        
        // Check is there a transaction
        $transaction = Transaction::where('account_id', $this->id)
                                    ->where('payment_success', true)
                                    ->where('lineitems','like','%product_id":"'.$product_id.'%')
                                    ->get()->pop();
        if($transaction != null)
            return true;
            
        return false;
    }

    /**
     * Get first name of person associated with the account
     * 
     * @return string
     */
    public function firstName() {
        return preg_split("/[\s]+/", $this->name)[0];
    }

    /**
     * Get the audience(s) which this account is a member of
     * 
     * @return mixed array of audience(s) which this account is a member of, or empty array if none
     */
    public function getAudience() {
        // Init
        $return = [];
        $products_subscribed_override_jdcode = json_decode($this->products_subscribed_override, true);

        if($this->hasActiveSubTo('camp_precall')) {
            $active_subs = $this->getActiveSubs();
            $return['precall'] = ['start_date' => $active_subs['camp_precall']['start_date']];
        }

        else if($this->hasActiveSubTo('camp_delta_migrate') || $this->hasActiveSubTo('camp_delta9')) {
            $return['delta'] = [];
        }

        return $return;
    }

    /**
     * Get the next subscription products which this account can purchase
     * 
     * @return mixed array of subscription products which this account can purchase next, or empty array if none
     */
    public function getNextSubscriptionProducts() {
        // Init
        $return = [];

        // Check overrides
        $override_general_jdcode = json_decode($this->override_general, true);
        $next_subscription_product = (is_array($override_general_jdcode['next_subscription_product'] ?? null)) ? $override_general_jdcode['next_subscription_product'] : [];

        // Check existing Delta customers
        $active_subs = $this->getActiveSubs(10);
        if($this->hasActiveSubTo('camp_delta9') || array_key_exists('camp_delta9', $next_subscription_product))
            $return[] = 'camp_delta_resubscribe';
        else if(array_key_exists('camp_delta_migrate', $next_subscription_product))
            $return[] = 'camp_delta_migrate';
        else if(
                isset($active_subs['camp_delta_migrate'])
                && $active_subs['camp_delta_migrate']['start_date']+(7*7*24*60*60) <= time()
                && $active_subs['camp_delta_migrate']['start_date']+(70*24*60*60) >= time()
            ) {
                $return[] = 'camp_delta_migrate';
        }    
        else if(isset($active_subs['camp_precall'])) {
            if(
                isset($active_subs['camp_precall'])
                && $active_subs['camp_precall']['start_date']+(7*7*24*60*60) <= time()
                && $active_subs['camp_precall']['start_date']+(63*24*60*60) >= time()
            )
                $return[] = 'camp_delta_migrate';
        }
        else
            $return[] = 'camp_precall';

        return $return;

    }

    /**
     * Get the most recent successful transactions for this account
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getRecentTransactions() {
        return Transaction::where('account_id', $this->id)
        ->where('payment_success', true)
        ->orderBy('date', 'desc')
        ->get();
    }

    /**
     * Get a referral code for this account
     * 
     * @return mixed The referral code for this account, or null if none
     */
    public function getReferralCode() {
        // Init
        $return = null;

        //// Check is this account ineligible for codes
        $transactions_recent = $this->getRecentTransactions();

        if ($transactions_recent->isNotEmpty()) {
            // Check if the first transaction exists and its payment method
            $firstTransaction = $transactions_recent->first();
    
            if ($firstTransaction && $firstTransaction->payment_method == 'code_promo') {
                return null;
            }
        }
        // if($transactions_recent->first()->payment_method == 'code_promo')
        //     return null;

        //// Check for existing code
        $query_check = KVP::where('value', 'like', '%account_id":'.$this->id.'%')
                            ->where('type', '=', 'promotion_code_referral');

        // If there are existing codes, process them and return the valid one
        if($query_check->count() > 0) {
            $claims = 0;
            
            foreach($query_check->get() as $kvp) {
                $kvp_v_jdcode = json_decode($kvp->value, true);
                
                // Check code is valid
                if($kvp_v_jdcode['active'] == true && strtotime($kvp_v_jdcode['date_expiry']) > time()) {
                    // Enforce claim limit
                    if(
                        $kvp_v_jdcode['claim_count'] < $kvp_v_jdcode['claim_limit']
                    ) {
                        $return = $kvp->key;
                    }

                    // Enforce exclusive claims
                    if($kvp_v_jdcode['claim_count'] > 0 && $kvp_v_jdcode['claim_is_exclusive']) {
                        if($return == $kvp->key) {
                            return $return;
                        }
                        else
                            return null;
                    }
                }
            }
        }

        //// Create a new code if no other exists
        if($return == null) {
            $kvp = new KVP;
            $kvp->type = 'promotion_code_referral';
            $kvp->key = strtoupper(substr(hash('sha256',microtime().random_bytes(16)), 0, 10));
            $kvp->value = json_encode([
                            'account_id' => $this->id, 
                            'date_created' => date('Y-m-d H:i:s'),
                            'date_expiry' => date('Y-m-d H:i:s', strtotime('+6 months')), 
                            'active' => true,
                            'claim_count' => 0,
                            'claim_limit' => 1,
                            'claim_is_exclusive' => true,
                            'claim_transactions' => []
                        ]);
            $kvp->save();

            $return = $kvp->key;
        }

        return $return;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}