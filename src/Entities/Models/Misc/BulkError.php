<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class BulkError
 *
 * @package App\Models
 * @version October 19, 2022, 9:41 pm UTC
 */
class BulkError extends Model
{
    use BaseCmsModel,
        HasFactory;

    public $table = 'bulk_errors';

    public $timestamps = false;


    public $fillable = [
        'payload',
        'queue',
        'container_id'
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
        'container_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'payload'      => 'required|string',
        'queue'        => 'required|string|max:191',
        'container_id' => 'nullable',
        'created_at'   => 'nullable',
    ];
}
