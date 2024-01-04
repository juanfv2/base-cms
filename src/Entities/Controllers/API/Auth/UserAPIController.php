<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\Account;
use App\Models\Auth\Person;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Juanfv2\BaseCms\Controllers\AppBaseController;

/**
 * Class PersonController
 */
class UserAPIController extends AppBaseController
{
    /** @var \App\Models\Auth\User */
    public $model;

    public $modelNameCamel = 'User';

    public $rules;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Store a newly created Person in storage.
     * POST /people
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if ($request->has('to_index')) {
            return $this->index($request);
        }

        $withEntity = $request->get('withEntity', '-');

        $this->rules = match ($withEntity) {
            'auth_people' => $this->model::$rules + Person::$rules,
            default => $this->model::$rules + Account::$rules,
        };

        $input = $this->validate($request, $this->rules);

        $mType = match ($withEntity) {
            'auth_people' => \App\Models\Auth\Person::class,
            default => \App\Models\Auth\Account::class,
        };

        $model = $this->model->createAuthUser($input, $mType);

        if ($request->hasFile('photo')) {
            return $this->fileUpload($request, 'auth_users', 'photo', $model->id, 0);
        }

        return $this->sendResponse(['id' => $model->id], __('validation.model.stored', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }

    /**
     * Update the specified Person in storage.
     * PUT/PATCH /people/{id}
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id, Request $request)
    {
        $withEntity = $request->get('withEntity', '-');

        $this->rules = match ($withEntity) {
            'auth_people' => $this->model::$rules + Person::$rules,
            default => $this->model::$rules + Account::$rules,
        };

        $this->rules['email'] = 'required|string|max:191|unique:auth_users,email,'.$id;
        $this->rules['password'] = 'min:6|confirmed';

        $input = $this->validate($request, $this->rules);
        // $input = $request->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $mType = match ($model->role_id) {
            Role::_3_ACCOUNT => 'account',
            default => 'person',
        };

        $updated = $model->updateAuthUser($input, $mType);

        return $this->sendResponse(['id' => $model->id], __('validation.model.updated', ['model' => __("models.{$this->modelNameCamel}.name")]), $updated);
    }

    /**
     * Remove the specified Person from storage.
     * DELETE /people/{id}
     *
     * @param  int  $id
     * @return Response
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $input = request()->all();

        /** @var \App\Models\Auth\User $model */
        $model = $this->model->find($id);

        if (empty($model)) {
            return $this->sendError(__('validation.model.not.found', ['model' => __("models.{$this->modelNameCamel}.name")]));
        }

        $mType = match ($model->role_id) {
            Role::_3_ACCOUNT => 'account',
            default => 'person',
        };

        $resp = $model->deleteAuthUser($mType);

        return $this->sendResponse(['id' => $id, 'success' => $resp], __('validation.model.deleted', ['model' => __("models.{$this->modelNameCamel}.name")]));
    }
}
