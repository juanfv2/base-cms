<?php

namespace Tests\Feature\APIs\Country;

use App\Models\Country\Region;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class RegionApiTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    /** @test */
    public function api_index_regions()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = Region::factory($created)->create();

        $this->response = $this->json('POST', route('api.regions.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_region()
    {
        // $this->withoutExceptionHandling();

        $model = Region::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('POST', route('api.regions.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_region()
    {
        $model = Region::factory()->create();

        $this->response = $this->actingAsAdmin()->json('GET', route('api.regions.show', ['region' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_region()
    {
        $model = Region::factory()->create();
        $modelEdited = Region::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('PUT', route('api.regions.update', ['region' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_region()
    {
        $model = Region::factory()->create();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.regions.destroy', ['region' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.regions.destroy', ['region' => $model->id]));

        $this->response->assertStatus(404);
    }
}
