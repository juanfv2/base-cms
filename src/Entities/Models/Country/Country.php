<?php

namespace App\Models\Country;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Country
 *
 * @package App\Models\Country
 * @version July 18, 2021, 1:48 am UTC
 */
class Country extends Model
{
    use HasFactory;

    public $table = 'countries';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'name',
        'code'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'code' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:10'
    ];

    public $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function users()
    {
        return $this->hasMany(\App\Models\Auth\User::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function regions()
    {
        return $this->hasMany(\App\Models\Country\Region::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function cities()
    {
        return $this->hasMany(\App\Models\Country\City::class, 'country_id');
    }
}