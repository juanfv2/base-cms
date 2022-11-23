<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use App\Models\Auth\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class ZRegisterAPIController
 */
class ZForgotPasswordController extends AppBaseController
{
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
            Carbon::now(),
        ]);

        $user->sendPasswordResetNotification($token);

        // return response()->json(['success' => true, 'message' => __('passwords.sent')], 200);
        return $this->sendResponse([], __('passwords.sent'));
    }
}
