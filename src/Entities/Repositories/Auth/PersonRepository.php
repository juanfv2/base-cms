<?php

namespace App\Repositories\Auth;

use App\Models\Auth\Person;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

use Juanfv2\BaseCms\Repositories\BaseRepository;

/**
 * Class PersonRepository
 * @package App\Repositories
 * @version September 8, 2020, 4:57 pm UTC
 */

class PersonRepository extends BaseRepository
{
    /**
     * Configure the Model
     **/
    public function model()
    {
        return Person::class;
    }

    public function createPersonWithUser($userRepository, $input)
    {
        return DB::transaction(function () use ($userRepository, $input) {

            $input['password'] = Hash::make($input['password']);
            $input['roles']    = is_string($input['roles']) ? json_decode($input['roles']) : $input['roles'];

            $userRepository->create($input);
            return $this->create($input);
        });
    }

    public function updatePersonAndUser($model, $userRepository, $input)
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

    public function deletePersonAndUser($model)
    {
        return DB::transaction(function () use ($model) {

            $model->user->delete();
            return $model->delete();
        });
    }
}
