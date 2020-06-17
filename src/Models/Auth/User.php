<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Juanfv2\BaseCms\Models\Account;
use Illuminate\Support\Facades\Route;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Juanfv2\BaseCms\Notifications\ResetPasswordNotification;

/**
 * @SWG\Definition(
 *      definition="User",
 *      required={"name", "email", "password", "disabled"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="password",
 *          description="password",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="disabled",
 *          description="disabled",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="role_id",
 *          description="role_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="uid",
 *          description="uid",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="createdBy",
 *          description="createdBy",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="updatedBy",
 *          description="updatedBy",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="remember_token",
 *          description="remember_token",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="deleted_at",
 *          description="deleted_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    public $table = 'auth_users';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * for "sqlserver":
     *
     * in unix server
     *      - protected $dateFormat = 'Y-m-d H:i:s';
     * in window server
     *      - protected $dateFormat = 'Y-m-d H:i:s.z';
     *
     *
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public $fillable = [
        'name',
        'email',
        'password',
        'disabled',
        'role_id',
        'uid',
        'createdBy',
        'updatedBy',
        'remember_token'
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
        'disabled' => 'boolean',
        'role_id' => 'integer',
        'uid' => 'string',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
        'remember_token' => 'string'
    ];

    // /**
    //  * The attributes that should be hidden for arrays.
    //  *
    //  * @var array
    //  */
    // protected $hidden = [
    //     'password',
    // ];
    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'email', 'disabled', 'uid', 'role_id'];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rulesCreate = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:auth_users',
        'password' => 'required|string|min:6|confirmed', //
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rulesUpdate = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|max:255',
        'password' => 'min:6', //|confirmed
    ];

    /**
     * @return array
     */
    public function getTokenAttribute()
    {
        return $this->remember_token;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function person()
    {
        return $this->hasOne(Person::class, 'email', 'email');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function account()
    {
        return $this->hasOne(Account::class, 'email', 'email');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'auth_users_has_roles');
    }

    public function hasPermission()
    {
        $cRoute = Route::getCurrentRoute()->action['as'];

        // logger(__FILE__ . ':' . __LINE__ . ' $cRoute ', [$cRoute]);

        if ($cRoute == 'api.login.logout') {
            return true;
        }

        if ($cRoute == 'api.login.permissions') {
            return true;
        }

        if (request()->has('cp')) {
            $cRoute = request()->get('cp', '-.-._._.-.-');
        }

        // mysql
        $menu = DB::select('call sp_has_permission (?, ?);', [$this->id, $cRoute]);

        $hasPermission = $menu[0]->aggregate > 0;

        // sqlserver $menu = DB::select('execute sp_has_permission ?, ?;', [$this->id, $cRoute]);

        // logger(__FILE__ . ':' . __LINE__ . ' $this->id ', [' . ' . $this->id . ' . ' . $cRoute . ' . ' . $hasPermission]);

        return $hasPermission;
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
        return $this->hasOne(\Juanfv2\BaseCms\Models\Auth\UserVerified::class);
    }
}
