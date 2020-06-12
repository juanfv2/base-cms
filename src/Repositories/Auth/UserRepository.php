<?php

namespace Juanfv2\BaseCms\Repositories\Auth;

use App\Models\Auth\User;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class UserRepository
 * @package App\Repositories
 * @version May 28, 2018, 4:33 am UTC
 *
 * @method User findWithoutFail($id, $columns = ['*'])
 * @method User find($id, $columns = ['*'])
 * @method User first($columns = ['*'])
 */
class UserRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'emailVerified',
        'password',
        'disabled',
        'group',
        'rememberToken',
        'createdBy',
        'updatedBy',
        'phoneNumber',
        'photoUrl',
        'role_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }
}
