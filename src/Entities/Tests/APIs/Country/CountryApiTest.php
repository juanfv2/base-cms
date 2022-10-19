<?php

namespace Tests\APIs\Country;

use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Country\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CountryApiTest extends TestCase
{
    use ApiTestTrait,
        WithoutMiddleware,
        DatabaseTransactions
        // RefreshDatabase
        // ...
    ;

    /** @test */
    public function api_index_country()
    {
        $this->withoutExceptionHandling();

        $created = 11;
        $limit   = 10;
        $offset  = 0;
        $models  = Country::factory($created)->create();

        $this->response = $this->json('POST', route('api.countries.store', ['limit' => $limit, 'offset' => $offset, 'to_index' => 2]));

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonIndex($limit, $models[0]);
    }

    /** @test */
    public function api_create_country()
    {
        // $this->withoutExceptionHandling();

        $model = Country::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.countries.store'), $model);

        // $this->response->dump();
        // dd($this->response->json());

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_read_country()
    {
        $model = Country::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.countries.show', ['country' => $model->id]));

        $this->assertJsonShow($model);
    }

    /** @test */
    public function api_update_country()
    {
        $model = Country::factory()->create();
        $modelEdited = Country::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.countries.update', ['country' => $model->id]), $modelEdited);

        $this->assertJsonModifications();
    }

    /** @test */
    public function api_delete_country()
    {
        $model = Country::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.countries.destroy', ['country' => $model->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.countries.show', ['country' => $model->id]));

        $this->response->assertStatus(404);
    }
}
