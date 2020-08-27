<?php

namespace Juanfv2\BaseCms\Models\Country;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *      definition="Country",
 *      required={"name", "code"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="code",
 *          description="code",
 *          type="string"
 *      )
 * )
 */
class Country extends Model
{
    public $table = 'countries';

    // use SoftDeletes;
    public $timestamps = false;
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
        $f = \Juanfv2\BaseCms\Models\Auth\XFile::where('entity', $this->table)
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
