<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\Country;
use App\Http\Controllers\AppBaseController;

/**
 * Class CountryController
 *
 * @package App\Http\Controllers\API
 */
class CountryAPIController extends AppBaseController
{
    /** @var \App\Models\Country */
    public $model;

    public $modelNameCamel = 'Country';

    public function __construct(Country $model)
    {
        $this->model = $model;
    }
}
