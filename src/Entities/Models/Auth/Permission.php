<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class Permission
 *
 * @version September 8, 2020, 4:57 pm UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection $authRoles
 * @property string $icon
 * @property string $name
 * @property string $urlBackEnd
 * @property string $urlFrontEnd
 * @property bool $isSection
 * @property bool $isVisible
 * @property int $orderInMenu
 * @property int $permission_id
 * @property int $createdBy
 * @property int $updatedBy
 */
class Permission extends Model
{
    use SoftDeletes,
        BaseCmsModel,
        HasFactory,
        UserResponsible;

    public $table = 'auth_permissions';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

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
        return $this->belongsToMany(\App\Models\Auth\Role::class, 'auth_roles_has_permissions');
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
            ->orderBy('name')
            //->orderBy('permission_id')
;
    }
}
