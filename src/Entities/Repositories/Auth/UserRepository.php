<?php

namespace App\Repositories\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Person;
use Illuminate\Support\Str;
use App\Models\Auth\Account;
use App\Models\Auth\XUserVerified;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class UserRepository
 * @package App\Repositories
 * @version September 8, 2020, 4:57 pm UTC
 */
class UserRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return User::class;
    }

    public function withAdditionalInfo($type, $input, $model = null)
    {
        $entity = "{$input['withEntity']}_{$type}_with";

        return $this->$entity($input, $model);
    }

    public function auth_people_create_with($input, $model)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles']    = is_string($input['roles']) ? json_decode($input['roles']) : $input['roles'];
            $r = $this->create($input);

            $input['user_id'] = $r->id;
            Person::create($input);
            return $r;
        });
    }

    public function auth_people_update_with($input, $model)
    {
        return DB::transaction(function () use ($model, $input) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $model = $this->update($model, $input);
            $model->person->update($input);

            Schema::enableForeignKeyConstraints();

            return $model;
        });
    }

    public function auth_people_delete_with($input, $model)
    {
        return DB::transaction(function () use ($model) {
            $model->delete();
            return $model->person->delete();
        });
    }

    public function auth_accounts_create_with($input, $model)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles']    = is_string($input['roles']) ? json_decode($input['roles']) : $input['roles'];
            $user = $this->create($input);

            $input['user_id'] = $user->id;
            Account::create($input);

            if (!isset($input['uid'])) {
                XUserVerified::create(['user_id' => $user->id, 'token' => Str::random(40)]);
                $user->notify(new \App\Notifications\UserRegisteredNotification($user));
            }

            return $user;
        });
    }

    public function auth_accounts_update_with($input, $model)
    {
        return DB::transaction(function () use ($input, $model) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $model = $this->update($model, $input);
            $model->account->update($input);

            Schema::enableForeignKeyConstraints();

            return $model;
        });
    }

    public function auth_accounts_delete_with($input, $model)
    {
        return DB::transaction(function () use ($model) {
            $model->delete();
            return $model->account->delete();
        });
    }
}
