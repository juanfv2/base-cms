<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Juanfv2\BaseCms\Resources\GenericResource;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;

use Juanfv2\BaseCms\Repositories\Auth\RoleRepository;
use Juanfv2\BaseCms\Requests\Auth\CreateRoleAPIRequest;
use Juanfv2\BaseCms\Requests\Auth\UpdateRoleAPIRequest;
use Juanfv2\BaseCms\Repositories\Auth\PermissionRepository;

/**
 * Class RoleController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */

class RoleAPIController extends AppBaseController
{
  /** @var  RoleRepository */
  private $modelRepository;
  /** @var  PermissionRepository */
  private $permissionRepository;

  public function __construct(RoleRepository $modelRepo, PermissionRepository $pRepo)
  {
    $this->modelRepository = $modelRepo;
    $this->permissionRepository = $pRepo;
  }

  /**
   * @param Request $request
   * @return Response
   *
   * @SWG\Get(
   *      path="/roles",
   *      summary="Get a listing of the Roles.",
   *      tags={"Role"},
   *      description="Get all Roles",
   *      produces={"application/json"},
   *      @SWG\Response(
   *          response=200,
   *          description="successful operation",
   *          @SWG\Schema(
   *              type="object",
   *              @SWG\Property(
   *                  property="success",
   *                  type="boolean"
   *              ),
   *              @SWG\Property(
   *                  property="data",
   *                  type="array",
   *                  @SWG\Items(ref="#/definitions/Role")
   *              ),
   *              @SWG\Property(
   *                  property="message",
   *                  type="string"
   *              )
   *          )
   *      )
   * )
   */
  public function index(Request $request)
  {
    $action = $request->get('action', '-');
    $limit  = $request->get('limit', -1);

    $this->modelRepository->pushCriteria(new RequestGenericCriteria($request));
    $itemCount = $this->modelRepository->count();

    if ($action != 'export') {
      $this->modelRepository->pushCriteria(new LimitOffsetCriteria($request));
    }

    $items = $this->modelRepository->all();

    /* */
    $items = GenericResource::collection($items);
    /* */

    switch ($action) {
      case 'export':
        $headers = json_decode($request->get('fields'), true);
        $zname = $request->get('title', '-');
        return $this->export($zname, $headers, $items);
      default:
        return $this->response2Api($items, $itemCount, $limit);
    }
  }

  /**
   * @param CreateRoleAPIRequest $request
   * @return Response
   *
   * @SWG\Post(
   *      path="/roles",
   *      summary="Store a newly created Role in storage",
   *      tags={"Role"},
   *      description="Store Role",
   *      produces={"application/json"},
   *      @SWG\Parameter(
   *          name="body",
   *          in="body",
   *          description="Role that should be stored",
   *          required=false,
   *          @SWG\Schema(ref="#/definitions/Role")
   *      ),
   *      @SWG\Response(
   *          response=200,
   *          description="successful operation",
   *          @SWG\Schema(
   *              type="object",
   *              @SWG\Property(
   *                  property="success",
   *                  type="boolean"
   *              ),
   *              @SWG\Property(
   *                  property="data",
   *                  ref="#/definitions/Role"
   *              ),
   *              @SWG\Property(
   *                  property="message",
   *                  type="string"
   *              )
   *          )
   *      )
   * )
   */
  public function store(CreateRoleAPIRequest $request)
  {
    $input = $request->all();

    $model = $this->modelRepository->create($input);

    // $model = new GenericResource($model);

    return ['id' => $model->id];
  }

  /**
   * @param int $id
   * @return Response
   *
   * @SWG\Get(
   *      path="/roles/{id}",
   *      summary="Display the specified Role",
   *      tags={"Role"},
   *      description="Get Role",
   *      produces={"application/json"},
   *      @SWG\Parameter(
   *          name="id",
   *          description="id of Role",
   *          type="integer",
   *          required=true,
   *          in="path"
   *      ),
   *      @SWG\Response(
   *          response=200,
   *          description="successful operation",
   *          @SWG\Schema(
   *              type="object",
   *              @SWG\Property(
   *                  property="success",
   *                  type="boolean"
   *              ),
   *              @SWG\Property(
   *                  property="data",
   *                  ref="#/definitions/Role"
   *              ),
   *              @SWG\Property(
   *                  property="message",
   *                  type="string"
   *              )
   *          )
   *      )
   * )
   */
  public function show($id)
  {
    /** @var \App\Models\Role $model */
    $model = $this->modelRepository->findWithoutFail($id);

    if (empty($model)) {
      return $this->sendError(__('validation.model.not.found', ['model' => __('models.role.name')]));
    }
    $model = new GenericResource($model);

    return $model;
  }

  /**
   * @param int $id
   * @param UpdateRoleAPIRequest $request
   * @return Response
   *
   * @SWG\Put(
   *      path="/roles/{id}",
   *      summary="Update the specified Role in storage",
   *      tags={"Role"},
   *      description="Update Role",
   *      produces={"application/json"},
   *      @SWG\Parameter(
   *          name="id",
   *          description="id of Role",
   *          type="integer",
   *          required=true,
   *          in="path"
   *      ),
   *      @SWG\Parameter(
   *          name="body",
   *          in="body",
   *          description="Role that should be updated",
   *          required=false,
   *          @SWG\Schema(ref="#/definitions/Role")
   *      ),
   *      @SWG\Response(
   *          response=200,
   *          description="successful operation",
   *          @SWG\Schema(
   *              type="object",
   *              @SWG\Property(
   *                  property="success",
   *                  type="boolean"
   *              ),
   *              @SWG\Property(
   *                  property="data",
   *                  ref="#/definitions/Role"
   *              ),
   *              @SWG\Property(
   *                  property="message",
   *                  type="string"
   *              )
   *          )
   *      )
   * )
   */
  public function update($id, UpdateRoleAPIRequest $request)
  {
    $input = $request->all();

    /** @var \App\Models\Role $model */
    $model = $this->modelRepository->findWithoutFail($id);

    if (empty($model)) {
      return $this->sendError(__('validation.model.not.found', ['model' => __('models.role.name')]));
    }

    $model = $this->modelRepository->update($input, $id);

    // $model = new GenericResource(role);

    return ['id' => $model->id];
  }

  /**
   * @param int $id
   * @return Response
   *
   * @SWG\Delete(
   *      path="/roles/{id}",
   *      summary="Remove the specified Role from storage",
   *      tags={"Role"},
   *      description="Delete Role",
   *      produces={"application/json"},
   *      @SWG\Parameter(
   *          name="id",
   *          description="id of Role",
   *          type="integer",
   *          required=true,
   *          in="path"
   *      ),
   *      @SWG\Response(
   *          response=200,
   *          description="successful operation",
   *          @SWG\Schema(
   *              type="object",
   *              @SWG\Property(
   *                  property="success",
   *                  type="boolean"
   *              ),
   *              @SWG\Property(
   *                  property="data",
   *                  type="string"
   *              ),
   *              @SWG\Property(
   *                  property="message",
   *                  type="string"
   *              )
   *          )
   *      )
   * )
   */
  public function destroy($id)
  {
    /** @var \App\Models\Role $model */
    $model = $this->modelRepository->findWithoutFail($id);

    if (empty($model)) {
      return $this->sendError(__('validation.model.not.found', ['model' => __('models.role.name')]));
    }

    $model->delete();

    return $this->sendResponse(__('validation.model.deleted', ['model' => __('models.role.name')]), $id);
  }

  public function permissions(Request $request)
  {
    $action = $request->get('action', '-');
    $limit  = $request->get('limit', -1);

    $this->permissionRepository->pushCriteria(new RequestGenericCriteria($request));
    $itemCount = $this->permissionRepository->count();

    if ($action != 'export') {
      $this->permissionRepository->pushCriteria(new LimitOffsetCriteria($request));
    }

    $items = $this->permissionRepository->all();

    /* */
    $items = GenericResource::collection($items);
    /* */

    switch ($action) {
      case 'export':
        $headers = json_decode($request->get('fields'), true);
        $zname = $request->get('title', '-');
        return $this->export($zname, $headers, $items);
      default:
        return $this->response2Api($items, $itemCount, $limit);
    }
  }
}
