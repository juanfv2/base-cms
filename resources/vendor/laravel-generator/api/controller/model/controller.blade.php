@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiController }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use Juanfv2\BaseCms\Controllers\AppBaseController;

{!! $docController !!}
class {{ $config->modelNames->name }}APIController extends AppBaseController
{
    /** @var \{{ $config->namespaces->model }}\{{ $config->modelNames->name }} */
    public $model;
    public $modelNameCamel = '{{ $config->modelNames->name }}';

    public function __construct({{ $config->modelNames->name }} $model)
    {
        $this->model = $model;
    }
}
