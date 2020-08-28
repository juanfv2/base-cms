<?php

namespace Juanfv2\BaseCms\Controllers\Country;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Repositories\Country\CityRepository;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Requests\Country\CreateCityAPIRequest;
use Juanfv2\BaseCms\Requests\Country\UpdateCityAPIRequest;

/**
 * Class CityController
 * @package Juanfv2\BaseCms\Controllers\API
 */

class CityAPIController extends BaseCmsController
{
    /** @var  CityRepository */
    private $modelRepository;

    public function __construct(CityRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/cities",
     *      summary="Get a listing of the Cities.",
     *      tags={"City"},
     *      description="Get all Cities",
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
     *                  @SWG\Items(ref="#/definitions/City")
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
        $limit = (int) $request->get('limit', -1);

        // if ($limit < 1) {
        //     return $this->sendError('set - limit - please', 500);
        // }

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
     * @param CreateCityAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/cities",
     *      summary="Store a newly created City in storage",
     *      tags={"City"},
     *      description="Store City",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="City that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/City")
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
     *                  ref="#/definitions/City"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCityAPIRequest $request)
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
     *      path="/cities/{id}",
     *      summary="Display the specified City",
     *      tags={"City"},
     *      description="Get City",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of City",
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
     *                  ref="#/definitions/City"
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
        /** @var \App\Models\City $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.city.name')]));
        }
        $model = new GenericResource($model);

        return $model;
    }

    /**
     * @param int $id
     * @param UpdateCityAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/cities/{id}",
     *      summary="Update the specified City in storage",
     *      tags={"City"},
     *      description="Update City",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of City",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="City that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/City")
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
     *                  ref="#/definitions/City"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCityAPIRequest $request)
    {
        $input = $request->all();

        /** @var \App\Models\City $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.city.name')]));
        }

        $model = $this->modelRepository->update($input, $id);

        // $model = new GenericResource(city);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/cities/{id}",
     *      summary="Remove the specified City from storage",
     *      tags={"City"},
     *      description="Delete City",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of City",
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
        /** @var \App\Models\City $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.city.name')]));
        }

        $model->delete();

        return $this->sendResponse(__('validation.model.deleted', ['model' => __('models.city.name')]), $id);
    }
}
