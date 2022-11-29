<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class Account
 *
 * @version April 1, 2021, 10:54 pm UTC
 */
class Account extends Model
{
    use BaseCmsModel,
        UserResponsible,
        HasFactory,
        SoftDeletes;

    public $incrementing = false;

    public $table = 'auth_accounts';

    public $fillable = [
        'id',
        'user_id',
        'firstName',
        'lastName',
        'cellPhone',
        'birthDate',
        'address',
        'neighborhood',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'firstName' => 'string',
        'lastName' => 'string',
        'cellPhone' => 'string',
        'birthDate' => 'date',
        'address' => 'string',
        'neighborhood' => 'string',
        'createdBy' => 'integer',
        'updatedBy' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id' => 'nullable',
        'user_id' => 'nullable',
        'firstName' => 'required|string|max:191',
        'lastName' => 'required|string|max:191',
        'cellPhone' => 'nullable|string|max:191',
        'birthDate' => 'nullable',
        'address' => 'nullable|string|max:191',
        'neighborhood' => 'nullable|string|max:191',
        'createdBy' => 'nullable',
        'updatedBy' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
        'photo' => 'nullable',                  // <--
        'images' => 'nullable',                  // <--

    ];

    public $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function ratings()
    {
        return $this->hasMany(\App\Models\Rating::class, 'account_id');
    }
}
