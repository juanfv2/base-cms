<?php

namespace Tests\Feature\APIs\Misc;

use App\Models\Misc\TrackingJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class TrackingJobApiTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    /** @test */
    public function api_index_tracking_jobs()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = TrackingJob::factory($created)->create();

        $this->response = $this->json('POST', route('api.tracking-jobs.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_tracking_job()
    {
        // $this->withoutExceptionHandling();

        $model = TrackingJob::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('POST', route('api.tracking-jobs.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_tracking_job()
    {
        $model = TrackingJob::factory()->create();

        $this->response = $this->actingAsAdmin()->json('GET', route('api.tracking-jobs.show', ['tracking_job' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_tracking_job()
    {
        $model = TrackingJob::factory()->create();
        $modelEdited = TrackingJob::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('PUT', route('api.tracking-jobs.update', ['tracking_job' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_tracking_job()
    {
        $model = TrackingJob::factory()->create();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.tracking-jobs.destroy', ['tracking_job' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.tracking-jobs.destroy', ['tracking_job' => $model->id]));

        $this->response->assertStatus(404);
    }
}
