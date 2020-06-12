<?php

namespace Juanfv2\BaseCms\Models\Auth;

use Eloquent as Model;

/**
 * Class GenericModel
 * @package Juanfv2\BaseCms\Models
 * @version January 14, 2019, 8:34 am UTC
 *
 */
class GenericModel extends Model
{
    public $table = '';
    // public $primaryKey = '';
    
    protected $hidden = [
        'createdBy',
        'updatedBy',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
