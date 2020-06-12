<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;
use Juanfv2\BaseCms\Repositories\Auth\RoleRepository;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Requests\Auth\CreateRoleAPIRequest;
use Juanfv2\BaseCms\Requests\Auth\UpdateRoleAPIRequest;

/**
 * Class RoleController
 * @package Juanfv2\BaseCms\Controllers\API
 */

class RoleAPIController extends BaseCmsController
{
    /** @var  RoleRepository */
    private $roleRepository;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepository = $roleRepo;
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
        $criteria = new RequestGenericCriteria($request);

        $this->roleRepository->pushCriteria($criteria);
        $itemCount = $this->roleRepository->count();
        $this->roleRepository->pushCriteria(new LimitOffsetCriteria($request));

        $roles = $this->roleRepository->all();

        /* */
        $items = GenericResource::collection($roles);
        /* */

        switch ($request->get('action', '-')) {
            case 'export':
                $headers = json_decode($request->get('fields'), true);
                $zname = $request->get('title', '-');
                return $this->export($zname, $headers, $items->collection->toArray());

            default:
                return $this->response2Api($items, $itemCount, $request->get('limit', -1));
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

        $role = $this->roleRepository->create($input);

        // $role = new GenericResource($role);

        return ['id' => $role->id];
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
    public function show($id, Request $request)
    {
        /** @var \Juanfv2\BaseCms\Models\Role $role */
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Role']));
        }
        $role = new GenericResource($role);

        return $role;
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

        /** @var \Juanfv2\BaseCms\Models\Role $role */
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Role']));
        }

        $role = $this->roleRepository->update($input, $id);

        // $role = new GenericResource($role);

        return ['id' => $role->id];
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
        /** @var \Juanfv2\BaseCms\Models\Role $role */
        $role = $this->roleRepository->findWithoutFail($id);

        if (empty($role)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Role']));
        }

        $role->delete();

        return $this->sendResponse($id, __('validation.model.deleted', ['model' => 'Role']));
    }
}
