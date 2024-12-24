<?php

namespace App\Helpers;

use App\Models\Account;

/**
 * Custom helper class to provide shadow camp specific globaly reuseable code
 */
class CustomHelper
{
    /**
     * Generate a url_id for a resource
     * 
     * @param string $resource_name The name of the resource
     * @return string The url_id for the resource
     */
    public static function generateUrlId($resource_name) {
        return strtolower(substr(hash('sha256',$resource_name.random_bytes(16)), 0, 10));
    }

    /**
     * Determine audience user belongs to
     * 
     * @param string $audience Audience or null
     * @return string Determined audience
     */
    public static function determineAudience($audience) {
        $audience_determined = $audience;

        if ($audience === null) {
            $got_sessionac = Account::getSessionAccount();

            if ($got_sessionac && isset($got_sessionac['account']) && $got_sessionac['account'] instanceof Account) {
                $account = $got_sessionac['account'];

                // Determine the audience based on the user's subscriptions
                if ($account->hasActiveSubTo('camp_precall')) {
                    $audience_determined = 'precall';
                } elseif ($account->hasActiveSubTo('camp_delta_migrate') || $account->hasActiveSubTo('camp_delta9')) {
                    $audience_determined = 'delta';
                }
            }
        }

        return $audience_determined;
    }
}
