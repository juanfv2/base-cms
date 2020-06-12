<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;


/**
 * @SWG\Definition(
 *      definition="XFile",
 *      required={"entity", "entity_id", "field", "name", "nameOriginal", "extension"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="entity",
 *          description="entity",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="entity_id",
 *          description="entity_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="field",
 *          description="field",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="nameOriginal",
 *          description="nameOriginal",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="extension",
 *          description="extension",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="data",
 *          description="data",
 *          type="string"
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
 *      )
 * )
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
