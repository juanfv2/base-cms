<?php

namespace App\Models\Country;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class City
 *
 * @version October 19, 2022, 9:04 pm UTC
 */
class City extends Model
{
    use BaseCmsModel,
        UserResponsible,
        HasFactory;

    public $table = 'cities';

    public $fillable = [
        'name',
        'latitude',
        'longitude',
        'country_id',
        'region_id',
        'createdBy',
        'updatedBy',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'country_id' => 'integer',
        'region_id' => 'integer',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:191',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'country_id' => 'nullable',
        'region_id' => 'nullable',
        'createdBy' => 'nullable',
        'updatedBy' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
    ];

    public $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function users()
    {
        return $this->hasMany(\App\Models\Auth\User::class, 'city_id');
    }
}
