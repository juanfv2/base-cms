<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Resources\GenericResource;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;

use Juanfv2\BaseCms\Repositories\AccountRepository;
use Juanfv2\BaseCms\Http\Requests\API\CreateAccountAPIRequest;
use Juanfv2\BaseCms\Http\Requests\API\UpdateAccountAPIRequest;

/**
 * Class AccountController
 * @package Juanfv2\BaseCms\Http\Controllers\API
 */

class AccountAPIController extends BaseCmsController
{
    /** @var  AccountRepository */
    private $modelRepository;

    public function __construct(AccountRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/accounts",
     *      summary="Get a listing of the Accounts.",
     *      tags={"Account"},
     *      description="Get all Accounts",
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
     *                  @SWG\Items(ref="#/definitions/Account")
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
     * @param CreateAccountAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/accounts",
     *      summary="Store a newly created Account in storage",
     *      tags={"Account"},
     *      description="Store Account",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Account that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Account")
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
     *                  ref="#/definitions/Account"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateAccountAPIRequest $request)
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
     *      path="/accounts/{id}",
     *      summary="Display the specified Account",
     *      tags={"Account"},
     *      description="Get Account",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Account",
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
     *                  ref="#/definitions/Account"
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
        /** @var \Juanfv2\BaseCms\Models\Account $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.account.name')]));
        }
        $model = new GenericResource($model);

        return $model;
    }

    /**
     * @param int $id
     * @param UpdateAccountAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/accounts/{id}",
     *      summary="Update the specified Account in storage",
     *      tags={"Account"},
     *      description="Update Account",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Account",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Account that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Account")
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
     *                  ref="#/definitions/Account"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateAccountAPIRequest $request)
    {
        $input = $request->all();

        /** @var \Juanfv2\BaseCms\Models\Account $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.account.name')]));
        }

        $model = $this->modelRepository->update($input, $id);

        // $model = new GenericResource(account);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/accounts/{id}",
     *      summary="Remove the specified Account from storage",
     *      tags={"Account"},
     *      description="Delete Account",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Account",
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
        /** @var \Juanfv2\BaseCms\Models\Account $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.account.name')]));
        }

        $model->delete();

        return $this->sendResponse(__('validation.model.deleted', ['model' => __('models.account.name')]), $id);
    }
}
