<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\Region;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Country\RegionRepository;

/**
 * Class RegionController
 *
 * @package App\Http\Controllers\API
 */
class RegionAPIController extends AppBaseController
{
    /** @var RegionRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'Region';

    public function __construct(RegionRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->rules = Region::$rules;
    }
}
