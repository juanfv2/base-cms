<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\XFile;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Auth\XFileRepository;

/**
 * Class XFileController
 * @package App\Http\Controllers\API
 */
class XFileAPIController extends AppBaseController
{
    /** @var XFileRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'xFile';

    public function __construct(XFileRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->rules = XFile::$rules;
    }
}