<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\AdminCRUDHelper;
use App\Models\Account;
use App\Models\User;

class ListContextAction extends Component
{
    public $id;

    public $action;

    public $result;

    public $name;

    public function render()
    {
        return view('livewire.list-context-action');
    }

    #[On('show-action')] 
    public function updateModal($id, $action)
    {
        // Update id field
        $this->id = $id;
        $this->action = $action;

        // Init
        $account = Account::where('id', $id)->first();

        $this->name = $account->name;

        // Get the associated user
        $user = User::where('email', $account->email)->first();
        
        if (!$user) {
            $this->result = 'User not found.';
            return;
        }

        // Check user privileges
        if ($user->privileges >= 10000 && $action == 'disable') {
            $this->result = 'Admin accounts cannot be disabled.';
            return;
        }
        
        switch ($action) {
            case 'disable':
                $account->user_id = null;
                $account->save();
                $this->result = 'Account has been disabled';
                break;
            case 'enable':
                $user = User::where('email', $account->email)->first();
                $account->user_id = $user->id;
                $account->save();
                $this->result = 'Account has been enabled';
                break;
            case 'move_to_delta':
                $account->products_subscribed_override = '{"camp_delta_migrate":{"start_date":"'.date('Y-m-d').' 00:00:00"}}';
                $account->save();
                $this->result = 'Account has been moved to Delta';
                break;
            case 'compliment_delta':
                $account->products_subscribed_override = '{"camp_delta_migrate":{"start_date":"'.date('Y-m-d').' 00:00:00"}}';
                $account->save();
                $this->result = 'Account has been given Delta complimentary';
                break;
            case 'compliment_precall':
                $account->products_subscribed_override = '{"camp_precall":{"start_date":"'.date('Y-m-d').' 00:00:00"}}';
                $account->save();
                $this->result = 'Account has been given Precall complimentary';
                break;
            case 'able_sub_delta':
                $account->override_general = '{"next_subscription_product":{"camp_delta_migrate":[]}}';
                $account->save();
                $this->result = 'Account has ability to subscribe to Delta';
                break;
            case 'clear_override':
                $account->override_general = null;
                $account->products_subscribed_override = null;
                $account->save();
                $this->result = 'Manual overrides have been cleared';
                break;
            default:
                break;
        }
    }
}
