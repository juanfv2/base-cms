<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;

/**
 * Class VisorLogError
 *
 * @version November 11, 2022, 8:53 pm UTC
 */
class VisorLogError extends Model
{
    use BaseCmsModel,
        HasFactory;

    public $table = 'visor_log_errors';

    public $timestamps = false;

    public $fillable = [
        'payload',
        'queue',
        'container_id',
        'created_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'payload' => 'string',
        'queue' => 'string',
        'container_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'payload' => 'required|string',
        'queue' => 'required|string|max:191',
        'container_id' => 'required',
        'created_at' => 'nullable',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }
}
