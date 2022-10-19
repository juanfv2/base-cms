<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\City;
use App\Http\Controllers\AppBaseController;

/**
 * Class CityController
 *
 * @package App\Http\Controllers\API
 */
class CityAPIController extends AppBaseController
{
    /** @var  */
    public $model;
    public $rules;
    public $modelNameCamel = 'City';

    public function __construct(City $model)
    {
        $this->model = $model;
        $this->rules = City::$rules;
    }
}
