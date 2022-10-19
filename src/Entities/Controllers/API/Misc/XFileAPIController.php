<?php

namespace App\Http\Controllers\API\Misc;

use App\Models\Misc\XFile;
use App\Http\Controllers\AppBaseController;

/**
 * Class XFileController
 *
 * @package App\Http\Controllers\API\Auth
 */
class XFileAPIController extends AppBaseController
{
    /** @var App\Models\XFile */
    public $model;
    public $rules;
    public $modelNameCamel = 'XFile';

    public function __construct(XFile $model)
    {
        $this->model = $model;
        $this->rules = XFile::$rules;
    }
}
