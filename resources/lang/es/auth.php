<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
     */

    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Vuelva a intentarlo en :seconds segundos.',

    'session' => [
        'out' => 'Token Invalido.',
    ],
    'no' => [
        'active' => 'Usuario inactivo.',
        'auth' => 'No Autorizado, no tiene permiso a esta sección.',
    ],

    'password' => [
        'reset' => [
            'subject' => 'Restablecimiento de contraseña',
            'action' => 'Restablecer la contraseña',
            'line' => [
                '1' => 'Usted está recibiendo este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta. Haga clic en el siguiente botón para restablecer su contraseña:',
                '2' => 'Si no solicitó restablecer la contraseña, no se requieren más acciones.',
            ],
        ],
    ],
];
