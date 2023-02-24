<?php

namespace App\Models\Country;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

class Country extends Model
{
    use BaseCmsModel;
    use UserResponsible;
    use SoftDeletes;
    use HasFactory;

    public $table = 'countries';

    public $fillable = [
        'name',
        'code',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
    ];

    public static $rules = [
        'name' => 'required|string',
        'code' => 'required|string',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
    ];

    public $hidden = [
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function regions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Country\Region::class, 'country_id');
    }

    public function cities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Country\City::class, 'country_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Auth\User::class, 'country_id');
    }
}
