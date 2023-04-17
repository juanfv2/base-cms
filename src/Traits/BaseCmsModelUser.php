<?php

namespace Juanfv2\BaseCms\Traits;

use App\Models\Auth\Account;
use App\Models\Auth\Person;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

trait BaseCmsModelUser
{
    public function withAdditionalInfo($type, $input)
    {
        $entity = "{$input['withEntity']}_{$type}_with";

        return $this->$entity($input);
    }

    public function auth_people_create_with($input)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles'] = is_string($input['roles']) ? json_decode($input['roles'], null, 512, JSON_THROW_ON_ERROR) : $input['roles'];
            $r = $this->mSave($input);

            $input['id'] = $r->id;
            $input['user_id'] = $r->id;
            $person = new Person;
            $person->mSave($input);

            return $r;
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
            $this->person->mSave($input);

            Schema::enableForeignKeyConstraints();

            return true;
        });
    }

    public function auth_accounts_create_with($input)
    {
        return DB::transaction(function () use ($input) {
            $input['password'] = Hash::make($input['password']);
            $input['roles'] = is_string($input['roles']) ? json_decode($input['roles'], null, 512, JSON_THROW_ON_ERROR) : $input['roles'];
            $r = $this->mSave($input);

            $input['id'] = $r->id;
            $input['user_id'] = $r->id;
            $account = new Account;
            $account->mSave($input);

            if (! isset($input['uid'])) {
                $r->verifyUser();
            }

            return $r;
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
            $this->account->mSave($input);

            Schema::enableForeignKeyConstraints();

            return true;
        });
    }

    public function deleteAuthUser()
    {
        return DB::transaction(function () {
            $this->email = $this->email.'-deleted-'.$this->id.'-'.time();
            $this->save();
            if ($this->person) {
                $this->person->delete();
            }
            if ($this->account) {
                $this->account->delete();
            }

            return $this->delete();
        });
    }
}
