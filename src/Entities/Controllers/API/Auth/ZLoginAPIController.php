<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Juanfv2\BaseCms\Resources\GenericResource;

/**
 * Class LoginAPIController
 */
class ZLoginAPIController extends AppBaseController
{
    public function login(Request $request)
    {
        $data = $this->validate($request, ['password' => 'required', 'email' => 'required|email']);
        $user = $this->attemptLogin($data);

        if ($user instanceof User) {
            $user = new GenericResource($user);

            return $this->sendResponse($user, __('user.welcome'));
        }

        return $user;
    }

    public function logout()
    {
        $r = $this->attemptLogout();
        // return response()->json(['bye' => $r], 204);
        return $this->sendResponse(__('user.bye'), null, $r, 204);
    }

    // Utilities

    /**
     * Attempt to create an access token using user credentials
     *
     * @param $array
     * @return User | \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory $response
     */
    public function attemptLogin($request)
    {
        $email = $request['email'];
        $password = $request['password'];

        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return $this->sendError(__('passwords.user'));
        }

        if (! Hash::check($password, $user->password)) {
            return $this->sendError(__('auth.failed'), [], 422);
        }

        if ($user->disabled) {
            return $this->sendError(__('auth.no.active'), [], 422);
        }

        $expire = 60 * 24; // min
        $time = Carbon::now()->add($expire, 'minute')->timestamp;

        $t = "{$user->id}-{$time}";

        $user->api_token = Crypt::encryptString($t);

        // todo: get >>> $decrypted = Crypt::decryptString($user->api_token);

        if ($user->api_token) {
            $user->save();

            return $user;
        }

        return $this->sendError(__('auth.failed'), [], 422);
    }

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function attemptLogout()
    {
        /** @var \App\Models\Auth\User $user */
        $user = auth()->user();

        if (! is_null($user)) {
            $user->api_token = Str::random(70);

            return $user->save();
        }
        // return Auth::user()->token()->revoke();

        return [];
    }
}
