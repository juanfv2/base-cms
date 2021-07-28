<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\City;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Country\CityRepository;

/**
 * Class CityController
 * @package App\Http\Controllers\API
 */
class CityAPIController extends AppBaseController
{
    /** @var CityRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'City';

    public function __construct(CityRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->rules = City::$rules;
    }
}
