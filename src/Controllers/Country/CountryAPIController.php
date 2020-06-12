<?php

namespace Juanfv2\BaseCms\Controllers\Country;

use Illuminate\Http\Request;
use Juanfv2\BaseCms\Criteria\RequestGenericCriteria;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Repositories\Country\CountryRepository;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Requests\Country\CreateCountryAPIRequest;
use Juanfv2\BaseCms\Requests\Country\UpdateCountryAPIRequest;

/**
 * Class CountryController
 * @package Juanfv2\BaseCms\Controllers\API
 */

class CountryAPIController extends BaseCmsController
{
    /** @var  CountryRepository */
    private $countryRepository;

    public function __construct(CountryRepository $countryRepo)
    {
        $this->countryRepository = $countryRepo;
    }

    /**
     * Display a listing of the Country.
     * GET|HEAD /countries
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action', '-');
        $criteria = new RequestGenericCriteria($request);

        $this->countryRepository->pushCriteria($criteria);
        $itemCount = $this->countryRepository->count();
        if ($action != 'export') {
            $this->countryRepository->pushCriteria(new LimitOffsetCriteria($request));
        }

        $countries = $this->countryRepository->all();

        /* */
        $items = GenericResource::collection($countries);
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
     * Store a newly created Country in storage.
     * POST /countries
     *
     * @param CreateCountryAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateCountryAPIRequest $request)
    {
        $input = $request->all();

        $country = $this->countryRepository->create($input);

        // $country = new GenericResource($country);

        return ['id' => $country->id];
    }

    /**
     * Display the specified Country.
     * GET|HEAD /countries/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var \Juanfv2\BaseCms\Models\Country $country */
        $country = $this->countryRepository->findWithoutFail($id);

        if (empty($country)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Country']));
        }
        $country = new GenericResource($country);

        return $country;
    }

    /**
     * Update the specified Country in storage.
     * PUT/PATCH /countries/{id}
     *
     * @param  int $id
     * @param UpdateCountryAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCountryAPIRequest $request)
    {
        $input = $request->all();

        /** @var \Juanfv2\BaseCms\Models\Country $country */
        $country = $this->countryRepository->findWithoutFail($id);

        if (empty($country)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Country']));
        }

        $country = $this->countryRepository->update($input, $id);

        // $country = new GenericResource(country);

        return ['id' => $country->id];
    }

    /**
     * Remove the specified Country from storage.
     * DELETE /countries/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var \Juanfv2\BaseCms\Models\Country $country */
        $country = $this->countryRepository->findWithoutFail($id);

        if (empty($country)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Country']));
        }

        $country->delete();

        return $this->sendResponse($id, __('validation.model.deleted', ['model' => 'Country']));
    }
}
