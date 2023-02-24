@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->apiTests }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->tests }}\TestCase;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class {{ $config->modelNames->name }}ApiTest extends TestCase
{
    use ApiTestTrait;
        use WithoutMiddleware;
        use DatabaseTransactions;
        // use RefreshDatabase;


    /** @test */
    public function api_index_{{ $config->modelNames->snakePlural }}()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit   = 10;
        $offset  = 0;
        $models  = {{ $config->modelNames->name }}::factory($created)->create();

        $this->response = $this->json('POST', route('{{ $config->apiPrefix }}.{{ $config->modelNames->dashedPlural }}.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }


    /** @test */
    public function api_create_{{ $config->modelNames->snake }}()
    {
        // $this->withoutExceptionHandling();

        $model = {{ $config->modelNames->name }}::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('POST', route('{{ $config->apiPrefix }}.{{ $config->modelNames->dashedPlural }}.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }
    /** @test */
    public function api_read_{{ $config->modelNames->snake }}()
    {
        $model = {{ $config->modelNames->name }}::factory()->create();

        $this->response = $this->actingAsAdmin()->json('GET', route('{{ $config->apiPrefix }}.{{ $config->modelNames->dashedPlural }}.show', ['{{ $config->modelNames->snake }}' => $model->{{ $config->primaryName }}]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_{{ $config->modelNames->snake }}()
    {
        $model = {{ $config->modelNames->name }}::factory()->create();
        $modelEdited = {{ $config->modelNames->name }}::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('PUT', route('{{ $config->apiPrefix }}.{{ $config->modelNames->dashedPlural }}.update', ['{{ $config->modelNames->snake }}' => $model->{{ $config->primaryName }}]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_{{ $config->modelNames->snake }}()
    {
        $model = {{ $config->modelNames->name }}::factory()->create();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('{{ $config->apiPrefix }}.{{ $config->modelNames->dashedPlural }}.destroy', ['{{ $config->modelNames->snake }}' => $model->{{ $config->primaryName }}]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('{{ $config->apiPrefix }}.{{ $config->modelNames->dashedPlural }}.destroy', ['{{ $config->modelNames->snake }}' => $model->{{ $config->primaryName }}]));

        $this->response->assertStatus(404);
    }
}
