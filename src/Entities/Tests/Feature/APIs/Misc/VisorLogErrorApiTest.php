<?php

namespace Tests\Feature\APIs\Misc;

use App\Models\Misc\VisorLogError;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class VisorLogErrorApiTest extends TestCase
{
    use ApiTestTrait;
    use WithoutMiddleware;
    use DatabaseTransactions;
    // use RefreshDatabase;

    /** @test */
    public function api_index_visor_log_errors()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = VisorLogError::factory($created)->create();

        $this->response = $this->json('POST', route('api.visor-log-errors.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_visor_log_error()
    {
        // $this->withoutExceptionHandling();

        $model = VisorLogError::factory()->make()->toArray();

        $model['payload'] = '-error-';

        $this->response = $this->json('POST', route('api.visor-log-errors.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_visor_log_error()
    {
        $model = VisorLogError::factory()->create();

        $this->response = $this->json('GET', route('api.visor-log-errors.show', ['visor_log_error' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_visor_log_error()
    {
        $model = VisorLogError::factory()->create();
        $modelEdited = VisorLogError::factory()->make()->toArray();

        $modelEdited['payload'] = '-error-';

        $this->response = $this->json('PUT', route('api.visor-log-errors.update', ['visor_log_error' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_visor_log_error()
    {
        $model = VisorLogError::factory()->create();

        $this->response = $this->json('DELETE', route('api.visor-log-errors.destroy', ['visor_log_error' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->json('DELETE', route('api.visor-log-errors.destroy', ['visor_log_error' => $model->id]));

        $this->response->assertStatus(404);
    }
}
