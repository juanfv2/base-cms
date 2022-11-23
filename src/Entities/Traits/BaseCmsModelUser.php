<?php

namespace App\Traits;

use App\Models\Auth\Account;
use App\Models\Auth\Person;
use App\Models\Driver;
use App\Models\Misc\XUserVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait BaseCmsModelUser
{
    public function withAdditionalInfo($type, $input)
    {
        $entity = "{$input['withEntity']}_{$type}_with";

        return $this->$entity($input);
    }

    /* -------------------------------------------------------------------------- */
    /* person                                                                     */
    /* -------------------------------------------------------------------------- */

    public function auth_people_create_with($input)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles'] = is_string($input['roles']) ? json_decode($input['roles'], null, 512, JSON_THROW_ON_ERROR) : $input['roles'];
            $user = $this->mSave($input);

            $input['id'] = $user->id;
            $input['user_id'] = $user->id;
            Person::create($input);

            return $user;
        });
    }

    public function auth_people_update_with($input)
    {
        return DB::transaction(function () use ($input) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $updated = $this->mSave($input);
            $this->person->update($input);

            Schema::enableForeignKeyConstraints();

            return $updated;
        });
    }

    public function auth_people_delete_with()
    {
        return DB::transaction(function () {
            $this->email = $this->email.'-deleted-'.$this->id.'-'.time();
            $this->save();
            if ($this->person) {
                $this->person->delete();
            }

            return $this->delete();
        });
    }

    /* -------------------------------------------------------------------------- */
    /* account                                                                    */
    /* -------------------------------------------------------------------------- */

    public function auth_accounts_create_with($input)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles'] = is_string($input['roles']) ? json_decode($input['roles'], null, 512, JSON_THROW_ON_ERROR) : $input['roles'];
            $user = $this->mSave($input);

            $input['id'] = $user->id;
            $input['user_id'] = $user->id;
            Account::create($input);

            if (! isset($input['uid'])) {
                XUserVerified::create(['user_id' => $user->id, 'token' => Str::random(40)]);
                $user->notify(new \App\Notifications\UserRegisteredNotification($user));
            }

            return $user;
        });
    }

    public function auth_accounts_update_with($input)
    {
        return DB::transaction(function () use ($input) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $updated = $this->mSave($input);
            $this->account->update($input);

            Schema::enableForeignKeyConstraints();

            return $updated;
        });
    }

    public function auth_accounts_delete_with()
    {
        return DB::transaction(function () {
            $this->email = $this->email.'-deleted-'.$this->id.'-'.time();
            $this->save();
            if ($this->account) {
                $this->account->delete();
            }

            return $this->delete();
        });
    }

    /* -------------------------------------------------------------------------- */
    /* driver                                                                     */
    /* -------------------------------------------------------------------------- */

    public function drivers_create_with($input)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles'] = is_string($input['roles']) ? json_decode($input['roles'], null, 512, JSON_THROW_ON_ERROR) : $input['roles'];
            $user = $this->mSave($input);

            $input['id'] = $user->id;
            $input['user_id'] = $user->id;
            Driver::create($input);

            // if (!isset($input['uid'])) {
            //     XUserVerified::create(['user_id' => $user->id, 'token' => Str::random(40)]);
            //     $user->notify(new \App\Notifications\UserRegisteredNotification($user));
            // }

            return $user;
        });
    }

    public function drivers_update_with($input)
    {
        return DB::transaction(function () use ($input) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $user = $this->mSave($input);
            $user->driver->update($input);

            Schema::enableForeignKeyConstraints();

            return $user;
        });
    }

    public function drivers_delete_with()
    {
        return DB::transaction(function () {
            $this->email = $this->email.'-deleted-'.$this->id.'-'.time();
            $this->save();
            if ($this->driver) {
                $this->driver->delete();
            }

            return $this->delete();
        });
    }
}
