<?php

namespace Tests\APIs\Country;

use App\Models\Country\Country;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CountryApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions; // , RefreshDatabase

    /** @test */
    public function api_create_country()
    {
        // $this->withoutExceptionHandling();

        $country = Country::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', '/api/countries', $country);

        // $this->response->dump();

        $this->assertApiModifications($country);
    }

    /** @test */
    public function api_read_country()
    {
        $country = Country::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/countries/{$country->id}");

        $this->assertApiResponse($country->toArray());
    }

    /** @test */
    public function api_update_country()
    {
        $country = Country::factory()->create();
        $editedCountry = Country::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', "/api/countries/{$country->id}", $editedCountry);

        $this->assertApiModifications($editedCountry);
    }

    /** @test */
    public function api_delete_country()
    {
        $country = Country::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', "/api/countries/{$country->id}");

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/countries/{$country->id}");

        $this->response->assertStatus(404);
    }
}
