<?php

namespace Juanfv2\BaseCms\Traits;

use App\Models\Auth\Account;
use App\Models\Auth\Person;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait BaseCmsModelUser
{
    public function deleteAuthUser($type)
    {
        return DB::transaction(function () use ($type) {
            $this->email = $this->email . '-deleted-' . $this->id . '-' . time();
            $this->save();
            $this->$type->delete();

            return $this->delete();
        });
    }

    public function createAuthUser($input, $type)
    {
        return DB::transaction(function () use ($input, $type) {
            $input['email'] = Str::lower($input['email']);
            $input['password'] = Hash::make($input['password']);
            $input['roles'] = is_string($input['roles']) ? json_decode($input['roles'], null, 512, JSON_THROW_ON_ERROR) : $input['roles'];
            $r = $this->mSave($input);

            $input['id'] = $r->id;
            $input['user_id'] = $r->id;

            $mType = new $type;
            $mType->mSave($input);

            if (!isset($input['uid'])) {
                $r->verifyUser();
            }

            return $r;
        });
    }

    public function updateAuthUser($input, $type)
    {
        return DB::transaction(function () use ($input, $type) {
            Schema::disableForeignKeyConstraints();

            if (isset($input['email'])) {
                $input['email'] = Str::lower($input['email']);
            }

            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $updated = $this->mSave($input);
            $updated = $this->$type->mSave($input);

            Schema::enableForeignKeyConstraints();

            return $updated;
        });
    }
}
