<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
'ig_user' => ['required', 'string', 'max:255', 'unique:accounts'],
            'fb_user' => ['required', 'string', 'max:255', 'unique:accounts'],
            'referrals_code' => ['nullable', 'string', 'max:255'],
        ],
        [],
        [
            'ig_user' => 'Instagram Username',
            'fb_user' => 'Facebook Username', 
            'referrals_code' => 'Referrals Code',
        ])->validate();

        $user_create = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        //// Create the Shadow Camp account
        if(isset($input['migac'])) {
            $account_create = Account::find($input['migac']);
        }
        else {
            $account_create = Account::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'ig_user' => $input['ig_user'],
                'fb_user' => $input['fb_user'],
                'referrals_code' => $input['referrals_code'],
                'begining' => date('Y-m-d H:i:s'),
            ]);
        }

        // Update the account with the user id
        $account_create->setAttribute('user_id', $user_create->getKey());
        $account_create->save();

        // Send confirmation email
        // $user_create->sendEmailVerificationNotification();
        
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', 
            Carbon::now()->addMinutes(60), 
            ['id' => $user_create->id, 'hash' => sha1($user_create->email)]
        );

        Mail::to($input['email'])->send(new \App\Mail\NewUser($verificationUrl));

        return $user_create;
    }
}
