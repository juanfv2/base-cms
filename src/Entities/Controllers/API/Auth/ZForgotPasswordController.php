<?php

namespace App\Http\Controllers\API\Auth;

use Carbon\Carbon;
use App\Models\Auth\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AppBaseController;

/**
 * Class ZRegisterAPIController
 * @package App\Controllers\Auth
 */
class ZForgotPasswordController extends AppBaseController
{

    /**
     * @param Request $request
     * @return Response
     *
     * @SWG\Post(
     *      path="/password/email",
     *      summary=" Account forgot email",
     *      tags={"Password"},
     *      description="forgot email",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Email",
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
        $this->validate($request, ['email' => 'required|email']);

        $email = $request['email'];

        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return $this->sendError(__('passwords.user'));
        }
        $resetTable = 'password_resets';

        $reset = DB::table($resetTable)->where('email', $email)->first();


        if ($reset) {
            $expire = 60; // min
            $created_at = Carbon::parse($reset->created_at);
            $expireTime = $created_at->clone()->add($expire, 'minute');
            $now = Carbon::now();

            // dd($reset, $created_at->toString(), $expireTime->toString(), $now->toString(), $now < $expireTime);

            if ($now < $expireTime) {
                return $this->sendError([], __('passwords.throttled'), 401);
            }
        }
        DB::delete("delete from `$resetTable` where email = ?", [$email]);

        $token = base64_encode(Str::random(60));

        DB::insert("insert into `$resetTable` (`email`, `token`, `created_at`) values (?, ?, ?)", [
            $email,
            $token,
            Carbon::now()
        ]);

        $user->sendPasswordResetNotification($token);

        // return response()->json(['success' => true, 'message' => __('passwords.sent')], 200);
        return $this->sendResponse([], __('passwords.sent'));
    }
}
