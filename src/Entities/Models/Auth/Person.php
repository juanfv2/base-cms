<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;

class Person extends Model
{
    use BaseCmsModel;
    use HasFactory;
    use SoftDeletes;
    use UserResponsible;

    public $table = 'auth_people';

    public $incrementing = false;

    public $fillable = [
        'id',
        'first_name',
        'last_name',
        'cell_phone',
        'birth_date',
        'address',
        'neighborhood',
        'user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string',
        'cell_hone' => 'string',
        'birth_date' => 'date',
        'address' => 'string',
        'neighborhood' => 'string',
    ];

    public static $rules = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'cell_phone' => 'nullable|string',
        'birth_date' => 'nullable',
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

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => ($attributes['first_name'].' '.$attributes['last_name']),
        );
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id');
    }
}
