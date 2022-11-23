<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserVerified
 *
 * @version February 7, 2019, 12:50 am UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection authRolesHasPermissions
 * @property \Illuminate\Database\Eloquent\Collection authUsersHasRoles
 * @property int user_id
 * @property string token
 */
class XUserVerified extends Model
{
    public $table = 'auth_users_verified';

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
    public static $rules = [];

    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class);
    }
}
