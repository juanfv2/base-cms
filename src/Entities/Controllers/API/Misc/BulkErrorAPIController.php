<?php

namespace App\Http\Controllers\API\Misc;

use App\Models\Misc\BulkError;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Misc\BulkErrorRepository;

/**
 * Class BulkErrorController
 *
 * @package App\Http\Controllers\API
 */
class BulkErrorAPIController extends AppBaseController
{
    /** @var BulkErrorRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'BulkError';

    public function __construct(BulkErrorRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->rules = BulkError::$rules;
    }
}
