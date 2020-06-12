<?php

namespace Juanfv2\BaseCms\Repositories\Auth;

use Juanfv2\BaseCms\Models\Auth\Person;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class PersonRepository
 * @package Juanfv2\BaseCms\Repositories
 * @version January 14, 2019, 8:34 am UTC
 *
 * @method Person findWithoutFail($id, $columns = ['*'])
 * @method Person find($id, $columns = ['*'])
 * @method Person first($columns = ['*'])
*/
class PersonRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'firstName',
        'lastName',
        'photoUrl',
        'phone',
        'cellPhone',
        'birthdate',
        'email',
        'createdBy',
        'updatedBy'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Person::class;
    }
}
