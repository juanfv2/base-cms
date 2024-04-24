<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\Account;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Juanfv2\BaseCms\Controllers\AppBaseController;
use Juanfv2\BaseCms\Resources\GenericResource;

/**
 * Class ZRegisterAPIController
 */
class ZRegisterAPIController extends AppBaseController
{
    public $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function register(Request $request)
    {
        $rules = array_merge(User::$rules, Account::$rules);
        $rules['first_name'] = 'nullable';
        $rules['last_name'] = 'nullable';
        $rules['role_id'] = 'nullable';
        $rules['disabled'] = 'nullable|boolean';

        $this->validate($request, $rules);

        $input = $request->all();

        // dd($input);
        if ($request->has('uid')) {
            return $this->registerUserByUid($input);
        }

        return $this->registerUser($input);
    }

    /**
     * Registrar usuario
     * y
     * enviar email si el registro fue correcto
     *
     * @param  Request  $request
     * @return array
     */
    public function registerUser($input)
    {
        $r = null;
        $message = '';
        switch (intval($input['role_id'])) {
            case Role::_3_ACCOUNT:
                $r = $this->createAccount($input);
                $message = __('messages.mail.verify', ['email' => $this->model->email]);
                break;
                // case 4:
                //     $r = $this->createDriver($input);
                //     $message = __('messages.mail.verify', ['email' => $this->model->email]);
                //     break;
            default:
                // code...
                break;
        }
        if ($r instanceof User) {
            return $this->sendResponse(['id' => $this->model->id, 'detail' => __('messages.mail.verifyTitle', ['email' => $this->model->email])], $message);
        }

        // warning: show error
        return $this->sendError(__('validation.model.error', ['model' => 'Usuario']));
    }

    /**
     * Si uid tiene un valor el usuario viene de red social, ej. Facebook
     *
     * @param  array  $request
     */
    public function registerUserByUid($input): static|\Illuminate\Database\Eloquent\Model|null
    {
        $this->model = User::where('uid', $input['uid'])->first();

        // warning si el usuario no existe, crearlo
        if (empty($this->model)) {
            $this->createAccount($input);
        }

        if ($this->model instanceof User) {
            $this->model->api_token = base64_encode(Str::random(40));

            $this->model = new GenericResource($this->model);

            return $this->sendResponse($this->model, __('user.welcome', ['email' => $this->model->email]));
        }

        return $this->model;
    }

    public function verifyUser(EmailVerificationRequest $request, $id, $hash)
    {

        $request->fulfill();

        $isValid = false;
        if (isset($verifyUser)) {
            $user = $verifyUser->user;
            if (isset($user) && $user->disabled) {
                $user->disabled = 0;
                $user->email_verified_at = Carbon::now();
                $user->save();
                $response = __('messages.mail.verified');
            } else {
                $response = __('messages.mail.verifiedAlready');
            }
            $isValid = true;
        } else {
            $response = __('messages.mail.verifiedFailed');
            $isValid = false;
        }

        return $this->sendResponse(
            [
                'success' => $isValid,
                'title' => __('messages.mail.welcome', ['app_name' => config('app.name')]),
                'description' => $response,
            ],
            __('messages.mail.welcome', ['app_name' => config('app.name')]),
            $isValid ? 200 : 500
        );
    }

    public function createAccount($input)
    {
        $roleId = Role::_3_ACCOUNT;
        $accountGroupId = 1;

        // user
        // $input['password']         = Hash::make($input['password']);
        $input['roles'] = [$roleId];
        $input['role_id'] = $roleId;
        $input['disabled'] = ! isset($input['uid']);
        // $input['account_group_id'] = $accountGroupId;
        $input['first_name'] = $input['name'];
        $input['last_name'] = '';

        // $input['country_id']   = 194;
        // $input['region_id']    = 3224;
        // $input['city_id']      = 2317133;

        // -- // 194        sv
        // -- // 3224       ss
        // -- // 2317133    ss

        $model = $this->model->createAuthUser($input, \App\Models\Auth\Account::class);

        $this->model = $model;

        return $this->model;
    }

    public function createDriver($input)
    {
        $roleId = 4;
        $accountGroupId = 1;

        // user
        // $input['password']         = Hash::make($input['password']);
        $input['roles'] = [$roleId];
        $input['role_id'] = $roleId;
        $input['disabled'] = ! isset($input['uid']);
        // $input['account_group_id'] = $accountGroupId;
        $input['first_name'] = $input['name'];
        $input['last_name'] = '';

        // $input['country_id']   = 194;
        // $input['region_id']    = 3224;
        // $input['city_id']      = 2317133;

        // -- // 194        sv
        // -- // 3224       ss
        // -- // 2317133    ss

        $model = $this->model->drivers_create_with($input, null);

        $this->model = $model;

        return $this->model;
    }
}
