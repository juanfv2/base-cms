<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class XFile
 * @package Juanfv2\BaseCms\Models
 * @version July 28, 2019, 5:51 am UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property \Illuminate\Database\Eloquent\Collection 
 * @property string entity
 * @property integer entity_id
 * @property string name
 * @property string data
 */
class XFile extends Model
{
    // use SoftDeletes;

    public $table = 'auth_x_files';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'entity',
        'entity_id',
        'field',
        'name',
        'nameOriginal',
        'extension',
        'data'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'entity' => 'string',
        'entity_id' => 'integer',
        'field' => 'string',
        'name' => 'string',
        'nameOriginal' => 'string',
        'extension' => 'string',
        'data' => 'json'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'entity' => 'required',
        'entity_id' => 'required',
        'field' => 'required',
        'name' => 'required',
        'nameOriginal' => 'required',
        'extension' => 'required'
    ];

    protected $hidden = [
         'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at'
    ];
}
