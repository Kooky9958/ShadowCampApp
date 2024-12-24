<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TerraUserProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'terra_user_id',
        'provider',
        'scopes',
        'last_webhook_update',
        'active',
        'widget_session_id',
    ];

    protected $casts = [
        'last_webhook_update' => 'datetime',
        'active'              => 'boolean',
    ];

    function activities(): HasMany
    {
        return $this->hasMany(TerraActivity::class, 'terra_user_provider_id');
    }

    function bodyRecords(): HasMany
    {
        return $this->hasMany(TerraBody::class, 'terra_user_provider_id');
    }

    function dailyRecords(): HasMany
    {
        return $this->hasMany(TerraDaily::class, 'terra_user_provider_id');
    }

    function menstruations(): HasMany
    {
        return $this->hasMany(TerraMenstruation::class, 'terra_user_provider_id');
    }

    function nutritions(): HasMany
    {
        return $this->hasMany(TerraNutrition::class, 'terra_user_provider_id');
    }

    function sleeps(): HasMany
    {
        return $this->hasMany(TerraSleep::class, 'terra_user_provider_id');
    }
}
