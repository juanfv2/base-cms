<?php

namespace App\Http\Controllers\API\Misc;

use App\Http\Controllers\AppBaseController;
use App\Models\Misc\XFile;

/**
 * Class XFileController
 */
class XFileAPIController extends AppBaseController
{
    /** @var App\Models\XFile */
    public $model;

    public $modelNameCamel = 'XFile';

    public function __construct(XFile $model)
    {
        $this->model = $model;
    }
}
