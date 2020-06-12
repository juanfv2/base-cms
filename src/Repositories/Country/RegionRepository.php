<?php

namespace Juanfv2\BaseCms\Repositories\Country;

use Juanfv2\BaseCms\Models\Country\Region;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class RegionRepository
 * @package App\Repositories
 * @version July 13, 2019, 2:43 pm CST
 *
 * @method Region findWithoutFail($id, $columns = ['*'])
 * @method Region find($id, $columns = ['*'])
 * @method Region first($columns = ['*'])
 */
class RegionRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'code',
        'country_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Region::class;
    }
}
