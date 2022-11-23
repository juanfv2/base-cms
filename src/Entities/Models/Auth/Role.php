<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class Role
 *
 * @version September 8, 2020, 4:57 pm UTC
 */
class Role extends Model
{
    use SoftDeletes,
        BaseCmsModel,
        HasFactory,
        UserResponsible;

    public $table = 'auth_roles';

    public $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'createdBy' => 'nullable',
        'updatedBy' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
        'permissions' => 'nullable', // <<
    ];

    public $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Auth\Permission::class, 'auth_roles_has_permissions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function users()
    {
        return $this->belongsToMany(\App\Models\Auth\User::class, 'auth_users_has_roles');
    }

    /**
     * @return array
     */
    public function getUrlPermissionsAttribute()
    {
        return $this->belongsToMany(Permission::class, 'auth_roles_has_permissions')
            ->select('urlFrontEnd')
            ->orderBy('urlFrontEnd', 'desc')
            ->get()
            ->pluck('urlFrontEnd');
    }

    /**
     * @return array
     */
    public function getIdsPermissionsAttribute()
    {
        return $this->belongsToMany(Permission::class, 'auth_roles_has_permissions')
            ->select('auth_permissions.id')
            ->orderBy('auth_permissions.id', 'desc')
            ->get()
            ->pluck('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMenusAttribute()
    {
        $menus = $this->belongsToMany(Permission::class, 'auth_roles_has_permissions')
            ->where('isVisible', 1)
            ->where('isSection', 1)
            ->orderBy('orderInMenu')
            ->get();
        foreach ($menus as $menu) {
            $menu->subMenus = $this->inRoleSubMenus($menu->id);
        }

        return $menus;
    }

    /**
     * @param $id
     * @return $this
     */
    public function inRoleSubMenus($id)
    {
        // logger(__FILE__ . ':' . __LINE__ . ' subMenus ', [$id]);
        return $this->belongsToMany(Permission::class, 'auth_roles_has_permissions')
            ->where('isVisible', 1)
            ->where('auth_permissions.permission_id', $id)
            ->orderBy('orderInMenu')
            ->get();
    }
}
