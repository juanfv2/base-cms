<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *      definition="Permission",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="icon",
 *          description="icon",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="urlBackEnd",
 *          description="urlBackEnd",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="urlFrontEnd",
 *          description="urlFrontEnd",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="section",
 *          description="section",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="show2user",
 *          description="show2user",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="permission_id",
 *          description="permission_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="orderInMenu",
 *          description="orderInMenu",
 *          type="integer",
 *          format="int32"
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
class Permission extends Model
{
    use SoftDeletes;

    public $table = 'auth_permissions';

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
        'icon',
        'name',
        'urlBackEnd',
        'urlFrontEnd',
        'section',
        'show2user',
        'permission_id',
        'orderInMenu',
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
        'section' => 'boolean',
        'show2user' => 'boolean',
        'permission_id' => 'integer',
        'orderInMenu' => 'integer',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
    ];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = ['id', 'icon', 'name', 'urlFrontEnd'];

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
    public function permissionRoles()
    {
        return $this->belongsToMany(Role::class, 'auth_roles_has_permissions');
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
            ->orderBy('name') //->orderBy('permission_id')
        ;
    }
}
