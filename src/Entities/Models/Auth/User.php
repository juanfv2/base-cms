<?php

namespace App\Models\Auth;

use App\Notifications\ResetPasswordNotification;
use App\Traits\BaseCmsModelUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\HasFile;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class User
 *
 * @version September 8, 2020, 4:57 pm UTC
 */
class User extends Authenticatable
{
    use BaseCmsModel,
        BaseCmsModelUser,
        UserResponsible,
        HasFile,
        SoftDeletes,
        HasFactory,
        Notifiable;

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
        'fcm_token',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'email_verified_at' => 'datetime',
        'disabled' => 'boolean',
        'phoneNumber' => 'string',
        'uid' => 'string',

        'role_id' => 'integer',
        'country_id' => 'integer',
        'region_id' => 'integer',
        'city_id' => 'integer',

        'fcm_token' => 'string',
        'api_token' => 'string',
        'remember_token' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:191',
        'email' => 'required|string|max:191|unique:auth_users',
        'password' => 'required|string|max:191',
        'email_verified_at' => 'nullable',
        'disabled' => 'required|boolean',
        'phoneNumber' => 'nullable|string|max:191',
        'uid' => 'nullable|string|max:191',

        'role_id' => 'required',
        'country_id' => 'nullable',
        'region_id' => 'nullable',
        'city_id' => 'nullable',

        'fcm_token' => 'nullable|string',
        'api_token' => 'nullable|string',
        'remember_token' => 'nullable|string|max:191',
        'createdBy' => 'nullable',
        'updatedBy' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',

        'withEntity' => 'nullable', // <<<
        'roles' => 'nullable',
    ];

    public $hidden = [
        'remember_token', 'api_token', 'password', 'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function country()
    {
        return $this->belongsTo(\App\Models\Country\Country::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function region()
    {
        return $this->belongsTo(\App\Models\Country\Region::class, 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function city()
    {
        return $this->belongsTo(\App\Models\Country\City::class, 'city_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function person()
    {
        return $this->hasOne(\App\Models\Auth\Person::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function account()
    {
        return $this->hasOne(\App\Models\Auth\Account::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function role()
    {
        return $this->belongsTo(\App\Models\Auth\Role::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Auth\Role::class, 'auth_users_has_roles');
    }

    public function verifyUser()
    {
        return $this->hasOne(\App\Models\Misc\XUserVerified::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return array
     */
    public function getTokenAttribute()
    {
        return $this->api_token;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     **/
    public function photo()
    {
        return $this->hasOneXFile(__FUNCTION__);
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
