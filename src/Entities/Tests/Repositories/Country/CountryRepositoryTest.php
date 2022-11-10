<?php

namespace Tests\Repositories\Country;

use App\Models\Country\Country;

use Tests\TestCase;
use Juanfv2\BaseCms\Traits\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Country\CountryRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CountryRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var CountryRepository
     */
    protected $modelRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->modelRepository = App::make(CountryRepository::class);
    }

    /** @test */
    public function repo_create_country()
    {
        $country = Country::factory()->make()->toArray();

        $createdCountry = $this->modelRepository->create($country);

        $createdCountry = $createdCountry->toArray();
        $this->assertArrayHasKey('id', $createdCountry);
        $this->assertNotNull($createdCountry['id'], 'Created Country must have id specified');
        $this->assertNotNull(Country::find($createdCountry['id']), 'Country with given id must be in DB');
        $this->assertModelData($country, $createdCountry);
    }

    /** @test */
    public function repo_read_country()
    {
        $country = Country::factory()->create();

        $dbCountry = $this->modelRepository->find($country->id);

        $dbCountry = $dbCountry->toArray();
        $this->assertModelData($country->toArray(), $dbCountry);
    }

    /** @test */
    public function repo_update_country()
    {
        $country = Country::factory()->create();
        $fakeCountry = Country::factory()->make()->toArray();

        $updatedCountry = $this->modelRepository->update($country, $fakeCountry);

        $this->assertModelData($fakeCountry, $updatedCountry->toArray());
        $dbCountry = $this->modelRepository->find($country->id);
        $this->assertModelData($fakeCountry, $dbCountry->toArray());
    }

    /** @test */
    public function repo_delete_country()
    {
        $country = Country::factory()->create();

        $resp = $this->modelRepository->delete($country->id);

        $this->assertTrue($resp);
        $this->assertNull(Country::find($country->id), 'Country should not exist in DB');
    }
}
