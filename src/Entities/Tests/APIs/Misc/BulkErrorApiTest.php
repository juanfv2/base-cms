<?php

namespace Tests\APIs\Misc;

use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Misc\BulkError;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BulkErrorApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ...
    ;

    /** @test */
    public function api_index_bulk_error()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit   = 10;
        $offset  = 0;
        $models  = BulkError::factory($created)->create();

        $this->response = $this->json('POST', route('api.bulk_errors.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_bulk_error()
    {
        // $this->withoutExceptionHandling();

        $model = BulkError::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.bulk_errors.store'), $model);

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_bulk_error()
    {
        $model = BulkError::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.bulk_errors.show', ['bulk_error' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_bulk_error()
    {
        $model = BulkError::factory()->create();
        $modelEdited = BulkError::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.bulk_errors.update', ['bulk_error' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_bulk_error()
    {
        $model = BulkError::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.bulk_errors.destroy', ['bulk_error' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.bulk_errors.show', ['bulk_error' => $model->id]));

        $this->response->assertStatus(404);
    }
}
