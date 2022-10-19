<?php

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Person;
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
            'id'           => $user->id,
            'user_id'      => $user->id,
            'firstName'    => $this->faker->word,
            'lastName'     => $this->faker->word,
            'cellPhone'    => $this->faker->word,
            'birthDate'    => $this->faker->date(),
            'address'      => $this->faker->word,
            'neighborhood' => $this->faker->word,
        ];
    }
}
