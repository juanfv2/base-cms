<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Auth/Person
 * @package Juanfv2\BaseCms\Models
 * @version August 15, 2019, 2:51 pm CST
 *
 * @property \Juanfv2\BaseCms\Models\City city
 * @property \Juanfv2\BaseCms\Models\Country country
 * @property \Juanfv2\BaseCms\Models\Region region
 * @property \Illuminate\Database\Eloquent\Collection authRolesHasPermissions
 * @property \Illuminate\Database\Eloquent\Collection authUsersHasRoles
 * @property \Illuminate\Database\Eloquent\Collection cities
 * @property string firstName
 * @property string lastName
 * @property string phone
 * @property string cellPhone
 * @property date birthDate
 * @property string email
 * @property string address
 * @property string neighborhood
 * @property integer country_id
 * @property integer region_id
 * @property integer city_id
 * @property integer createdBy
 * @property integer updatedBy
 */
class Person extends Model
{
    use SoftDeletes;

    public $table = 'auth_people';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'firstName',
        'lastName',
        'phone',
        'cellPhone',
        'birthDate',
        'email',
        'address',
        'neighborhood',
        'country_id',
        'region_id',
        'city_id',
        'createdBy',
        'updatedBy'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'firstName' => 'string',
        'lastName' => 'string',
        'phone' => 'string',
        'cellPhone' => 'string',
        'birthDate' => 'date',
        'email' => 'string',
        'address' => 'string',
        'neighborhood' => 'string',
        'country_id' => 'integer',
        'region_id' => 'integer',
        'city_id' => 'integer',
        'createdBy' => 'integer',
        'updatedBy' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rulesCreate = [
        'email' => 'required|string|email|max:255|unique:auth_people',
        'password' => 'required|string|min:6', // |confirmed
        'firstName' => 'required',
        'lastName' => 'required'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rulesUpdate = [
        'password' => 'min:6', //|confirmed
        'firstName' => 'required',
        'lastName' => 'required',
        'email' => 'required'
    ];

    protected $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @return array
     */
    public function getPhotoUrlAttribute()
    {
        $f = XFile::where('entity', $this->table)
            ->where('field', 'photoUrl')
            ->where('entity_id', $this->id)
            ->first();
        // logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
        return $f;
    }


    public function user()
    {
        return $this->hasOne(User::class, 'email', 'email');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function country()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Country\Country::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function region()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Country\Region::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function city()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Country\City::class);
    }
}
