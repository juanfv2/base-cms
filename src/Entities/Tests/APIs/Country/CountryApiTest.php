<?php

namespace Tests\APIs\Country;

use App\Models\Country\Country;

use Tests\TestCase;
use Tests\ApiTestTrait;

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
    public function api_create_country()
    {
        // $this->withoutExceptionHandling();

        $country = Country::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', route('api.countries.store'), $country);

        // $this->response->dump();

        $this->assertApiModifications($country);
    }

    /** @test */
    public function api_read_country()
    {
        $country = Country::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.countries.show', ['country' => $country->id]));

        $this->assertApiResponse($country->toArray());
    }

    /** @test */
    public function api_update_country()
    {
        $country = Country::factory()->create();
        $editedCountry = Country::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', route('api.countries.update', ['country' => $country->id]), $editedCountry);

        $this->assertApiModifications($editedCountry);
    }

    /** @test */
    public function api_delete_country()
    {
        $country = Country::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', route('api.countries.destroy', ['country' => $country->id]));

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', route('api.countries.show', ['country' => $country->id]));

        $this->response->assertStatus(404);
    }
}
