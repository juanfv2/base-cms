<?php

namespace Tests\Repositories\Country;

use App\Models\Country\City;

use Tests\TestCase;
use Juanfv2\BaseCms\Traits\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Country\CityRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CityRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var CityRepository
     */
    protected $modelRepository;

    public function setUp() : void
    {
        parent::setUp();
        $this->modelRepository = App::make(CityRepository::class);
    }

    /** @test */
    public function repo_create_city()
    {
        $city = City::factory()->make()->toArray();

        $createdCity = $this->modelRepository->create($city);

        $createdCity = $createdCity->toArray();
        $this->assertArrayHasKey('id', $createdCity);
        $this->assertNotNull($createdCity['id'], 'Created City must have id specified');
        $this->assertNotNull(City::find($createdCity['id']), 'City with given id must be in DB');
        $this->assertModelData($city, $createdCity);
    }

    /** @test */
    public function repo_read_city()
    {
        $city = City::factory()->create();

        $dbCity = $this->modelRepository->find($city->id);

        $dbCity = $dbCity->toArray();
        $this->assertModelData($city->toArray(), $dbCity);
    }

    /** @test */
    public function repo_update_city()
    {
        $city = City::factory()->create();
        $fakeCity = City::factory()->make()->toArray();

        $updatedCity = $this->modelRepository->update($city, $fakeCity);

        $this->assertModelData($fakeCity, $updatedCity->toArray());
        $dbCity = $this->modelRepository->find($city->id);
        $this->assertModelData($fakeCity, $dbCity->toArray());
    }

    /** @test */
    public function repo_delete_city()
    {
        $city = City::factory()->create();

        $resp = $this->modelRepository->delete($city->id);

        $this->assertTrue($resp);
        $this->assertNull(City::find($city->id), 'City should not exist in DB');
    }
}
