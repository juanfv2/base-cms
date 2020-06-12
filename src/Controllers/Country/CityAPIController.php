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
    private $cityRepository;

    public function __construct(CityRepository $cityRepo)
    {
        $this->cityRepository = $cityRepo;
    }

    /**
     * Display a listing of the City.
     * GET|HEAD /cities
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action', '-');
        $limit = (int) $request->get('limit', -1);

        // if ($limit < 1) {
        //     return $this->sendError('set - limit - please', 500);
        // }

        $criteria = new RequestGenericCriteria($request);

        $this->cityRepository->pushCriteria($criteria);
        $itemCount = $this->cityRepository->count();

        if ($action != 'export') {
            $this->cityRepository->pushCriteria(new LimitOffsetCriteria($request));
        }

        $cities = $this->cityRepository->all();

        /* */
        $items = GenericResource::collection($cities);
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
     * Store a newly created City in storage.
     * POST /cities
     *
     * @param CreateCityAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateCityAPIRequest $request)
    {
        $input = $request->all();

        $city = $this->cityRepository->create($input);

        // $city = new GenericResource($city);

        return ['id' => $city->id];
    }

    /**
     * Display the specified City.
     * GET|HEAD /cities/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var \Juanfv2\BaseCms\Models\City $city */
        $city = $this->cityRepository->findWithoutFail($id);

        if (empty($city)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'City']));
        }
        $city = new GenericResource($city);

        return $city;
    }

    /**
     * Update the specified City in storage.
     * PUT/PATCH /cities/{id}
     *
     * @param  int $id
     * @param UpdateCityAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCityAPIRequest $request)
    {
        $input = $request->all();

        /** @var \Juanfv2\BaseCms\Models\City $city */
        $city = $this->cityRepository->findWithoutFail($id);

        if (empty($city)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'City']));
        }

        $city = $this->cityRepository->update($input, $id);

        // $city = new GenericResource(city);

        return ['id' => $city->id];
    }

    /**
     * Remove the specified City from storage.
     * DELETE /cities/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var \Juanfv2\BaseCms\Models\City $city */
        $city = $this->cityRepository->findWithoutFail($id);

        if (empty($city)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'City']));
        }

        $city->delete();

        return $this->sendResponse($id, __('validation.model.deleted', ['model' => 'City']));
    }
}
