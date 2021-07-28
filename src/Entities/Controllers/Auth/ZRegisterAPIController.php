<?php

namespace App\Http\Controllers\API\Auth;

use Carbon\Carbon;
use App\Models\Auth\User;
use Illuminate\Support\Str;

use App\Models\Auth\Account;
use Illuminate\Http\Request;
use App\Models\Auth\XUserVerified;

use App\Repositories\Auth\UserRepository;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Auth\AccountRepository;
use Juanfv2\BaseCms\Resources\GenericResource;

/**
 * Class ZRegisterAPIController
 * @package App\Http\Controllers\Auth
 */
class ZRegisterAPIController extends AppBaseController
{

    /** @var  UserRepository */
    private $userRepository;
    /** @var  User */
    private $user;

    function __construct(UserRepository $userRepo)
    {
        $this->userRepository = $userRepo;
    }

    function register(Request $request)
    {
        $rules      = User::$rules + Account::$rules;
        $rules['firstName'] = 'nullable';
        $rules['lastName']  = 'nullable';
        $rules['role_id']   = 'nullable';
        $rules['disabled']  = 'nullable|boolean';
        $this->validate($request, $rules);

        $input = $request->all();

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
     * @param Request $request
     * @return array
     */
    function registerUser($input)
    {
        $r = $this->createAccount($input);
        if ($r instanceof User) {

            // warning: show error
            return $this->sendResponse(
                [
                    'id' => $this->user->account->id,
                    'detail' => __('messages.mail.verifyTitle', ['email' => $this->user->email])
                ],
                __('messages.mail.verify', ['email' => $this->user->email])
            );
        }
        // warning: show error
        return $r;
    }

    /**
     * Si uid tiene un valor el usuario viene de red social, ej. facebook
     *
     * @param Array $request
     * @return $this|\Illuminate\Database\Eloquent\Model|null|static
     */
    function registerUserByUid($input)
    {
        $this->user = User::where('uid', $input['uid'])->first();

        // warning si el usuario no existe, crearlo
        if (empty($this->user)) {
            $this->createAccount($input);
        }

        if ($this->user instanceof User) {
            $this->user->api_token = base64_encode(Str::random(40));

            $user = new GenericResource($this->user);

            return $this->sendResponse($user, __('user.welcome', ['email' => $user->email]));
        }

        return $this->user;
    }

    function verifyUser($token)
    {
        $verifyUser = XUserVerified::where('token', $token)->first();

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
                'description' => $response
            ],
            __('messages.mail.welcome', ['app_name' => config('app.name')]),
            $isValid ? 200 : 500
        );
    }

    public function createAccount($input)
    {
        $roleId         = 3;
        $accountGroupId = 1;

        // user
        // $input['password']         = Hash::make($input['password']);
        $input['roles']            = [$roleId];
        $input['role_id']          = $roleId;
        $input['disabled']         = !isset($input['uid']);
        $input['account_group_id'] = $accountGroupId;
        $input['firstName']        = $input['name'];
        $input['lastName']         = '';

        // $input['country_id']   = 194;
        // $input['region_id']    = 3224;
        // $input['city_id']      = 2317133;

        // -- // 194        sv
        // -- // 3224       ss
        // -- // 2317133    ss

        $model = $this->userRepository->auth_accounts_create_with($input, null);

        $this->user = $model;

        return $this->user;
    }
}
