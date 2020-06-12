<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Juanfv2\BaseCms\Resources\GenericResource;
use App\Criteria\RequestGenericCriteria;
use Juanfv2\BaseCms\Repositories\Auth\UserRepository;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Repositories\Auth\PersonRepository;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Juanfv2\BaseCms\Requests\Auth\CreatePersonAPIRequest;
use Juanfv2\BaseCms\Requests\Auth\UpdatePersonAPIRequest;

/**
 * Class PersonController
 * @package Juanfv2\BaseCms\Controllers\API
 */

class PersonAPIController extends BaseCmsController
{
    /** @var  PersonRepository */
    private $personRepository;
    /** @var  UserRepository */
    private $userRepository;

    public function __construct(PersonRepository $personRepo, UserRepository $userRepo)
    {
        $this->personRepository = $personRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the Person.
     * GET|HEAD /people
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $action = $request->get('action', '-');
        $criteria = new RequestGenericCriteria($request);

        $this->personRepository->pushCriteria($criteria);
        $itemCount = $this->personRepository->count();

        if ($action != 'export') {
            $this->personRepository->pushCriteria(new LimitOffsetCriteria($request));
        }

        $people = $this->personRepository->all();

        /* */
        $items = GenericResource::collection($people);
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
     * Store a newly created Person in storage.
     * POST /people
     *
     * @param CreatePersonAPIRequest $request
     *
     * @return Response
     */
    public function store(CreatePersonAPIRequest $request)
    {
        $input = $request->all();

        $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);

        try {

            DB::beginTransaction();

            $this->userRepository->create($input);
            $person = $this->personRepository->create($input);
            // $person = new GenericResource($person);

            DB::commit();

            return ['id' => $person->id];
        } catch (\PDOException $e) {
            logger(__FILE__ . ':' . __LINE__ . ' $e ', [$e->getMessage()]);
            // Woopsy
            DB::rollBack();
            return $this->sendError(__('validation.model.error', ['model' => 'Persona']));
        }
    }

    /**
     * Display the specified Person.
     * GET|HEAD /people/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var \Juanfv2\BaseCms\Models\Person $person */
        $person = $this->personRepository->findWithoutFail($id);

        if (empty($person)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Person']));
        }
        $person = new GenericResource($person);

        return $person;
    }

    /**
     * Update the specified Person in storage.
     * PUT/PATCH /people/{id}
     *
     * @param  int $id
     * @param UpdatePersonAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdatePersonAPIRequest $request)
    {
        $input = $request->all();

        /** @var \Juanfv2\BaseCms\Models\Person $person */
        $person = $this->personRepository->findWithoutFail($id);

        if (empty($person)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Persona']));
        }

        try {

            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();

            $userId = $input['user_id'];
            if (request()->has('password')) {
                $input['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }

            $this->userRepository->update($input, $userId);
            $person = $this->personRepository->update($input, $id);

            // $person = new GenericResource(person);

            DB::commit();
            Schema::enableForeignKeyConstraints();

            return ['id' => $person->id];
        } catch (\PDOException $e) {
            logger(__FILE__ . ':' . __LINE__ . ' $e ', [$e->getMessage()]);
            // Woopsy
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            return $this->sendError(__('validation.model.error', ['model' => 'Persona']));
        }
    }

    /**
     * Remove the specified Person from storage.
     * DELETE /people/{id}
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {

        /** @var \Juanfv2\BaseCms\Models\Person $person */
        $person = $this->personRepository->findWithoutFail($id);

        if (empty($person)) {
            return $this->sendError(__('validation.model.not.found', ['model' => 'Persona']));
        }
        try {

            DB::beginTransaction();

            $person->user->delete();
            $person->delete();

            DB::commit();

            return $this->sendResponse($id, __('validation.model.deleted', ['model' => 'Persona']));
        } catch (\PDOException $e) {
            // Woopsy
            DB::rollBack();

            return $this->sendError(__('validation.model.not.found', ['model' => 'Persona']));
        }
    }
}
