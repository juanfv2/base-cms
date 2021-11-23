<?php

namespace Tests\APIs\Misc;

use App\Models\Misc\BulkError;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BulkErrorApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions; // , RefreshDatabase

    /** @test */
    public function api_create_bulk_error()
    {
        // $this->withoutExceptionHandling();

        $bulkError = BulkError::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.bulk_errors.store'), $bulkError);

        // $this->response->dump();

        $this->assertApiModifications($bulkError);
    }

    /** @test */
    public function api_read_bulk_error()
    {
        $bulkError = BulkError::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.bulk_errors.show', ['bulk_error' => $bulkError->id]));

        $this->assertApiResponse($bulkError->toArray());
    }

    /** @test */
    public function api_update_bulk_error()
    {
        $bulkError = BulkError::factory()->create();
        $editedBulkError = BulkError::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.bulk_errors.update', ['bulk_error' => $bulkError->id]), $editedBulkError);

        $this->assertApiModifications($editedBulkError);
    }

    /** @test */
    public function api_delete_bulk_error()
    {
        $bulkError = BulkError::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.bulk_errors.destroy', ['bulk_error' => $bulkError->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.bulk_errors.show', ['bulk_error' => $bulkError->id]));

        $this->response->assertStatus(404);
    }
}
