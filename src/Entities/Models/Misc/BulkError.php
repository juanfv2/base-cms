<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class BulkError
 *
 * @package App\Models
 * @version November 4, 2021, 10:09 pm UTC
 */
class BulkError extends Model
{
    use HasFactory;

    public $table = 'bulk_errors';

    public $timestamps = false;

    protected $dates = ['deleted_at'];



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
        'payload' => 'required|string',
        'queue' => 'required|string|max:191',
        'container_id' => 'required',
        'created_at' => 'required'
    ];
}
