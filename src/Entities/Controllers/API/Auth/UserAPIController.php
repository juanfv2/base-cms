<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Person;
use App\Models\Auth\Account;

use Illuminate\Http\Request;
use App\Repositories\Auth\UserRepository;
use App\Http\Controllers\AppBaseController;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\API\Auth
 */
class UserAPIController extends AppBaseController
{
    /** @var  UserRepository */
    public $modelRepository;
    public $rules;
    public $modelNameCamel = 'User';

    public function __construct(UserRepository $modelRepo)
    {
        $this->modelRepository = $modelRepo;
        $this->rules = User::$rules + Person::$rules;
    }

    /**
     * Store a newly created Person in storage.
     * POST /people
     *
     * @param CreatePersonAPIRequest $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $withEntity = $request->get('withEntity', '-');

        switch ($withEntity) {
            case 'auth_people':
                $this->rules = $this->rules + Person::$rules;
                break;
            default:
                $this->rules = $this->rules + Account::$rules;
                break;
        }

        $input = $this->validate($request, $this->rules);

        $model = $this->modelRepository->withAdditionalInfo('create', $input);

        if ($request->hasFile('photo')) {
            return $this->fileUpload($request, 'auth_users', 'photo', $model->id, 0);
        }

        return $this->sendResponse(['id' => $model->id], __('validation.model.stored', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Update the specified Person in storage.
     * PUT/PATCH /people/{id}
     *
     * @param int $id
     * @param UpdatePersonAPIRequest $request
     *
     * @return Response
     */
    public function update($id, Request $request)
    {
        $withEntity = $request->get('withEntity', '-');

        switch ($withEntity) {
            case 'auth_people':
                $this->rules = $this->rules + Person::$rules;
                break;
            default:
                $this->rules = $this->rules + Account::$rules;
                break;
        }

        $this->rules['email']    = 'required|string|max:191';
        $this->rules['password'] = 'min:6|confirmed';

        $input = $this->validate($request, $this->rules);
        // $input = $request->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }
        $model = $this->modelRepository->withAdditionalInfo('update', $input, $model);

        return $this->sendResponse(['id' => $model->id], __('validation.model.updated', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Remove the specified Person from storage.
     * DELETE /people/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $input = request()->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->modelRepository->findWithoutFail($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $resp = $this->modelRepository->withAdditionalInfo('delete', $input, $model);

        return $this->sendResponse(['id' => $id, 'success' => $resp], __('validation.model.deleted', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }
}
