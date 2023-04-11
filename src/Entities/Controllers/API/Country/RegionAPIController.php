<?php

namespace App\Http\Controllers\API\Country;

use App\Models\Country\Region;
use Juanfv2\BaseCms\Controllers\AppBaseController;

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
