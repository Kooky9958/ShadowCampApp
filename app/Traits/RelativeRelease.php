<?php

namespace App\Traits;

use App\Models\Account;

trait RelativeRelease
{
    /**
     * Check should this item be displayed
     * 
     * @return bool True if item should be displayed, false otherwise
     */
    public function shouldDisplay() {
        $return = true;
        $now = new \DateTime('today 23:59', new \DateTimeZone(env('TIMEZONE')));
        
        //// Playlist specific considerations
        if($this instanceof \App\Models\VideoPlaylist || $this instanceof \App\Models\ProductContentResourceList) {
            if(isset($this->is_virtual) && $this->is_virtual == true) {
                return $this->virtual_should_display ?? true;
            }
        }

        //// Universal considerations
        if($this->published != true)
            $return = false;
        else if($this->date_expiry != null && strtotime($this->date_expiry) <= $now->getTimestamp())
            $return = false;
        else if($this->date_release != null && strtotime($this->date_release) > $now->getTimestamp())
            $return = false;
        else if ($this->release_relative_day != null) {
            $return = false;
            $got_session_account = Account::getSessionAccount();
            $audience_ac = $got_session_account['account']->getAudience();

            $this_audience_jdcode = json_decode($this->audience);
            $this_release_relative_day_zeroed = $this->release_relative_day-1;

            foreach($this_audience_jdcode as $audience_my) {
                if(array_key_exists($audience_my, $audience_ac)) { // If the account is in one of the items's audiences
                    if (isset($audience_ac[$audience_my]['start_date'])) { // If the account has a valid start date for the audience
                        $start_date_ac = $audience_ac[$audience_my]['start_date'];

                        if((($start_date_ac+($this_release_relative_day_zeroed*86400))) <= $now->getTimestamp()) { // If the relative release day has passed
                            if ($this->release_relative_persistent == true)
                                $return = true;
                            else if(($start_date_ac+((env('RELATIVE_RELEASE_VALIDITY_DAYS')+$this_release_relative_day_zeroed)*86400)) > $now->getTimestamp())
                                $return = true;
                        }
                    }
                }
            }
        }

        return $return;
    }
}