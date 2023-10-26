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
    use HasFactory;
    use SoftDeletes;
    use UserResponsible;

    private ?\Illuminate\Database\Eloquent\Collection $menus = null;

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
        return $this->belongsToMany(\App\Models\Auth\Permission::class, 'auth_permission_role');
    }

    public function users()
    {
        return $this->belongsToMany(\App\Models\Auth\User::class, 'auth_role_user');
    }

    public function getMenusAttribute()
    {
        return $this->menusFromParent(0);
    }

    public function menusFromParent($permission_id)
    {
        $this->menus = $this->belongsToMany(Permission::class, 'auth_permission_role')->get();

        /**
         * Primero "menus" y luego cualquiera de estas dos propiedades.
         *   includes = ['menus', 'idsPermissions']
         */
        $this->idsPermissions = $this->menus->pluck('id')->sort()->toArray();
        $this->urlPermissions = $this->menus->pluck('urlFrontEnd')->sort()->toArray();
        $this->_urlBackEnd_ = implode(',', $this->menus->pluck('urlBackEnd')->sort()->toArray());

        $menus = $this->menus
            ->where('isVisible', 1)
            ->where('isSection', 1)
            ->where('permission_id', $permission_id)
            ->sortBy('orderInMenu');

        foreach ($menus as $menu) {
            $menu->subMenus = $this->subMenusFromPermission($menu->id);
        }

        return $menus;
    }

    public function subMenusFromPermission($id)
    {
        // logger(__FILE__ . ':' . __LINE__ . ' subMenus ', [$menus, $menu]);

        $subMenus = $this->menus
            ->where('isVisible', 1)
            ->where('permission_id', $id)
            ->sortBy('orderInMenu');

        foreach ($subMenus as $menu) {
            $menu->subMenus = $this->subMenusFromPermission($menu->id);
        }

        return $subMenus->toArray();
    }
}
