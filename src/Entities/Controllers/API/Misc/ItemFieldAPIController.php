<?php

namespace App\Http\Controllers\API\Misc;

use App\Models\Misc\ItemField;
use Juanfv2\BaseCms\Controllers\AppBaseController;

/**
 * Class ItemFieldAPIController
 */
class ItemFieldAPIController extends AppBaseController
{
    /** @var \App\Models\ItemField */
    public $model;

    public $modelNameCamel = 'ItemField';

    public function __construct(ItemField $model)
    {
        $this->model = $model;
    }
}
