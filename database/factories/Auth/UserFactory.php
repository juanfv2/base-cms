<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Role;
use App\Models\Auth\User;
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
            'api_token' => $this->faker->word,
            'disabled' => 0,
            'uid' => $this->faker->word,
            'role_id' => Role::factory(),
            'remember_token' => $this->faker->word,
            'createdBy' => $this->faker->numberBetween(0, 10),
            'updatedBy' => $this->faker->numberBetween(0, 10),
        ];
    }
}
