<?php

namespace Tests\APIs\Country;

use App\Models\Country\City;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CityApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions; // , RefreshDatabase

    /** @test */
    public function api_create_city()
    {
        // $this->withoutExceptionHandling();

        $city = City::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', '/api/cities', $city);

        // $this->response->dump();

        $this->assertApiModifications($city);
    }

    /** @test */
    public function api_read_city()
    {
        $city = City::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/cities/{$city->id}");

        $this->assertApiResponse($city->toArray());
    }

    /** @test */
    public function api_update_city()
    {
        $city = City::factory()->create();
        $editedCity = City::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', "/api/cities/{$city->id}", $editedCity);

        $this->assertApiModifications($editedCity);
    }

    /** @test */
    public function api_delete_city()
    {
        $city = City::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', "/api/cities/{$city->id}");

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/cities/{$city->id}");

        $this->response->assertStatus(404);
    }
}
