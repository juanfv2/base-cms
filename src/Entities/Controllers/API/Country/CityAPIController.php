<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\City;
use Juanfv2\BaseCms\Controllers\AppBaseController;

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
