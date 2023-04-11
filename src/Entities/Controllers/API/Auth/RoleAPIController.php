<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use Illuminate\Http\Request;
use Juanfv2\BaseCms\Controllers\AppBaseController;

/**
 * Class RoleController
 */
class RoleAPIController extends AppBaseController
{
    /** @var \App\Models\Auth\Role */
    public $model;

    public $modelNameCamel = 'Role';

    public function __construct(Role $model)
    {
        $this->model = $model;
    }

    public function permissions(Request $request)
    {
        $this->model = new Permission();

        return $this->index($request);
    }
}
