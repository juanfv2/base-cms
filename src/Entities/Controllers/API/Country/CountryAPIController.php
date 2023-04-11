<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\Country;
use Juanfv2\BaseCms\Controllers\AppBaseController;

/**
 * Class CountryController
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
