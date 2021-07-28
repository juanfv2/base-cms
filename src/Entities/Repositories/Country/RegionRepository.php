<?php

namespace App\Repositories\Country;

use App\Models\Country\Region;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class RegionRepository
 * @package App\Repositories
 * @version July 17, 2021, 11:51 pm UTC
*/
class RegionRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Region::class;
    }
}
