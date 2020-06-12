<?php

namespace Juanfv2\BaseCms\Repositories\Country;

use Juanfv2\BaseCms\Models\Country\Country;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class CountryRepository
 * @package Juanfv2\BaseCms\Repositories
 * @version July 13, 2019, 1:54 pm CST
 *
 * @method Country findWithoutFail($id, $columns = ['*'])
 * @method Country find($id, $columns = ['*'])
 * @method Country first($columns = ['*'])
*/
class CountryRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'code'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Country::class;
    }
}
