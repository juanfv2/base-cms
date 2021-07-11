<?php

namespace App\Repositories\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Person;
use App\Models\Auth\Account;
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
            $r = $this->create($input);

            $input['user_id'] = $r->id;
            Account::create($input);
            return $r;
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
