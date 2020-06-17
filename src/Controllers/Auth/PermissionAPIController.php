<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Models\Auth\Permission;
use Juanfv2\BaseCms\Resources\GenericResource;
use Prettus\Repository\Criteria\RequestCriteria;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;

use Juanfv2\BaseCms\Repositories\Auth\PermissionRepository;
use Juanfv2\BaseCms\Requests\Auth\CreatePermissionAPIRequest;
use Juanfv2\BaseCms\Requests\Auth\UpdatePermissionAPIRequest;

/**
 * Class PermissionController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */

class PermissionAPIController extends BaseCmsController
{
    /** @var  PermissionRepository */
    private $modelRepository;

    public function __construct(PermissionRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/permissions",
     *      summary="Get a listing of the Permissions.",
     *      tags={"Permission"},
     *      description="Get all Permissions",
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
     *                  @SWG\Items(ref="#/definitions/Permission")
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
        $criteria = new RequestGenericCriteria($request);

        $this->modelRepository->pushCriteria($criteria);
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
                return $this->export($zname, $headers, $items->collection->toArray());
            default:
                return $this->response2Api($items, $itemCount, $request->get('limit', -1));
        }
    }

    /**
     * @param CreatePermissionAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/permissions",
     *      summary="Store a newly created Permission in storage",
     *      tags={"Permission"},
     *      description="Store Permission",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Permission that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Permission")
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
     *                  ref="#/definitions/Permission"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreatePermissionAPIRequest $request)
    {
        $input = $request->all();

        if ($input['permissions']) {

            $results = [];

            foreach ($input['permissions'] as $value) {
                $results[] = $this->createMenus($value);
            }

            return $results;
        }

        $model = $this->modelRepository->create($input);

        // $model = new GenericResource($model);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/permissions/{id}",
     *      summary="Display the specified Permission",
     *      tags={"Permission"},
     *      description="Get Permission",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Permission",
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
     *                  ref="#/definitions/Permission"
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
        /** @var \App\Models\Permission $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.permission.name')]));
        }
        $model = new GenericResource($model);

        return $model;
    }

    /**
     * @param int $id
     * @param UpdatePermissionAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/permissions/{id}",
     *      summary="Update the specified Permission in storage",
     *      tags={"Permission"},
     *      description="Update Permission",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Permission",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Permission that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Permission")
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
     *                  ref="#/definitions/Permission"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdatePermissionAPIRequest $request)
    {
        $input = $request->all();

        /** @var \App\Models\Permission $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.permission.name')]));
        }

        $model = $this->modelRepository->update($input, $id);

        // $model = new GenericResource(permission);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/permissions/{id}",
     *      summary="Remove the specified Permission from storage",
     *      tags={"Permission"},
     *      description="Delete Permission",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Permission",
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
        /** @var \App\Models\Permission $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.permission.name')]));
        }

        $model->delete();

        return $this->sendResponse(__('validation.model.deleted', ['model' => __('models.permission.name')]), $id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *      path="/permissions/menus",
     *      summary="Get a listing of the Permissions.",
     *      tags={"Permission"},
     *      description="Get all Permissions",
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
     *                  @SWG\Items(ref="#/definitions/Permission")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function menus(Request $request)
    {
        $criteria = new RequestCriteria($request);

        $this->modelRepository->pushCriteria($criteria);
        $permissions = $this->modelRepository->all();

        /* */
        $permissions = GenericResource::collection($permissions);
        /* */

        return $this->response2Api($permissions, -1, $request->get('limit', -1));
    }

    public function createMenus($request)
    {
        $id = isset($request['id']) ? $request['id'] : null;
        $isGroup = isset($request['isGroup']);
        $nameSingular = $request['name'];
        $namePlural = $request['namePlural'];
        $icon = $request['icon'];
        $namePluralBackEnd = $request['namePluralBackEnd'];
        $isSection = $request['isSection'];
        $isVisible = $request['isVisible'];
        $permission_id = $request['permission_id'];
        $orderInMenu = $request['orderInMenu'];

        $permissionIndex = new Permission();
        $permissionIndex->id = $id;
        $permissionIndex->name = $namePlural;
        $permissionIndex->icon = $icon;
        $permissionIndex->urlBackEnd = 'api.' . $namePluralBackEnd . '.index';
        $permissionIndex->urlFrontEnd = '/' . $namePluralBackEnd;
        $permissionIndex->isSection = $isSection;
        $permissionIndex->isVisible = $isVisible;
        $permissionIndex->permission_id = $permission_id;
        $permissionIndex->orderInMenu = $orderInMenu;
        $permissionIndex->save();

        if ($isGroup) {

            $permissionShow = new Permission();
            $permissionShow->name = 'Mostrar ' . $nameSingular;
            $permissionShow->icon = $icon;
            $permissionShow->urlBackEnd = 'api.' . $namePluralBackEnd . '.show';
            $permissionShow->urlFrontEnd = '/' . $namePluralBackEnd . '/show';
            $permissionShow->isSection = 0;
            $permissionShow->isVisible = 0;
            $permissionShow->permission_id = $permissionIndex->id;
            $permissionShow->orderInMenu = 0;
            $permissionShow->save();

            $permissionCreate = new Permission();
            $permissionCreate->name = 'Crear ' . $nameSingular;
            $permissionCreate->icon = $icon;
            $permissionCreate->urlBackEnd = 'api.' . $namePluralBackEnd . '.store';
            $permissionCreate->urlFrontEnd = '/' . $namePluralBackEnd . '/new';
            $permissionCreate->isSection = 0;
            $permissionCreate->isVisible = 0;
            $permissionCreate->permission_id = $permissionIndex->id;
            $permissionCreate->orderInMenu = 1;
            $permissionCreate->save();

            $permissionUpdate = new Permission();
            $permissionUpdate->name = 'Actualizar ' . $nameSingular;
            $permissionUpdate->icon = $icon;
            $permissionUpdate->urlBackEnd = 'api.' . $namePluralBackEnd . '.update';
            $permissionUpdate->urlFrontEnd = '/' . $namePluralBackEnd . '/edit';
            $permissionUpdate->isSection = 0;
            $permissionUpdate->isVisible = 0;
            $permissionUpdate->permission_id = $permissionIndex->id;
            $permissionUpdate->orderInMenu = 2;
            $permissionUpdate->save();

            $permissionDelete = new Permission();
            $permissionDelete->name = 'Borrar ' . $nameSingular;
            $permissionDelete->icon = $icon;
            $permissionDelete->urlBackEnd = 'api.' . $namePluralBackEnd . '.destroy';
            $permissionDelete->urlFrontEnd = '/' . $namePluralBackEnd . '/delete';
            $permissionDelete->isSection = 0;
            $permissionDelete->isVisible = 0;
            $permissionDelete->permission_id = $permissionIndex->id;
            $permissionDelete->orderInMenu = 3;
            $permissionDelete->save();
        }

        return 'ok';
    }
}
