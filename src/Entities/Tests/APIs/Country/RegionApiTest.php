<?php

namespace Tests\APIs\Country;

use App\Models\Country\Region;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegionApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions; // , RefreshDatabase

    /** @test */
    public function api_create_region()
    {
        // $this->withoutExceptionHandling();

        $region = Region::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('POST', '/api/regions', $region);

        // $this->response->dump();

        $this->assertApiModifications($region);
    }

    /** @test */
    public function api_read_region()
    {
        $region = Region::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/regions/{$region->id}");

        $this->assertApiResponse($region->toArray());
    }

    /** @test */
    public function api_update_region()
    {
        $region = Region::factory()->create();
        $editedRegion = Region::factory()->make()->toArray();

        $this->response = $this->actingAsAdmin('api')->json('PUT', "/api/regions/{$region->id}", $editedRegion);

        $this->assertApiModifications($editedRegion);
    }

    /** @test */
    public function api_delete_region()
    {
        $region = Region::factory()->create();

        $this->response = $this->actingAsAdmin('api')->json('DELETE', "/api/regions/{$region->id}");

        $this->assertApiSuccess();

        $this->response = $this->actingAsAdmin('api')->json('GET', "/api/regions/{$region->id}");

        $this->response->assertStatus(404);
    }
}
