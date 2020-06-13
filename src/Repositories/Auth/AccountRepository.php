<?php

namespace Juanfv2\BaseCms\Repositories\Auth;

use Juanfv2\BaseCms\Models\Auth\Account;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class AccountRepository
 * @package Juanfv2\BaseCms\Repositories
 * @version June 13, 2020, 12:52 am UTC
*/

class AccountRepository extends MyBaseRepository
{
     /**
     * @var array
     */
    protected $fieldSearchable = [
        'firstName',
        'lastName',
        'phone',
        'cellPhone',
        'birthDate',
        'email',
        'address',
        'neighborhood',
        'country_id',
        'region_id',
        'city_id',
        'createdBy',
        'updatedBy'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Account::class;
    }
}
