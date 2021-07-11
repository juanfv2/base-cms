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
 * @package App\Models
 * @version September 8, 2020, 4:57 pm UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection $authRoles
 * @property string $name
 * @property string $email
 * @property string $password
 * @property boolean $disabled
 * @property string $uid
 * @property string $api_token
 * @property integer $role_id
 * @property integer $createdBy
 * @property integer $updatedBy
 * @property string $remember_token
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
        'userCanDownload',
        'phoneNumber',
        'role_id',
        'company_id',
        'group_id',
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
        'userCanDownload' => 'boolean',
        'phoneNumber' => 'string',
        'role_id' => 'integer',
        'company_id' => 'integer',
        'group_id' => 'integer',
        'remember_token' => 'string',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',

        'logs'      => 'integer',
        'viewed_at' => 'datetime',
        'search' => 'json',
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
        'userCanDownload'   => 'required|boolean',
        'phoneNumber'       => 'nullable|string|max:191',
        'role_id'           => 'required',
        'company_id'        => 'required',
        'group_id'          => 'required',
        'remember_token'    => 'nullable',
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
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function group()
    {
        return $this->belongsTo(\App\Models\Group::class, 'group_id');
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

    public function getVersionsAttribute()
    {
        $varname1 = "";
        $varname1 .= "SELECT v.* ";
        $varname1 .= "FROM visor_list_file_version_logs l ";
        $varname1 .= "join visor_list_file_versions v on l.visor_list_file_version_id = v.id ";
        $varname1 .= "where l.visor_list_searching_log_id = ?";
        $t1 = DB::select($varname1, [$this->visor_list_searching_log_id]);
        return $t1;
    }

    /**
     * @return array
     */
    public function getViewedsAttribute()
    {
        $min = session('min', '-');
        $max = session('max', '-');

        $varname1 = "";
        $varname1 .= "SELECT ";
        $varname1 .= " COUNT(`v`.`container_id`) `views`, ";
        $varname1 .= " `c`.`name` ";
        $varname1 .= "FROM ";
        $varname1 .= "`visor_list_file_version_logs` `l` ";
        $varname1 .= "JOIN ";
        $varname1 .= "`visor_list_file_versions` `v` ON `v`.`id` = `l`.`visor_list_file_version_id` ";
        $varname1 .= "JOIN ";
        $varname1 .= "`containers` `c` ON `c`.`id` = `v`.`container_id` ";
        $varname1 .= "WHERE ";
        $varname1 .= "`user_id` = ? ";

        if ($min != '-') {
            $varname1 .= "and `l`.`viewed_at` > '{$min}' ";
        }
        if ($max != '-') {
            $varname1 .= "and `l`.`viewed_at` <= '{$max}' ";
        }
        $varname1 .= "GROUP BY `c`.`name` ";
        $varname1 .= "ORDER BY `views` DESC";
        $t1 = DB::select($varname1, [$this->id]);
        return $t1;
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
        // logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
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
