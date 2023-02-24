<?php

namespace App\Http\Controllers\API\Country;

use Juanfv2\BaseCms\Controllers\AppBaseController;
use App\Models\Country\City;

/**
 * Class CityController
 */
class CityAPIController extends AppBaseController
{
    /** @var \App\Models\Country\City */
    public $model;

    public $modelNameCamel = 'City';

    public function __construct(City $model)
    {
        $this->model = $model;
    }
}
