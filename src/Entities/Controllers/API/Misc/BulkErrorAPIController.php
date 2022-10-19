<?php

namespace App\Http\Controllers\API\Misc;

use App\Models\Misc\BulkError;
use App\Http\Controllers\AppBaseController;

/**
 * Class BulkErrorController
 *
 * @package App\Http\Controllers\API
 */
class BulkErrorAPIController extends AppBaseController
{
    /** @var App\Models\BulkError */
    public $model;
    public $rules;
    public $modelNameCamel = 'BulkError';

    public function __construct(BulkError $model)
    {
        $this->model = $model;
        $this->rules = BulkError::$rules;
    }
}
