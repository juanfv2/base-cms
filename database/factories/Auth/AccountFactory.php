<?php

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Auth\Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::factory()->create();

        return [
            'id' => $user->id,
            'user_id' => $user->id,
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'cellPhone' => $this->faker->numerify('0##########'),
            'birthDate' => $this->faker->date('Y-m-d'),
            'address' => $this->faker->word,
            'neighborhood' => $this->faker->word,
            // 'created_by' => $this->faker->word,
            // 'updated_by' => $this->faker->word,
            // 'created_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
