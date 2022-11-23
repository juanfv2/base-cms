<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\ControllerFiles;

/**
 * Class XFile
 *
 * @version October 19, 2022, 9:29 pm UTC
 */
class XFile extends Model
{
    use BaseCmsModel,
        HasFactory,
        ControllerFiles;

    public $table = 'auth_x_files';

    public $fillable = [
        'entity',
        'entity_id',
        'field',
        'name',
        'nameOriginal',
        'publicPath',
        'extension',
        'data',
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
        'publicPath' => 'string',
        'extension' => 'string',
        'data' => 'json',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'entity' => 'required|string|max:191',
        'entity_id' => 'required',
        'field' => 'required|string|max:191',
        'name' => 'required|string|max:191',
        'nameOriginal' => 'required|string|max:191',
        'publicPath' => 'required|string|max:191',
        'extension' => 'required|string|max:10',
        'data' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
    ];

    public $hidden = [
        'created_at', 'updated_at',
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
