<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

class Person extends Model
{
    use BaseCmsModel;
    use UserResponsible;
    use SoftDeletes;
    use HasFactory;

    public $table = 'auth_people';

    public $incrementing = false;

    public $fillable = [
        'id',
        'firstName',
        'lastName',
        'cellPhone',
        'birthDate',
        'address',
        'neighborhood',
        'user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'firstName' => 'string',
        'lastName' => 'string',
        'cellPhone' => 'string',
        'birthDate' => 'date',
        'address' => 'string',
        'neighborhood' => 'string',
    ];

    public static $rules = [
        'firstName' => 'required|string',
        'lastName' => 'required|string',
        'cellPhone' => 'nullable|string',
        'birthDate' => 'nullable',
        'address' => 'nullable|string',
        'neighborhood' => 'nullable|string',
        'user_id' => 'nullable',
        'created_by' => 'nullable',
        'updated_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
    ];

    public $hidden = [
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id');
    }
}
