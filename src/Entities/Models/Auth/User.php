<?php

namespace App\Models\Auth;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\BaseCmsModelUser;
use Juanfv2\BaseCms\Traits\HasFile;
use Juanfv2\BaseCms\Traits\UserResponsible;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use BaseCmsModel;
    use BaseCmsModelUser;
    use UserResponsible;
    use HasFile;
    use SoftDeletes;
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    public $table = 'auth_users';

    public $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'disabled',
        'phoneNumber',
        'uid',
        'role_id',
        'country_id',
        'region_id',
        'city_id',
        'api_token',
        'remember_token',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'email_verified_at' => 'datetime',
        'disabled' => 'boolean',
        'phoneNumber' => 'string',
        'uid' => 'string',
        'api_token' => 'string',
        'remember_token' => 'string',
    ];

    public static $rules = [
        'name' => 'required|string',
        'email' => 'required|string|unique:auth_users',
        'password' => 'required|string',
        'email_verified_at' => 'nullable',
        'disabled' => 'required|boolean',
        'phoneNumber' => 'nullable|string',
        'uid' => 'nullable|string',
        'role_id' => 'required',
        'country_id' => 'nullable',
        'region_id' => 'nullable',
        'city_id' => 'nullable',
        'api_token' => 'nullable|string',
        'remember_token' => 'nullable|string',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
        'withEntity' => 'nullable',
        'roles' => 'nullable',
    ];

    public $hidden = [
        'remember_token', 'api_token', 'password',  'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Country\Country::class, 'country_id');
    }

    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Country\Region::class, 'region_id');
    }

    public function city(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Country\City::class, 'city_id');
    }

    public function person(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Auth\Person::class, 'user_id');
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Auth\Account::class, 'user_id');
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\Role::class, 'role_id');
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Auth\Role::class, 'auth_user_role');
    }

    public function photo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOneXFile(__FUNCTION__);
    }

    public function getTokenAttribute()
    {
        return $this->remember_token;
    }

    public function verifyUser()
    {
        event(new \Illuminate\Auth\Events\Registered($this));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($i = $model->photo) {
                $i->delete();
            }
        });
    }
}
