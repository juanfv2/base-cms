<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\AppBaseController;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ZResetPasswordController extends AppBaseController
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $input = $this->validate($request, $this->rules());

        $email = $input['email'];

        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            return $this->sendError(__('passwords.user'));
        }
        $resetTable = 'password_resets';
        $reset = DB::table($resetTable)->where('email', $email)->first();

        $isValid = $reset && $reset->token == $input['token'];

        if ($isValid) {
            DB::delete("delete from `$resetTable` where email = ?", [$email]);

            $user->password = Hash::make($input['password']);
            $user->save();

            // return response()->json(['success' => $isValid, 'message' => __('passwords.reset')],  200);
            return $this->sendResponse(__('passwords.reset'));
        }

        // return response()->json(['success' => $isValid, 'message' => __('passwords.throttled')], 500);
        return $this->sendError(__('passwords.throttled'));
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }
}
