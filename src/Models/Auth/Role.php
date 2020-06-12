<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *      definition="Role",
 *      required={""},
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
 *          property="description",
 *          description="description",
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
 *      )
 * )
 */
class Role extends Model
{
    use SoftDeletes;

    public $table = 'auth_roles';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * for "sqlserver":
     *
     * in unix server
     *      - protected $dateFormat = 'Y-m-d H:i:s';
     * in window server
     *      - protected $dateFormat = 'Y-m-d H:i:s.z';
     *
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $fillable = [
        'name',
        'description',
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
        'name' => 'string',
        'description' => 'string',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'description'];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'auth_roles_has_permissions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function users()
    {
        return $this->belongsToMany(User::class, 'auth_users_has_roles');
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
        logger(__FILE__ . ':' . __LINE__ . ' subMenus ', [$id]);
        return $this->belongsToMany(Permission::class, 'auth_roles_has_permissions')
            ->where('isVisible', 1)
            ->where('auth_permissions.permission_id', $id)
            ->orderBy('orderInMenu')
            ->get();
    }
}
