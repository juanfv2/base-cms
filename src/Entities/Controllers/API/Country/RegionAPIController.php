<?php

namespace App\Http\Controllers\API\Country;

use Juanfv2\BaseCms\Controllers\AppBaseController;
use App\Models\Country\Region;

/**
 * Class RegionController
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
