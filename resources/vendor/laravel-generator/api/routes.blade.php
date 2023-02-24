Route::apiResource('{{ $config->modelNames->dashedPlural }}', {{ $config->namespaces->apiController }}\{{ $config->modelNames->name }}APIController::class);
