<?php

namespace App\Repositories\Auth;

use App\Models\Auth\Role;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class RoleRepository
 * @package App\Repositories
 * @version September 8, 2020, 4:57 pm UTC
 */

class RoleRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Role::class;
    }
}
