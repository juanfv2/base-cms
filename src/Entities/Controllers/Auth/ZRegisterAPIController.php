<?php

namespace App\Http\Controllers\API\Auth;

use Carbon\Carbon;
use App\Models\Auth\User;
use Illuminate\Support\Str;
use App\Models\Auth\Account;
use Illuminate\Http\Request;
use App\Models\Auth\XUserVerified;

use App\Http\Resources\GenericResource;
use App\Repositories\Auth\UserRepository;
use App\Http\Controllers\AppBaseController;
use App\Repositories\Auth\AccountRepository;

/**
 * Class ZRegisterAPIController
 * @package App\Http\Controllers\Auth
 */
class ZRegisterAPIController extends AppBaseController
{

    /** @var  UserRepository */
    private $userRepository;
    /** @var  AccountRepository */
    private $accountRepository;
    /** @var  User */
    private $user;

    function __construct(UserRepository $userRepo, AccountRepository $customerRepo)
    {
        $this->userRepository = $userRepo;
        $this->accountRepository = $customerRepo;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/register",
     *      summary="Store a newly created Account in storage",
     *      tags={"Register"},
     *      description="Store Account",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Account that should be stored",
     *          required=false,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="password_confirmation",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="firstName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="lastName",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="email",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="uid",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="role_id",
     *                  type="integer"
     *              ),
     *          )
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
     *                  ref="#/definitions/Account"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
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
     * @throws \Prettus\Validator\Exceptions\ValidatorException
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
     * @param Request $request
     * @return $this|\Illuminate\Database\Eloquent\Model|null|static
     * @throws \Prettus\Validator\Exceptions\ValidatorException
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
            ['success' => $isValid, 'title' => __('messages.mail.welcome', ['app_name' => config('app.name')]), 'description' => $response],
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

        $model = $this->accountRepository->registerAccountWithUser($this->userRepository, $input);

        $this->user = $model->user;

        return $this->user;
    }
}
