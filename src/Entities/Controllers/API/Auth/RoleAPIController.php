<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\Role;
use Illuminate\Http\Request;

use App\Http\Controllers\AppBaseController;
use App\Repositories\Auth\RoleRepository;
use App\Repositories\Auth\PermissionRepository;

/**
 * Class RoleController
 *
 * @package App\Http\Controllers\API\Auth
 */
class RoleAPIController extends AppBaseController
{
    /** @var  PermissionRepository */
    private $permissionRepository;

    /** @var  RoleRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'Role';

    public function __construct(RoleRepository $modelRepo, PermissionRepository $pRepo)
    {
        $this->permissionRepository = $pRepo;
        $this->modelRepository = $modelRepo;
        $this->rules = Role::$rules;
    }

    public function permissions(Request $request)
    {
        $this->modelRepository = $this->permissionRepository;

        return $this->index($request);
    }
}
