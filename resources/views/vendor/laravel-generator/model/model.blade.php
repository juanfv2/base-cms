@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->model }};

use Illuminate\Database\Eloquent\Model;
use Juanfv2\BaseCms\Traits\BaseCmsModel;
use Juanfv2\BaseCms\Traits\UserResponsible;
@if($config->options->softDelete)use Illuminate\Database\Eloquent\SoftDeletes;@nls(1)@endif
@if($config->options->tests)use Illuminate\Database\Eloquent\Factories\HasFactory;@nls(1)@endif

@if(isset($swaggerDocs)){!! $swaggerDocs  !!}@endif
class {{ $config->modelNames->name }} extends Model
{
    use BaseCmsModel;
    use UserResponsible;
@if($config->options->softDelete)@tab()use SoftDeletes;@nls(1)@endif
@if($config->options->tests)@tab()use HasFactory;@nls(2)@endif
    public $table = '{{ $config->tableName }}';

@if($customPrimaryKey)@tab()protected $primaryKey = '{{ $customPrimaryKey }}';@nls(2)@endif
@if($config->connection)@tab()protected $connection = '{{ $config->connection }}';@nls(2)@endif
@if(!$timestamps)@tab()public $timestamps = false;@nls(2)@endif
@if($customSoftDelete)@tab()protected $dates = ['{{ $customSoftDelete }}'];@nls(2)@endif
@if($customCreatedAt)@tab()const CREATED_AT = '{{ $customCreatedAt }}';@nls(2)@endif
@if($customUpdatedAt)@tab()const UPDATED_AT = '{{ $customUpdatedAt }}';@nls(2)@endif
    public $fillable = [
        {!! $fillables !!}
    ];

    protected $casts = [
        {!! $casts !!}
    ];

    public static $rules = [
        {!! $rules !!}
    ];

    public $hidden = [
        'created_by', 'updated_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    {!! $relations !!}
}
