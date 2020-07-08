<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;
use Juanfv2\BaseCms\Models\Auth\XFile;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *      definition="Account",
 *      required={"firstName", "lastName", "email", "name", "password", "role_id"},
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="password",
 *          description="password",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="role_id",
 *          description="role_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="uid",
 *          description="uid",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="firstName",
 *          description="firstName",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="lastName",
 *          description="lastName",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="phone",
 *          description="phone",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="cellPhone",
 *          description="cellPhone",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="birthDate",
 *          description="birthDate",
 *          type="string",
 *          format="date"
 *      ),
 *      @SWG\Property(
 *          property="email",
 *          description="email",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="address",
 *          description="address",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="neighborhood",
 *          description="neighborhood",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="country_id",
 *          description="country_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="region_id",
 *          description="region_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="city_id",
 *          description="city_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="createdBy",
 *          description="createdBy",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="updatedBy",
 *          description="updatedBy",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="deleted_at",
 *          description="deleted_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class Account extends Model
{
    use SoftDeletes;

    public $table = 'auth_accounts';

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
        'firstName' => 'required',
        'lastName'  => 'required',
        'email'     => 'required|string|email|max:255|unique:auth_accounts',
        'password'  => 'required|string|min:6', // |confirmed // <== for auth_user
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rulesUpdate = [
        'firstName' => 'required',
        'lastName'  => 'required',
        'email'     => 'required',
        'password'  => 'min:6', // |confirmed // <== for auth_user
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

    /**
     * @return array
     */
    public function getImagesAttribute()
    {
        $f = XFile::where('entity', $this->table)
            ->where('field', 'images')
            ->where('entity_id', $this->id)
            ->get();
        // logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
        return $f;
    }

    public function user()
    {
        return $this->hasOne(\Juanfv2\BaseCms\Models\Auth\User::class, 'email', 'email');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function city()
    {
        return $this->belongsTo(\Juanfv2\BaseCms\Models\Country\City::class);
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
}
