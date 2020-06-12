<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;

/**
 * Class UserVerified
 * @package App\Models
 * @version February 7, 2019, 12:50 am UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection authRolesHasPermissions
 * @property \Illuminate\Database\Eloquent\Collection authUsersHasRoles
 * @property integer user_id
 * @property string token
 */
class UserVerified extends Model
{

    public $table = 'auth_users_verified';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'user_id',
        'token',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'token' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function user()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Auth\User::class);
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

    public function verifyUser()
    {
        return $this->hasOne(\Juanfv2\BaseCms\Models\UserVerified::class);
    }

}
