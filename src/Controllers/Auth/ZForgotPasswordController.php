<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Juanfv2\BaseCms\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ZForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
     */

    use SendsPasswordResetEmails;

    /**
     * @param Request $request
     * @return array
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        // return [
        //     'success' => $response == Password::RESET_LINK_SENT,
        //     'message' => __($response),
        //     'errors' => [__($response)],
        // ];
        $isValid = $response == Password::RESET_LINK_SENT;

        return response()->json([
            'success' => $isValid,
            'message' => __($response)], $isValid ? 200 : 500);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
