<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Juanfv2\BaseCms\Models\Auth\User;
use Illuminate\Support\Facades\Schema;
use Juanfv2\BaseCms\Models\Auth\UserVerified;

use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Repositories\Auth\UserRepository;
use Juanfv2\BaseCms\Repositories\Auth\AccountRepository;
use Juanfv2\BaseCms\Requests\Auth\CreateAccountAPIRequest;
use Juanfv2\BaseCms\Notifications\UserRegisteredNotification;

/**
 * Class ZRegisterAPIController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */
class ZRegisterAPIController extends BaseCmsController
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
    function register(CreateAccountAPIRequest $request)
    {
        if ($request->has('uid')) {
            return $this->registerUserByUid($request);
        }
        return $this->registerUser($request);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    function registerUser(CreateAccountAPIRequest $request)
    {
        $info = $request->all();

        $roleId = $info['role_id'];

        // user
        $info['password'] = Hash::make($info['password']);
        $info['roles'] = [$roleId];
        $info['role_id'] = $roleId;
        $info['disabled'] = $request->get('uid', true);

        // $info['country_id'] = 194;
        // $info['region_id'] = 3224;
        // $info['city_id'] = 2317133;

        // -- // 194        sv
        // -- // 3224       ss
        // -- // 2317133    ss

        try {

            Schema::disableForeignKeyConstraints();
            DB::beginTransaction();

            $this->user = $this->userRepository->create($info);
            $newAccount = $this->accountRepository->create($info);

            if (!$request->has('uid')) {
                UserVerified::create(['user_id' => $this->user->id, 'token' => Str::random(40)]);
                $this->user->notify(new UserRegisteredNotification($this->user));
            }

            DB::commit();
            Schema::enableForeignKeyConstraints();

            return $this->sendResponse(__('messages.mail.verify', ['email' => $this->user->email]), [
                'id' => $newAccount->id,
                'detail' => __('messages.mail.verifyTitle', ['email' => $this->user->email])
            ]);
        } catch (\PDOException $e) {
            // logger(__FILE__ . ':' . __LINE__ . ' $e ', [$e]);
            // Woopsy
            DB::rollBack();
            Schema::enableForeignKeyConstraints();

            return $this->sendError(
                __('validation.model.error', ['model' => __('models.account.name')]),
                500,
                [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Database\Eloquent\Model|null|static
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    function registerUserByUid(Request $request)
    {
        $this->user = User::where('uid', $request->get('uid'))->first();

        if (!$this->user) {
            $this->registerUser($request);
        }

        if ($this->user instanceof User) {
            $this->user->remember_token = $this->user->createToken($this->user->id . '-token')->accessToken;

            $user = new GenericResource($this->user);

            return $user;
        }

        return $this->user;
    }

    function verifyUser($token)
    {
        $verifyUser = UserVerified::where('token', $token)->first();

        $isValid = false;
        if (isset($verifyUser)) {

            $user = $verifyUser->user;
            if (isset($user) && $user->disabled) {
                $user->disabled = 0;
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

        return response()->json(
            [
                'success' => $isValid,
                'title' => __('messages.mail.welcome', ['app_name' => config('app.name')]),
                'description' => $response
            ],
            $isValid ? 200 : 500
        );
    }

    function registered(Request $request, $user)
    {
        $this->guard()->logout();
        return redirect('/login')->with('status', __('messages.mail.check'));
    }
}
