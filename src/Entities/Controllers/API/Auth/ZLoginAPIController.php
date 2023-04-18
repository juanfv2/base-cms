<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Juanfv2\BaseCms\Controllers\AppBaseController;
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
        return $this->sendResponse($r, __('user.bye'), true, 204);
    }

    // Utilities

    /**
     * Attempt to create an access token using user credentials
     *
     * @param $array
     * @return \App\Models\Auth\User|\Illuminate\Http\JsonResponse
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
            return $this->sendError([], __('auth.failed'), 422);
        }

        if ($user->disabled) {
            return $this->sendError([], __('auth.no.active'), 422);
        }

        $rCountry = request()->get('rCountry', request()->headers->get('r-country', '.l.'));
        $tokenName = "{$rCountry}-{$user->id}";
        $token = $user->createToken($tokenName);
        $user->remember_token = $token->plainTextToken;

        return $user;
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
            $user->tokens()->delete();
        }

        return [];
    }
}
