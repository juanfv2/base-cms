<?php

namespace Juanfv2\BaseCms\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

/**
 * Class ZRegisterAPIController
 * @package Juanfv2\BaseCms\Controllers\Auth
 */
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
     * @return Response
     *
     * @SWG\Post(
     *      path="/password/email",
     *      summary="Store a newly created Account in storage",
     *      tags={"Password"},
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
     *                  property="email",
     *                  type="string"
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
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
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
            'message' => __($response)
        ], $isValid ? 200 : 500);
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
