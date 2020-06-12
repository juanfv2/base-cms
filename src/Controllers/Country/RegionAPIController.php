<?php

namespace Juanfv2\BaseCms\Controllers\Country;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Resources\GenericResource;
use App\Criteria\RequestGenericCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Repositories\Country\RegionRepository;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Requests\Country\CreateRegionAPIRequest;
use Juanfv2\BaseCms\Requests\Country\UpdateRegionAPIRequest;

/**
 * Class RegionController
 * @package Juanfv2\BaseCms\Controllers\API
 */

class RegionAPIController extends BaseCmsController
{
    /** @var  RegionRepository */
    private $regionRepository;

    public function __construct(RegionRepository $regionRepo)
    {
        $this->regionRepository = $regionRepo;
    }

    /**
     * Display a listing of the Region.
     * GET|HEAD /regions
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action', '-');
        $criteria = new RequestGenericCriteria($request);

        $this->regionRepository->pushCriteria($criteria);
        $itemCount = $this->regionRepository->count();

        if ($action != 'export') {
            $this->regionRepository->pushCriteria(new LimitOffsetCriteria($request));
        }

        $regions = $this->regionRepository->all();

        /* */
        $items = GenericResource::collection($regions);
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
     * Store a newly created Region in storage.
     * POST /regions
     *
     * @param CreateRegionAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateRegionAPIRequest $request)
    {
        $input = $request->all();

        $region = $this->regionRepository->create($input);

        // $region = new GenericResource($region);

        return ['id' => $region->id];
    }

    /**
     * Display the specified Region.
     * GET|HEAD /regions/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var \Juanfv2\BaseCms\Models\Region $region */
        $region = $this->regionRepository->findWithoutFail($id);

        if (empty($region)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Region']));
        }
        $region = new GenericResource($region);

        return $region;
    }

    /**
     * Update the specified Region in storage.
     * PUT/PATCH /regions/{id}
     *
     * @param  int $id
     * @param UpdateRegionAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateRegionAPIRequest $request)
    {
        $input = $request->all();

        /** @var \Juanfv2\BaseCms\Models\Region $region */
        $region = $this->regionRepository->findWithoutFail($id);

        if (empty($region)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Region']));
        }

        $region = $this->regionRepository->update($input, $id);

        // $region = new GenericResource(region);

        return ['id' => $region->id];
    }

    /**
     * Remove the specified Region from storage.
     * DELETE /regions/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var \Juanfv2\BaseCms\Models\Region $region */
        $region = $this->regionRepository->findWithoutFail($id);

        if (empty($region)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Region']));
        }

        $region->delete();

        return $this->sendResponse($id, __('validation.model.deleted', ['model' => 'Region']));
    }
}
