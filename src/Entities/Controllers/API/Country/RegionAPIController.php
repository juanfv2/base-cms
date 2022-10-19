<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\Region;
use App\Http\Controllers\AppBaseController;

/**
 * Class RegionController
 *
 * @package App\Http\Controllers\API
 */
class RegionAPIController extends AppBaseController
{
    /** @var Region */
    public $model;
    public $rules;
    public $modelNameCamel = 'Region';

    public function __construct(Region $model)
    {
        $this->model = $model;
        $this->rules = Region::$rules;
    }
}
