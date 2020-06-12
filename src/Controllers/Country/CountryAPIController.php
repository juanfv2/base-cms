<?php

namespace Juanfv2\BaseCms\Controllers\Country;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Resources\GenericResource;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;

use Juanfv2\BaseCms\Repositories\Country\CountryRepository;
use Juanfv2\BaseCms\Requests\Country\CreateCountryAPIRequest;
use Juanfv2\BaseCms\Requests\Country\UpdateCountryAPIRequest;

/**
 * Class CountryController
 * @package Juanfv2\BaseCms\Controllers\API
 */

class CountryAPIController extends BaseCmsController
{
    /** @var  CountryRepository */
    private $modelRepository;

    public function __construct(CountryRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Get(
     *      path="/countries",
     *      summary="Get a listing of the Countries.",
     *      tags={"Country"},
     *      description="Get all Countries",
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
     *                  @SWG\Items(ref="#/definitions/Country")
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
     * @param CreateCountryAPIRequest $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/countries",
     *      summary="Store a newly created Country in storage",
     *      tags={"Country"},
     *      description="Store Country",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Country that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Country")
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
     *                  ref="#/definitions/Country"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCountryAPIRequest $request)
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
     *      path="/countries/{id}",
     *      summary="Display the specified Country",
     *      tags={"Country"},
     *      description="Get Country",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Country",
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
     *                  ref="#/definitions/Country"
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
        /** @var \App\Models\Country $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.country.name')]));
        }
        $model = new GenericResource($model);

        return $model;
    }

    /**
     * @param int $id
     * @param UpdateCountryAPIRequest $request
     * @return Response
     *
     * @SWG\Put(
     *      path="/countries/{id}",
     *      summary="Update the specified Country in storage",
     *      tags={"Country"},
     *      description="Update Country",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Country",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Country that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Country")
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
     *                  ref="#/definitions/Country"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCountryAPIRequest $request)
    {
        $input = $request->all();

        /** @var \App\Models\Country $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.country.name')]));
        }

        $model = $this->modelRepository->update($input, $id);

        // $model = new GenericResource(country);

        return ['id' => $model->id];
    }

    /**
     * @param int $id
     * @return Response
     *
     * @SWG\Delete(
     *      path="/countries/{id}",
     *      summary="Remove the specified Country from storage",
     *      tags={"Country"},
     *      description="Delete Country",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of Country",
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
        /** @var \App\Models\Country $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __('models.country.name')]));
        }

        $model->delete();

        return $this->sendResponse($id, __('validation.model.deleted', ['model' => __('models.country.name')]));
    }
}
