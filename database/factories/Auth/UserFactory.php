<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Country\City;
use App\Models\Country\Country;
use App\Models\Country\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'email' => $this->faker->email,
            'password' => '$2y$10$Hh8ASBG2oQFWTJb.uNTKeex8Z2WjYigFfPJf5uBh0IAAgBujZWg3i', // 123456
            'email_verified_at' => $this->faker->date('Y-m-d H:i:s'),
            'disabled' => 0,
            'phoneNumber' => $this->faker->word,
            'uid' => $this->faker->word,

            'role_id' => Role::factory(),
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
            'city_id' => City::factory(),

            'api_token' => $this->faker->word,
            'remember_token' => $this->faker->word,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
