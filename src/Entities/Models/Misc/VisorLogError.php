<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;

class VisorLogError extends Model
{
    use BaseCmsModel;
    use HasFactory;

    public $table = 'visor_log_errors';

    public $timestamps = false;

    public $fillable = [
        'payload',
        'queue',
        'container_id',
    ];

    protected $casts = [
        'payload' => 'string',
        'queue' => 'string',
    ];

    public static $rules = [
        'payload' => 'required|string',
        'queue' => 'required|string|max:191',
        'container_id' => 'required',
        'created_at' => 'nullable',
    ];

    public $hidden = [
        'payload',
    ];

    public function getErrorAttribute()
    {
        $object = json_decode((string) $this->payload, null, 512, 0);

        return $object ?? $this->payload;
    }
}
