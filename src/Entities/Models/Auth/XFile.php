<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Juanfv2\BaseCms\Traits\ControllerFiles;

/**
 * Class XFile
 * @package App\Models
 * @version September 8, 2020, 4:56 pm UTC
 *
 * @property string $entity
 * @property integer $entity_id
 * @property string $field
 * @property string $name
 * @property string $nameOriginal
 * @property string $publicPath
 * @property string $extension
 * @property string $data
 */
class XFile extends Model
{
    use HasFactory, ControllerFiles;

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
        'publicPath',
        'extension',
        'data'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'           => 'integer',
        'entity'       => 'string',
        'entity_id'    => 'integer',
        'field'        => 'string',
        'name'         => 'string',
        'nameOriginal' => 'string',
        'publicPath'   => 'string',
        'extension'    => 'string',
        'data'         => 'json'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'entity'       => 'required|string|max:255',
        'entity_id'    => 'required|integer',
        'field'        => 'required|string|max:255',
        'name'         => 'required|string|max:255',
        'nameOriginal' => 'required|string|max:255',
        'publicPath'   => 'required|string|max:255',
        'extension'    => 'required|string|max:10',
        'data'         => 'nullable',
        'created_at'   => 'nullable',
        'updated_at'   => 'nullable'
    ];

    public $hidden = [
        'created_at', 'updated_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            // Do some stuff before delete

            $baseAssets = 'public/assets/adm';
            $strLocation = "$baseAssets/$file->entity/$file->field";

            $temp = $file->getPathFileName($strLocation, $file->name);
            $file->deleteFileWithGlob("{$temp}*");

            // Storage::delete($strLocation);
        });
    }
}
