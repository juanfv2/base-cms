<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Person;
use App\Models\Auth\Account;

use Illuminate\Http\Request;
use App\Repositories\Auth\UserRepository;
use App\Http\Controllers\AppBaseController;
use App\Models\Driver;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\API\Auth
 */
class UserAPIController extends AppBaseController
{
    /** @var \App\Models\Auth\User */
    public $model;
    public $rules;
    public $modelNameCamel = 'User';

    public function __construct(User $model)
    {
        $this->model = $model;
        $this->rules = $model::$rules;
    }

    /**
     * Store a newly created Person in storage.
     * POST /people
     *
     * @param Request $request
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
            case 'drivers':
                $this->rules = $this->rules + Driver::$rules;
                break;
            default:
                $this->rules = $this->rules + Account::$rules;
                break;
        }

        $input = $this->validate($request, $this->rules);

        $model = $this->model->withAdditionalInfo('create', $input);

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

        $this->rules['email']     = 'required|string|max:191|unique:auth_users,email,' . $id;
        $this->rules['password'] = 'min:6|confirmed';

        $input = $this->validate($request, $this->rules);
        // $input = $request->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }
        $model = $model->withAdditionalInfo('update', $input, $model);

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
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        if ($model->driver) {
            $input['withEntity'] = 'drivers';
        }
        if ($model->person) {
            $input['withEntity'] = 'auth_people';
        }
        if ($model->account) {
            $input['withEntity'] = 'auth_accounts';
        }

        $resp = $model->withAdditionalInfo('delete', $input, $model);

        return $this->sendResponse(['id' => $id, 'success' => $resp], __('validation.model.deleted', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }
}
