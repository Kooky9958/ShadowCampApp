<?php 
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class ApiCreateUserController extends Controller
{
    /**
     * Create a new user account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_user(Request $request)
    {
        // Validate the request data
        $validatedData = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
                'ig_user' => ['required', 'string', 'max:255', 'unique:accounts'],
                'fb_user' => ['required', 'string', 'max:255', 'unique:accounts'],
            ],
            [],
            [
                'ig_user' => 'Instagram Username',
                'fb_user' => 'Facebook Username',
            ]
        )->validate();

        // Create the user
        $user_create = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Create or find the account
        if ($request->has('migac')) {
            $account_create = Account::find($request->input('migac'));
        } else {
            $account_create = Account::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'ig_user' => $validatedData['ig_user'],
                'fb_user' => $validatedData['fb_user'],
                'begining' => now(),
            ]);
        }

        // Associate the account with the user
        $account_create->user_id = $user_create->id;
        $account_create->save();

        // Send confirmation email
        $emailSent = false;
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user_create->id, 'hash' => sha1($user_create->email)]
        );

        try {
            Mail::to($validatedData['email'])->send(new \App\Mail\NewUser($verificationUrl));
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::error('Error sending email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'User created successfully.',
            'user' => $user_create,
            'is_email_sent' => $emailSent,
        ], 201);
    }
}
