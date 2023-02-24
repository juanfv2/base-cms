<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

class Role extends Model
{
    use BaseCmsModel;
    use UserResponsible;
    use SoftDeletes;
    use HasFactory;

    public $table = 'auth_roles';

    public $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
    ];

    public static $rules = [
        'name' => 'required|string',
        'description' => 'required|string',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
        'permissions' => 'nullable',
    ];

    public $hidden = [
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Auth\Permission::class, 'auth_role_permission');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\Auth\User::class, 'auth_user_role');
    }

    public function getUrlPermissionsAttribute()
    {
        return $this->belongsToMany(Permission::class, 'auth_role_permission')
            ->select('urlFrontEnd')
            ->orderBy('urlFrontEnd', 'desc')
            ->get()
            ->pluck('urlFrontEnd');
    }

    public function getIdsPermissionsAttribute()
    {
        return $this->belongsToMany(Permission::class, 'auth_role_permission')
            ->select('auth_permissions.id')
            ->orderBy('auth_permissions.id', 'desc')
            ->get()
            ->pluck('id');
    }

    public function getMenusAttribute()
    {
        $menus = $this->belongsToMany(Permission::class, 'auth_role_permission')
            ->where('isVisible', 1)
            ->where('isSection', 1)
            ->orderBy('orderInMenu')
            ->get();
        foreach ($menus as $menu) {
            $menu->subMenus = $this->inRoleSubMenus($menu->id);
        }

        return $menus;
    }

    public function inRoleSubMenus($id)
    {
        // logger(__FILE__ . ':' . __LINE__ . ' subMenus ', [$id]);
        return $this->belongsToMany(Permission::class, 'auth_role_permission')
            ->where('isVisible', 1)
            ->where('auth_permissions.permission_id', $id)
            ->orderBy('orderInMenu')
            ->get();
    }
}
