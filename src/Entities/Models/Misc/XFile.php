<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\ControllerFiles;

class XFile extends Model
{
    use BaseCmsModel;
    use ControllerFiles;
    use HasFactory;

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

    protected $casts = [
        'entity' => 'string',
        'field' => 'string',
        'name' => 'string',
        'nameOriginal' => 'string',
        'publicPath' => 'string',
        'extension' => 'string',
        'data' => 'json',
    ];

    public static $rules = [
        'entity' => 'required|string',
        'entity_id' => 'required',
        'field' => 'required|string',
        'name' => 'required|string',
        'nameOriginal' => 'required|string',
        'publicPath' => 'required|string',
        'extension' => 'required|string',
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
