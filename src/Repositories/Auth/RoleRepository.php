<?php

namespace Juanfv2\BaseCms\Repositories\Auth;

use Juanfv2\BaseCms\Models\Auth\Role;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class RoleRepository
 * @package App\Repositories
 * @version May 28, 2018, 4:35 am UTC
 *
 * @method Role findWithoutFail($id, $columns = ['*'])
 * @method Role find($id, $columns = ['*'])
 * @method Role first($columns = ['*'])
*/
class RoleRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'description',
        'createdBy',
        'updatedBy'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Role::class;
    }
}
