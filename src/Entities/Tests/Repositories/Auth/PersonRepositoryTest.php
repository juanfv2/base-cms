<?php

namespace Tests\Repositories\Auth;

use App\Models\Auth\Person;

use Tests\TestCase;
use Tests\ApiTestTrait;

use Illuminate\Support\Facades\App;
use App\Repositories\Auth\PersonRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PersonRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var PersonRepository
     */
    protected $modelRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->modelRepository = App::make(PersonRepository::class);
    }

    /** @test */
    public function repo_create_person()
    {
        $person = Person::factory()->make()->toArray();

        $createdPerson = $this->modelRepository->create($person);

        $createdPerson = $createdPerson->toArray();
        $this->assertArrayHasKey('id', $createdPerson);
        $this->assertNotNull($createdPerson['id'], 'Created Person must have id specified');
        $this->assertNotNull(Person::find($createdPerson['id']), 'Person with given id must be in DB');
        $this->assertModelData($person, $createdPerson);
    }

    /** @test */
    public function repo_read_person()
    {
        $person = Person::factory()->create();

        $dbPerson = $this->modelRepository->find($person->id);

        $dbPerson = $dbPerson->toArray();
        $this->assertModelData($person->toArray(), $dbPerson);
    }

    /** @test */
    public function repo_update_person()
    {
        $person = Person::factory()->create();
        $fakePerson = Person::factory()->make()->toArray();

        $updatedPerson = $this->modelRepository->update($person, $fakePerson);

        $this->assertModelData($fakePerson, $updatedPerson->toArray());
        $dbPerson = $this->modelRepository->find($person->id);
        $this->assertModelData($fakePerson, $dbPerson->toArray());
    }

    /** @test */
    public function repo_delete_person()
    {
        $person = Person::factory()->create();

        $resp = $this->modelRepository->delete($person->id);

        $this->assertTrue($resp);
        $this->assertNull(Person::find($person->id), 'Person should not exist in DB');
    }
}
