<?php


return [

    // /framework/src/Illuminate/Validation/ValidationException.php
    // parent::__construct(__('validation.message'));
    'message' => 'Los datos proporcionados no eran válidos.',

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
     */

    'accepted' => ':attribute debe ser aceptado.',
    'active_url' => ':attribute no es una URL válida.',
    'after' => ':attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => ':attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => ':attribute sólo puede contener letras.',
    'alpha_dash' => ':attribute sólo puede contener letras, números y guiones (a-z, 0-9, -_).',
    'alpha_num' => ':attribute sólo puede contener letras y números.',
    'array' => ':attribute debe ser un array.',
    'before' => ':attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => ':attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'numeric' => ':attribute debe ser un valor entre :min y :max.',
        'file' => 'El archivo :attribute debe pesar entre :min y :max kilobytes.',
        'string' => ':attribute debe contener entre :min y :max caracteres.',
        'array' => ':attribute debe contener entre :min y :max elementos.',
    ],
    'boolean' => ':attribute debe ser verdadero o falso.',
    'confirmed' => 'Confirmación de :attribute no coincide.',
    'country' => ':attribute no es un país válido.',
    'date' => ':attribute no corresponde con una fecha válida.',
    'date_format' => ':attribute no corresponde con el formato de fecha :format.',
    'different' => 'Los campos :attribute y :other han de ser diferentes.',
    'digits' => ':attribute debe ser un número de :digits dígitos.',
    'digits_between' => ':attribute debe contener entre :min y :max dígitos.',
    'dimensions' => ':attribute tiene dimensiones inválidas.',
    'distinct' => ':attribute tiene un valor duplicado.',
    'email' => ':attribute no corresponde con una dirección de e-mail válida.',
    'file' => ':attribute debe ser un archivo.',
    'filled' => ':attribute es obligatorio.',
    'exists' => ':attribute no existe.',
    'image' => ':attribute debe ser una imagen.',
    'in' => ':attribute debe ser igual a alguno de estos valores :values.',
    'in_array' => ':attribute no existe en :other.',
    'integer' => ':attribute debe ser un número entero.',
    'ip' => ':attribute debe ser una dirección IP válida.',
    'ipv4' => ':attribute debe ser una dirección IPv4 válida.',
    'ipv6' => ':attribute debe ser una dirección IPv6 válida.',
    'json' => ':attribute debe ser una cadena de texto JSON válida.',
    'max' => [
        'numeric' => ':attribute debe ser :max como máximo.',
        'file' => 'El archivo :attribute debe pesar :max kilobytes como máximo.',
        'string' => ':attribute debe contener :max caracteres como máximo.',
        'array' => ':attribute debe contener :max elementos como máximo.',
    ],
    'mimes' => ':attribute debe ser un archivo de tipo :values.',
    'mimetypes' => ':attribute debe ser un archivo de tipo :values.',
    'min' => [
        'numeric' => ':attribute debe tener al menos :min.',
        'file' => 'El archivo :attribute debe pesar al menos :min kilobytes.',
        'string' => ':attribute debe contener al menos :min caracteres.',
        'array' => ':attribute no debe contener más de :min elementos.',
    ],
    'not_in' => ':attribute seleccionado es inválido.',
    'numeric' => ':attribute debe ser un número.',
    'present' => ':attribute debe estar presente.',
    'regex' => 'El formato del campo :attribute es inválido.',
    'required' => ':attribute es obligatorio.',
    'required_if' => ':attribute es obligatorio cuando el campo :other es :value.',
    'required_unless' => ':attribute es requerido a menos que :other se encuentre en :values.',
    'required_with' => ':attribute es obligatorio cuando :values está presente.',
    'required_with_all' => ':attribute es obligatorio cuando :values está presente.',
    'required_without' => ':attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => ':attribute es obligatorio cuando ninguno de los campos :values está presente.',
    'same' => 'Los campos :attribute y :other deben coincidir.',
    'size' => [
        'numeric' => ':attribute debe ser :size.',
        'file' => 'El archivo :attribute debe pesar :size kilobytes.',
        'string' => ':attribute debe contener :size caracteres.',
        'array' => ':attribute debe contener :size elementos.',
    ],
    'state' => 'El estado no es válido para el país seleccionado.',
    'string' => ':attribute debe contener sólo caracteres.',
    'timezone' => ':attribute debe contener una zona válida.',
    'unique' => 'El :attribute ya está en uso.',
    'uploaded' => 'El elemento :attribute fallo al subir.',
    'url' => 'El formato de :attribute no corresponde con el de una URL válida.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
     */

    //    'custom' => [
    //        'attribute-name' => [
    //            'rule-name' => 'custom-message',
    //        ],
    //    ],
    'custom' => [
        'password' => [
            'required' => 'Ingresa tu contraseña',
            'nomatch' => 'Contraseñas no coinciden',
        ],
        'email' => [
            'required' => '¡Necesitamos saber su dirección de correo electrónico!',
            'unique' => ':attribute ya existe',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
     */

    'attributes' => [
        'name' => 'Nombre',
        'code' => 'Código',
        'email' => 'Correo electrónico',
        'password' => 'Contraseña',
    ],

    'model' => [
        'list'      => 'Lista de [:model]',
        'showed'   => ':model',
        'stored'   => ':model guardado',
        'updated'   => ':model actualizado',
        'deleted'   => ':model borrado',
        'not.found' => ':model no encontrado',
        'error'     => 'Error inesperado, :model',
    ],

    'columns.no.match' => 'El número de columnas no coincide <br/>requeridas: <b>:required</b> <br/>enviadas: <b>:sent</b>',

];
