<?php

namespace Juanfv2\BaseCms\Repositories\Country;

use App\Models\Country\City;
use Juanfv2\BaseCms\Repositories\MyBaseRepository;

/**
 * Class CityRepository
 * @package App\Repositories
 * @version July 13, 2019, 2:46 pm CST
 *
 * @method City findWithoutFail($id, $columns = ['*'])
 * @method City find($id, $columns = ['*'])
 * @method City first($columns = ['*'])
 */
class CityRepository extends MyBaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'region_id',
        'country_id',
        'latitude',
        'longitude',
        'name'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return City::class;
    }
}
