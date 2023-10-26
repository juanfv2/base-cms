<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

class Permission extends Model
{
    use BaseCmsModel;
    use HasFactory;
    use SoftDeletes;
    use UserResponsible;

    public $table = 'auth_permissions';

    public $fillable = [
        'icon',
        'name',
        'urlBackEnd',
        'urlFrontEnd',
        'isSection',
        'isVisible',
        'orderInMenu',
        'permission_id',
        'createdBy',
        'updatedBy',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'icon' => 'string',
        'name' => 'string',
        'urlBackEnd' => 'string',
        'urlFrontEnd' => 'string',
        'isSection' => 'boolean',
        'isVisible' => 'boolean',
        'orderInMenu' => 'integer',
        'permission_id' => 'integer',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'icon' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'urlBackEnd' => 'required|string|max:255',
        'urlFrontEnd' => 'required|string|max:255',
        'isSection' => 'required|boolean',
        'isVisible' => 'required|boolean',
        'orderInMenu' => 'required|integer',
        'permission_id' => 'required',
        'createdBy' => 'nullable',
        'updatedBy' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
    ];

    public $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Auth\Role::class, 'auth_permission_role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subMenus()
    {
        // logger('Permission.subMenus');
        return $this->hasMany(Permission::class)
            ->where('isSection', 0)
            ->where('isVisible', 1)
            ->orderBy('orderInMenu')
            ->orderBy('name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actions()
    {
        return $this->hasMany(Permission::class)
            ->where('isSection', 0)
            ->where('isVisible', 0)
            ->orderBy('orderInMenu')
            ->orderBy('name');
    }

    public static function userHasPermission(int $user_id, string $urlParent, string $urlChild)
    {
        if ($urlChild === '-.-') {
            return DB::table('auth_users')
                ->join('auth_role_user', 'auth_role_user.user_id', '=', 'auth_users.id')
                ->join('auth_roles', 'auth_role_user.role_id', '=', 'auth_roles.id')
                ->join('auth_permission_role', 'auth_permission_role.role_id', '=', 'auth_roles.id')
                ->join('auth_permissions', 'auth_permission_role.permission_id', '=', 'auth_permissions.id')
                ->where('auth_permissions.urlBackEnd', '=', $urlParent)
                ->where('auth_users.id', '=', $user_id)
                ->whereNull('auth_users.deleted_at')
                ->count();
        }

        return DB::table('auth_users as _u')
            ->join('auth_role_user as _ur', '_ur.user_id', '=', '_u.id')
            ->join('auth_roles as _r', '_ur.role_id', '=', '_r.id')
            ->join('auth_permission_role as _rp', '_rp.role_id', '=', '_r.id')
            ->join('auth_permissions as _p1', '_rp.permission_id', '=', '_p1.id')
            ->join('auth_permission_permission as _pp', '_pp.parent_id', '=', '_p1.id')
            ->join('auth_permissions as _p2', '_pp.child_id', '=', '_p2.id')
            ->where('_p1.urlBackEnd', '=', $urlParent)
            ->where('_p2.urlBackEnd', '=', $urlChild)
            ->where('_u.id', '=', $user_id)
            ->whereNull('_u.deleted_at')
            ->count();
    }

    public static function savePermission($permission)
    {
        return DB::table('auth_permissions')
            ->updateOrInsert(
                ['urlBackEnd' => $permission['urlBackEnd'], 'urlFrontEnd' => $permission['urlFrontEnd']],
                $permission
            );
    }

    public static function savePermission2Role($urlParent, $role_id)
    {
        $permission_id = DB::table('auth_permissions')
            ->where('urlBackEnd', $urlParent)
            ->value('id');

        if ($role_id && $permission_id) {
            return DB::table('auth_permission_role')
                ->updateOrInsert(
                    ['role_id' => $role_id, 'permission_id' => $permission_id],
                    ['role_id' => $role_id, 'permission_id' => $permission_id]
                );
        }

        return false;
    }

    public static function savePermissionParentChild($urlParent, $urlChild)
    {
        $parent_id = DB::table('auth_permissions')
            ->where('urlBackEnd', $urlParent)
            ->value('id');

        $child_id = DB::table('auth_permissions')
            ->where('urlBackEnd', $urlChild)
            ->value('id');

        if ($parent_id && $child_id) {
            return DB::table('auth_permission_permission')
                ->updateOrInsert(
                    ['parent_id' => $parent_id, 'child_id' => $child_id],
                    ['parent_id' => $parent_id, 'child_id' => $child_id]
                );
        }

        return false;
    }
}
