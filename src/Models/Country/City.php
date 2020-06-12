<?php

namespace Juanfv2\BaseCms\Models\Country;

use Eloquent as Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class City
 * @package App\Models
 * @version July 13, 2019, 2:46 pm CST
 *
 * @property \Juanfv2\BaseCms\Models\Country country
 * @property \Juanfv2\BaseCms\Models\Region region
 * @property \Illuminate\Database\Eloquent\Collection authPeople
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property \Illuminate\Database\Eloquent\Collection hosts
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property integer region_id
 * @property integer country_id
 * @property float latitude
 * @property float longitude
 * @property string name
 */
class City extends Model
{
    // use SoftDeletes;
    public $timestamps = false;

    public $table = 'cities';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'region_id',
        'country_id',
        'latitude',
        'longitude',
        'name'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'region_id' => 'integer',
        'country_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'name' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'region_id' => 'required',
        'country_id' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'name' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function country()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Country\Country::class, 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function region()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Country\Region::class, 'region_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function people()
    {
        return $this->hasMany(\Juanfv2\BaseCms\Models\Auth\Person::class);
    }
}
