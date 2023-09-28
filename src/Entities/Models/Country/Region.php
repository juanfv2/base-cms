<?php

namespace App\Models\Country;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

class Region extends Model
{
    use BaseCmsModel;
    use HasFactory;
    use SoftDeletes;
    use UserResponsible;

    public $table = 'regions';

    public $fillable = [
        'name',
        'code',
        'country_id',
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
        'country_id' => 'required',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
    ];

    public $hidden = [
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Country\Country::class, 'country_id');
    }

    public function cities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Country\City::class, 'region_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Auth\User::class, 'region_id');
    }
}
