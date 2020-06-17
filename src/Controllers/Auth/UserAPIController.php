<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Resources\GenericResource;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;

use Juanfv2\BaseCms\Repositories\Auth\UserRepository;
use Juanfv2\BaseCms\Requests\Auth\CreateUserAPIRequest;
use Juanfv2\BaseCms\Requests\Auth\UpdateUserAPIRequest;

/**
 * Class UserController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */

class UserAPIController extends BaseCmsController
{
    /** @var  UserRepository */
    private $modelRepository;

    public function __construct(UserRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/users",
     *      summary="Get a listing of the Users.",
     *      tags={"User"},
     *      description="Get all Users",
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
     *                  @SWG\Items(ref="#/definitions/User")
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
     * @param CreateUserAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/users",
     *      summary="Store a newly created User in storage",
     *      tags={"User"},
     *      description="Store User",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="User that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/User")
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
     *                  ref="#/definitions/User"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateUserAPIRequest $request)
    {
        $input = $request->all();

        $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);

        $model = $this->modelRepository->create($input);

        // $model = new GenericResource($model);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/users/{id}",
     *      summary="Display the specified User",
     *      tags={"User"},
     *      description="Get User",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of User",
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
     *                  ref="#/definitions/User"
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
        /** @var \App\Models\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.user.name')]));
        }
        $model = new GenericResource($model);

        return $model;
    }

    /**
     * @param int $id
     * @param UpdateUserAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/users/{id}",
     *      summary="Update the specified User in storage",
     *      tags={"User"},
     *      description="Update User",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of User",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="User that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/User")
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
     *                  ref="#/definitions/User"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateUserAPIRequest $request)
    {
        $input = $request->all();

        /** @var \App\Models\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.user.name')]));
        }

        if (request()->has('password')) {
            $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        $model = $this->modelRepository->update($input, $id);

        // $model = new GenericResource(user);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/users/{id}",
     *      summary="Remove the specified User from storage",
     *      tags={"User"},
     *      description="Delete User",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of User",
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
        /** @var \App\Models\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.user.name')]));
        }

        $model->delete();

        return $this->sendResponse(__('validation.model.deleted', ['model' => __('models.user.name')]), $id);
    }
}
