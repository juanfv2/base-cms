<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Juanfv2\BaseCms\Models\Auth\User;
use Juanfv2\BaseCms\Resources\GenericResource;
use Juanfv2\BaseCms\Requests\Auth\LoginRequest;


/**
 * Class LoginAPIController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */
class ZLoginAPIController extends Controller
{

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

        $user = $this->attemptLogin($request);

        if ($user instanceof User) {
            $user = new GenericResource($user);
        }

        return $user;
    }

    public function logout()
    {
        $r = $this->attemptLogout();
        return response()->json(['bye' => $r], 204);
    }

    // Utilities

    /**
     * Attempt to create an access token using user credentials
     * @param $request
     * @return User | \Illuminate\Http\Request  $request
     */
    public function attemptLogin($request)
    {
        $credentials = request(['email', 'password']);

        $errors = ['message' => __('auth.failed')];

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => __('passwords.user')], 404);
        }

        $user = $request->user();

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
    public function attemptLogout()
    {
        return Auth::user()->token()->revoke();
    }
}
