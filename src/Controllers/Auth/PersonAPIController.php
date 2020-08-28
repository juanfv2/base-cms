<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Juanfv2\BaseCms\Resources\GenericResource;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;

use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;
use Juanfv2\BaseCms\Repositories\Auth\UserRepository;
use Juanfv2\BaseCms\Repositories\Auth\PersonRepository;
use Juanfv2\BaseCms\Requests\Auth\CreatePersonAPIRequest;
use Juanfv2\BaseCms\Requests\Auth\UpdatePersonAPIRequest;

/**
 * Class PersonController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */

class PersonAPIController extends BaseCmsController
{
    /** @var  PersonRepository */
    private $modelRepository;
    /** @var  UserRepository */
    private $userRepository;

    public function __construct(PersonRepository $modelRepo, UserRepository $userRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/people",
     *      summary="Get a listing of the People.",
     *      tags={"Person"},
     *      description="Get all People",
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
     *                  @SWG\Items(ref="#/definitions/Person")
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
     * @param CreatePersonAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/people",
     *      summary="Store a newly created Person in storage",
     *      tags={"Person"},
     *      description="Store Person",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Person that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Person")
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
     *                  ref="#/definitions/Person"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreatePersonAPIRequest $request)
    {
        $input = $request->all();

        $input['password'] = Hash::make($input['password']);

        try {

            DB::beginTransaction();

            $this->userRepository->create($input);
            $person = $this->modelRepository->create($input);

            DB::commit();

            return ['id' => $person->id];
        } catch (\PDOException $e) {
            DB::rollBack();
            return $this->sendError(
                __('validation.model.error', ['model' => __('models.person.name')]),
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Get(
     *      path="/people/{id}",
     *      summary="Display the specified Person",
     *      tags={"Person"},
     *      description="Get Person",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Person",
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
     *                  ref="#/definitions/Person"
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
        /** @var \App\Models\Person $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.person.name')]));
        }
        $model = new GenericResource($model);

        return $model;
    }

    /**
     * @param int $id
     * @param UpdatePersonAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/people/{id}",
     *      summary="Update the specified Person in storage",
     *      tags={"Person"},
     *      description="Update Person",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Person",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Person that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Person")
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
     *                  ref="#/definitions/Person"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdatePersonAPIRequest $request)
    {
        $input = $request->all();

        /** @var \Juanfv2\BaseCms\Models\Person $person */
        $person = $this->modelRepository->findWithoutFail($id);

        if (empty($person)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.person.name')]));
        }

        try {

            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();

            $userId = $input['user_id'];
            if (request()->has('password')) {
                $input['password'] = Hash::make($input['password']);
            }

            $this->userRepository->update($input, $userId);
            $person = $this->modelRepository->update($input, $id);

            DB::commit();
            Schema::enableForeignKeyConstraints();

            return ['id' => $person->id];
        } catch (Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();

            return $this->sendError(
                __('validation.model.error', ['model' => __('models.person.name')]),
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/people/{id}",
     *      summary="Remove the specified Person from storage",
     *      tags={"Person"},
     *      description="Delete Person",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Person",
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
        /** @var \App\Models\Person $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.person.name')]));
        }
        try {

            DB::beginTransaction();

            $model->user->delete();
            $model->delete();

            DB::commit();

            return $this->sendResponse(__('validation.model.deleted', ['model' => __('models.person.name')]), $id);
        } catch (Exception $e) {
            // Woopsy
            DB::rollBack();

            return $this->sendError(
                __('validation.model.error', ['model' => __('models.person.name')]),
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }
}
