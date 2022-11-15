<?php

namespace App\Http\Controllers\API\Misc;

use App\Models\Misc\VisorLogError;
use App\Http\Controllers\AppBaseController;

/**
 * Class VisorLogErrorController
 */
class VisorLogErrorAPIController extends AppBaseController
{
    /** @var App\Models\VisorLogError */
    public $model;

    public $modelNameCamel = 'VisorLogError';

    public function __construct(VisorLogError $model)
    {
        $this->model = $model;
    }
}
