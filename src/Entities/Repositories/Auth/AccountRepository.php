<?php

namespace App\Repositories\Auth;

use Illuminate\Support\Str;
use App\Models\Auth\Account;
use App\Models\Auth\XUserVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Notifications\UserRegisteredNotification;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class AccountRepository
 * @package App\Repositories
 * @version September 8, 2020, 4:57 pm UTC
 */

class AccountRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Account::class;
    }

    public function registerAccountWithUser($userRepository, $input)
    {
        return DB::transaction(function () use ($userRepository, $input) {
            // Schema::disableForeignKeyConstraints();

            $input['password'] = Hash::make($input['password']);
            $input['roles']    = is_string($input['roles']) ? json_decode($input['roles']) : $input['roles'];

            $user = $userRepository->create($input);
            $account = $this->create($input);

            if (!isset($input['uid'])) {
                XUserVerified::create(['user_id' => $user->id, 'token' => Str::random(40)]);
                $user->notify(new UserRegisteredNotification($user));
            }

            // Schema::enableForeignKeyConstraints();
            return $account;
        });
    }

    public function createAccountWithUser($userRepository, $input)
    {
        return DB::transaction(function () use ($userRepository, $input) {

            $input['password'] = Hash::make($input['password']);
            $input['roles']    = is_string($input['roles']) ? json_decode($input['roles']) : $input['roles'];

            $userRepository->create($input);
            return $this->create($input);
        });
    }

    public function updateAccountAndUser($model, $userRepository, $input)
    {
        return DB::transaction(function () use ($model, $userRepository, $input) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $userRepository->update($model->user, $input);
            $model = $this->update($model, $input);

            Schema::enableForeignKeyConstraints();

            return $model;
        });
    }

    public function deleteAccountAndUser($model)
    {
        return DB::transaction(function () use ($model) {

            $model->user->delete();
            return $model->delete();
        });
    }
}
