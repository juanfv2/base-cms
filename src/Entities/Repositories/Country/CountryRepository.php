<?php

namespace App\Repositories\Country;

use App\Models\Country\Country;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class CountryRepository
 * @package App\Repositories
 * @version July 17, 2021, 11:36 pm UTC
*/
class CountryRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Country::class;
    }
}
