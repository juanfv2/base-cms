<?php

namespace Tests\APIs;

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
    public function api_index_visor_log_error()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = VisorLogError::factory($created)->create();

        $this->response = $this->json('POST', route('api.visor_log_errors.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_visor_log_error()
    {
        // $this->withoutExceptionHandling();

        $model = VisorLogError::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.visor_log_errors.store'), $model);

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_visor_log_error()
    {
        $model = VisorLogError::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.visor_log_errors.show', ['visor_log_error' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_visor_log_error()
    {
        $model = VisorLogError::factory()->create();
        $modelEdited = VisorLogError::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.visor_log_errors.update', ['visor_log_error' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_visor_log_error()
    {
        $model = VisorLogError::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.visor_log_errors.destroy', ['visor_log_error' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.visor_log_errors.destroy', ['visor_log_error' => $model->id]));

        $this->response->assertStatus(404);
    }
}
