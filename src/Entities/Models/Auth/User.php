<?php

namespace App\Models\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
// use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class User
 *
 * @package App\Models
 * @version September 8, 2020, 4:57 pm UTC
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, Notifiable, SoftDeletes, HasFactory, UserResponsible;

    public $table = 'auth_users';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



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

        'api_token' => 'string',
        'remember_token' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name'              => 'required|string|max:191',
        'email'             => 'required|string|max:191|unique:auth_users',
        'password'          => 'required|string|max:191',
        'email_verified_at' => 'nullable',
        'disabled'          => 'required|boolean',
        'phoneNumber'       => 'nullable|string|max:191',
        'uid'               => 'nullable|string|max:191',

        'role_id'           => 'required',
        'country_id'        => 'nullable',
        'region_id'         => 'nullable',
        'city_id'           => 'nullable',

        'api_token'         => 'nullable|string',
        'remember_token'    => 'nullable|string|max:191',
        'createdBy'         => 'nullable',
        'updatedBy'         => 'nullable',
        'created_at'        => 'nullable',
        'updated_at'        => 'nullable',
        'deleted_at'        => 'nullable',

        'withEntity'        => 'nullable', // <<<
        'roles'             => 'nullable',
    ];

    public $hidden = [
        'remember_token', 'api_token', 'password', 'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at'
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
        return $this->hasOne(\App\Models\Auth\XUserVerified::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return array
     */
    public function getTokenAttribute()
    {
        return $this->api_token;
    }

    /**
     * @return \App\Models\Auth\XFile
     */
    public function getPhotoUrlAttribute()
    {
        $f =  \App\Models\Auth\XFile::where('entity', $this->table)
            ->where('field', 'photoUrl')
            ->where('entity_id', $this->id)
            ->first();
        logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
        return $f;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($i = $model->photoUrl) {
                $i->delete();
            }
        });
    }
}
