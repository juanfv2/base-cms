<?php

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Person;
use App\Models\Country\City;
use App\Models\Country\Region;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create();
        return [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'cellPhone' => $this->faker->phoneNumber,
            'birthDate' => $this->faker->date(),
            'address' => $this->faker->address,
            'neighborhood' => $this->faker->word,
            'email' => $user->email,
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
            'city_id' => City::factory(),
            'createdBy' => $this->faker->numberBetween(0, 10),
            'updatedBy' => $this->faker->numberBetween(0, 10),
        ];
    }
}
