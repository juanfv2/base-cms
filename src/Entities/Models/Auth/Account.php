<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Juanfv2\BaseCms\Traits\UserResponsible;

/**
 * Class Account
 * @package App\Models
 * @version April 1, 2021, 10:54 pm UTC
 *
 * @property \App\Models\AuthUser $user
 * @property string $firstName
 * @property string $lastName
 * @property string $cellPhone
 * @property string $birthDate
 * @property string $address
 * @property string $neighborhood
 * @property integer $user_id
 * @property integer $createdBy
 * @property integer $updatedBy
 */
class Account extends Model
{
    use SoftDeletes, HasFactory, UserResponsible;

    public $table = 'auth_accounts';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
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
        'id'           => 'integer',
        'user_id'      => 'integer',
        'firstName'    => 'string',
        'lastName'     => 'string',
        'cellPhone'    => 'string',
        'birthDate'    => 'date',
        'address'      => 'string',
        'neighborhood' => 'string',
        'createdBy'    => 'integer',
        'updatedBy'    => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id'      => 'required',
        'firstName'    => 'required|string|max:191',
        'lastName'     => 'required|string|max:191',
        'cellPhone'    => 'nullable|string|max:191',
        'birthDate'    => 'nullable',
        'address'      => 'nullable|string|max:191',
        'neighborhood' => 'nullable|string|max:191',
        'createdBy'    => 'nullable',
        'updatedBy'    => 'nullable',
        'created_at'   => 'nullable',
        'updated_at'   => 'nullable',
        'deleted_at'   => 'nullable',
        'photoUrl'     => 'nullable', // <--
        'images'       => 'nullable', // <--

    ];

    public $hidden = [
        'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @return array
     */
    public function getPhotoUrlAttribute()
    {
        $f = \App\Models\Auth\XFile::where('entity', $this->table)
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
        $f = \App\Models\Auth\XFile::where('entity', $this->table)
            ->where('field', 'images')
            ->where('entity_id', $this->id)
            ->get();
        // logger(__FILE__ . ':' . __LINE__ . ' $f ', [$f]);
        return $f;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($account) {

            $account->images->each->delete();

            $p = $account->photoUrl;
            if ($p) {
                $p->delete();
            }
        });
    }
}
