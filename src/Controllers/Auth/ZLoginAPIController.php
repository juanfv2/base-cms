<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Juanfv2\BaseCms\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Repositories\Auth\UserRepository;
use Juanfv2\BaseCms\Controllers\BaseCmsController;
use Juanfv2\BaseCms\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * Class LoginAPIController
 * @package Juanfv2\BaseCms\Controllers\API
 */
class ZLoginAPIController extends BaseCmsController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function getIp()
    {
        $arr = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        $server = $_SERVER ? $_SERVER : [];
        foreach ($arr as $key) {
            if (array_key_exists($key, $server) === true) {
                foreach (explode(',', $server[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }

    public function authenticate(LoginRequest $request)
    {
        $this->validateLogin($request);

        $user = $this->attemptLogin($request);
        // logger(__FILE__ . ':' . __LINE__ . ' $user ', [$user]);

        if ($user instanceof User) {
            $user = new GenericResource($user);
        }

        return $user;
    }

    public function logout()
    {
        $this->logoutApp();
        return response()->json('bye', 204);
    }

    // Utilities

    /**
     * Attempt to create an access token using user credentials
     * @param $request
     * @return User | \Illuminate\Http\Request  $request
     */
    public function attemptLogin($request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $errors = ['message' => __('auth.failed')];

        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return response()->json(['message' => __('passwords.user')], 404);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json($errors, 422);
        }

        if ($user->disabled) {
            return response()->json(['message' => __('auth.no.active')], 422);
        }

        $token = $user->createToken($user->id . '-token')->accessToken;

        if ($token) {
            $user->remember_token = $token;
            return $user;
        }

        return response()->json($errors, 422);
    }

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function logoutApp()
    {
        $accessToken = Auth::user()->token();
        $accessToken->revoke();
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }
}
