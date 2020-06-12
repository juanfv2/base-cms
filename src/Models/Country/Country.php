<?php

namespace Juanfv2\BaseCms\Models\Country;

use Eloquent as Model;
use Juanfv2\BaseCms\Models\Auth\XFile;
// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Country
 * @package Juanfv2\BaseCms\Models
 * @version July 13, 2019, 1:54 pm CST
 *
 * @property \Illuminate\Database\Eloquent\Collection authPeople
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property \Illuminate\Database\Eloquent\Collection cities
 * @property \Illuminate\Database\Eloquent\Collection hosts
 * @property \Illuminate\Database\Eloquent\Collection regions
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property string name
 * @property string code
 * @property string flag
 */
class Country extends Model
{
    // use SoftDeletes;
    public $timestamps = false;

    public $table = 'countries';

    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';
    // protected $dates = ['deleted_at'];


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
        'name' => 'required',
        'code' => 'required'
    ];

    /**
     * @return array
     */
    public function getFlagAttribute()
    {
        $f = XFile::where('entity', $this->table)
            ->where('field', 'flag')
            ->where('entity_id', $this->id)
            ->first();
        // logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
        return $f;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function people()
    {
        return $this->hasMany(\Juanfv2\BaseCms\Models\Auth\Person::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function regions()
    {
        return $this->hasMany(\Juanfv2\BaseCms\Models\Country\Region::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function cities()
    {
        return $this->hasMany(\Juanfv2\BaseCms\Models\Country\City::class);
    }
}
