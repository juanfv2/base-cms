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
    /** @var \App\Models\Country\Region */
    public $model;

    public $modelNameCamel = 'Region';

    public function __construct(Region $model)
    {
        $this->model = $model;
    }
}
