<?php

namespace App\Repositories\Auth;

use App\Models\Auth\Permission;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class PermissionRepository
 * @package App\Repositories
 * @version September 8, 2020, 4:57 pm UTC
 */

class PermissionRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Permission::class;
    }
}
