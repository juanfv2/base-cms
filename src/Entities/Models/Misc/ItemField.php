<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;

class ItemField extends Model
{
    use BaseCmsModel;
    use HasFactory;

    public $table = 'item_fields';

    public $fillable = [
        'alias',
        'name',
        'label',
        'field',
        'type',
        'allowSearch',
        'allowExport',
        'allowImport',
        'allowInList',
        'sorting',
        'fixed',
        'hidden',
        'index',
        'table',
        'model',
        'extra',
        'allowNull',
        'key',
        'defaultValue',
    ];

    protected $casts = [
        'alias' => 'string',
        'name' => 'string',
        'label' => 'string',
        'field' => 'string',
        'type' => 'string',
        'allowSearch' => 'boolean',
        'allowExport' => 'boolean',
        'allowImport' => 'boolean',
        'allowInList' => 'boolean',
        'sorting' => 'boolean',
        'fixed' => 'boolean',
        'hidden' => 'boolean',
        'table' => 'string',
        'model' => 'string',
        'extra' => 'string',
        'allowNull' => 'string',
        'key' => 'string',
        'defaultValue' => 'string',
    ];

    public static $rules = [
        'alias' => 'required|string|max:191',
        'name' => 'required|string|max:191',
        'label' => 'required|string|max:191',
        'field' => 'required|string|max:191',
        'type' => 'required|string|max:191',
        'allowSearch' => 'required|boolean',
        'allowExport' => 'required|boolean',
        'allowImport' => 'required|boolean',
        'allowInList' => 'required|boolean',
        'sorting' => 'required|boolean',
        'fixed' => 'required|boolean',
        'hidden' => 'required|boolean',
        'index' => 'required',
        'table' => 'required|string|max:191',
        'model' => 'required|string|max:191',
        'extra' => 'nullable|string|max:65535',
        'allowNull' => 'required|string|max:191',
        'key' => 'required|string|max:191',
        'defaultValue' => 'required|string|max:191',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
    ];

    public $hidden = [
        'table', 'alias', 'key', 'extra', 'allowNull', 'key', 'defaultValue', 'createdBy', 'updatedBy', 'created_at', 'updated_at', 'deleted_at',
    ];
}
