<?php

namespace Juanfv2\BaseCms\Repositories\Auth;

use App\Models\Auth\Permission;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class PermissionRepository
 * @package App\Repositories
 * @version May 28, 2018, 4:36 am UTC
 *
 * @method Permission findWithoutFail($id, $columns = ['*'])
 * @method Permission find($id, $columns = ['*'])
 * @method Permission first($columns = ['*'])
 */
class PermissionRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
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
     * Configure the Model
     **/
    public function model()
    {
        return Permission::class;
    }
}
