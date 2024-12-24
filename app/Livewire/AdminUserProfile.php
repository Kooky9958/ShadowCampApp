<?php

namespace App\Livewire;

use App\Models\ProfileQuestion;
use App\Models\User;
use App\Models\Region;
use Livewire\Component;

class AdminUserProfile extends Component
{
    public $user;
    public $transactions;

    public $name         = null;
    public $email        = null;
    public $gender       = null;
    public $age          = null;
    public $height       = null;
    public $weight       = null;
    public $address_line1 = null;
    public $country      = null;
    public $region       = null;
    public $city         = null;
    public $postcode     = null;
    public $photo        = null;
    public $profileQuestions     = null;
    public $state = [
        'country' => null,
        'region' => null,
    ];

    public function mount($userId)
    {
        $this->user = User::find($userId);
        
        // Check if the user exists
        if (!$this->user) {
            abort(404);
        }

         // Retrieve the user's current country and region
        $selectedCountry = $this->user->country;
        $selectedRegion = $this->user->region;

        // Retrieve the profile questions for the user
        $profileQuestions = ProfileQuestion::where('user_id', $this->user->id)->first();

        // Initialize profileQuestions and decode the JSON strings into arrays
        $this->profileQuestions = $profileQuestions ? $profileQuestions : new \stdClass();
        
        // Decode properties safely
        $this->profileQuestions->goals = json_decode($profileQuestions->goals ?? '[]', true) ?? [];
        $this->profileQuestions->mental_health_issues = json_decode($profileQuestions->mental_health_issues ?? '[]', true) ?? [];
        
        // Initialize other properties if they don't exist
        $this->profileQuestions->hair_loss = $profileQuestions->hair_loss ?? null;
        $this->profileQuestions->birth_control = $profileQuestions->birth_control ?? null;
        $this->profileQuestions->reproductive_disorder = $profileQuestions->reproductive_disorder ?? null;
        $this->profileQuestions->weight_change = $profileQuestions->weight_change ?? null;
        $this->profileQuestions->coffee_consumption = $profileQuestions->coffee_consumption ?? null;

        $account = $this->user->account; // Get the account related to the user
        $this->transactions = $account ? $account->transactions : collect(); // Fetch transactions if the account exists

        $selectedRegion = Region::where('name', $selectedRegion)->pluck('id')->first();

        // Populate other user properties
        $this->name         = $this->user->name;
        $this->email        = $this->user->email;
        $this->gender       = $this->user->gender;
        $this->age          = $this->user->age;
        $this->height       = $this->user->height;
        $this->weight       = $this->user->weight;
        $this->address_line1 = $this->user->address_line1;
        $this->country      = $this->user->country;
        $this->region       = $this->user->region;
        $this->city         = $this->user->city;
        $this->postcode     = $this->user->postcode;
        $this->photo        = $this->user->profile_photo_path;
        // Set the selected country and region
        $this->state['country'] = $selectedCountry;
        $this->state['region'] = $selectedRegion;
    }

    public function updateProfile()
    {
        // Call the update method with the user ID
        $this->emit('updateUserProfile', $this->user->id);
    }

    public function render()
    {
        return view('livewire.admin-user-profile')
            ->layout('layouts.app');
    }
}
