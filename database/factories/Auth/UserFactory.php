<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Role;
use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Auth\User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $country = Country::first();
        if (! $country) {
            $country = Country::factory()->create();
        }
        $role = Role::first();
        if (! $role) {
            $role = Role::factory()->create();
        }

        return [
            'name' => $this->faker->word,
            'email' => $this->faker->email,
            'password' => '$2y$10$Hh8ASBG2oQFWTJb.uNTKeex8Z2WjYigFfPJf5uBh0IAAgBujZWg3i', // 123456
            'email_verified_at' => $this->faker->date('Y-m-d H:i:s'),
            'disabled' => 0,
            'phone_number' => $this->faker->numerify('0##########'),
            'uid' => $this->faker->word,
            'role_id' => $role->id,
            'country_id' => $country->id,
            // 'region_id' => $this->faker->word,
            // 'city_id' => $this->faker->word,
            'api_token' => $this->faker->text($this->faker->numberBetween(5, 96)),
            'remember_token' => $this->faker->text($this->faker->numberBetween(5, 96)),
            // 'created_by' => $this->faker->word,
            // 'updated_by' => $this->faker->word,
            // 'created_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
