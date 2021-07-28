<?php

namespace App\Repositories\Country;

use App\Models\Country\City;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class CityRepository
 * @package App\Repositories
 * @version July 18, 2021, 12:03 am UTC
*/
class CityRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return City::class;
    }
}
