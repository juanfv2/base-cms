<?php

namespace Tests\Feature\APIs\Country;

use App\Models\Country\City;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Juanfv2\BaseCms\Traits\ApiTestTrait;
use Tests\TestCase;

class CityApiTest extends TestCase
{
    use ApiTestTrait;
    use DatabaseTransactions;
    use WithoutMiddleware;
    // use RefreshDatabase;

    /** @test */
    public function api_index_cities()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit = 10;
        $offset = 0;
        $models = City::factory($created)->create();

        $this->response = $this->json('POST', route('api.cities.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dd();

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_city()
    {
        // $this->withoutExceptionHandling();

        $model = City::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('POST', route('api.cities.store'), $model);

        // $this->response->dd();

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_city()
    {
        $model = City::factory()->create();

        $this->response = $this->actingAsAdmin()->json('GET', route('api.cities.show', ['city' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_city()
    {
        $model = City::factory()->create();
        $modelEdited = City::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin()->json('PUT', route('api.cities.update', ['city' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_city()
    {
        $model = City::factory()->create();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.cities.destroy', ['city' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin()->json('DELETE', route('api.cities.destroy', ['city' => $model->id]));

        $this->response->assertStatus(404);
    }
}
