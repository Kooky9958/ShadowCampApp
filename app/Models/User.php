<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements \App\Interfaces\AdminCRUD, \App\Interfaces\AdminCRUDSearchable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'age',
        'height',
        'weight',
        'address_line1',
        'city',
        'region',
        'country',
        'postcode',
        'hobbies',
    ];

    /**
     * The attributes that are NOT mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [
        'privileges'
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'privileges' => null
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'hobbies' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the human readable name  of the implementing model
     *
     * @return string human readable name of the implementing model
     */
    public static function getName(): string
    {
        return "User";
    }

    /**
     * Get the human readable plural name of the implementing model
     *
     * @return string human readable plural name  of the implementing model
     */
    public static function getNamePlural(): string
    {
        return "Users";
    }

    /**
     * Get the list of attributes which should not be displayed by the AdminCRUD extension
     *
     * @return array list of attributes which should not be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACHiddenAttributes(): array
    {
        return ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token', 'two_factor_confirmed_at', 'current_team_id', 'profile_photo_path', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
    }

    /**
     * Get the list of attributes which MUST be displayed by the AdminCRUD extension
     *
     * @return array list of attributes which MUST be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACForceDisplayAttributes(): array
    {
        return [];
    }

    /**
     * Get the list of belongs-to relations which should be displayed by the AdminCRUD extension
     *
     * @return array list of field which contain belongs-to relations  and should be displayed by the AdminCRUD extension; Empty array for none
     */
    public static function getACDisplayBelongsToRelations(): array
    {
        return [];
    }

    /**
     * Get an ORM query which searches for the given search query string
     *
     * @param string $search_query_string The search query string to search for
     * @return Illuminate\Database\Eloquent\Builder The ORM query to execute
     */
    public static function getSearchORMQuery(string $search_query_string)
    {
        $search_query_abstracted = '%' . preg_replace('/[^a-zA-Z0-9@]/', '%', trim($search_query_string)) . '%';

        return self::where('email', 'like', $search_query_abstracted)
            ->orWhere('name', 'like', $search_query_abstracted)
            ->orWhere('id', 'like', $search_query_abstracted)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check does the authenticated user have admin privileges
     *
     * @return boolean true if the user has admin privileges, false otherwise
     */
    public static function isAdmin()
    {
        $auth_user = \Auth::user();

        if ($auth_user != null && $auth_user->privileges >= 10000)
            return true;

        return false;
    }

    public static function firstOrCreate(array $array, array $array1)
    {
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public static function getAllHobbies()
    {
        $hobbies = User::pluck('hobbies');

        $hobbiesArray = $hobbies->filter(function ($hobby) {
            return !empty($hobby);
        })->flatMap(function ($hobbies) {
            return json_decode($hobbies, true);
        });

        $uniqueHobbies = $hobbiesArray->unique()->filter(function ($hobby) {
            return !empty($hobby);
        });

        return $uniqueHobbies->values();
    }

    public static function getAllCountries()
    {
        return Country::pluck('name');
    }

    public function getAllRegions($countryId = null)
    {
        if (is_null($countryId)) {
            return [];
        }

        return Region::where('country_id', $countryId)
            ->get(['id', 'name'])
            ->mapWithKeys(function ($region) {
                return [$region->id => $region->name];
            });
    }

    public function profileQuestions()
    {
        return $this->hasMany(ProfileQuestion::class);
    }

    public function terraProviders(): HasMany
    {
        return $this->hasMany(TerraUserProvider::class, 'user_id');
    }

    public function nonNegotiables(): HasMany
    {
        return $this->hasMany(NonNegotiable::class);
    }
}
