<?php

namespace Database\Factories\Auth;

use App\Models\Auth\User;
use App\Models\Auth\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstName'    => $this->faker->word,
            'lastName'     => $this->faker->word,
            'cellPhone'    => $this->faker->word,
            'birthDate'    => $this->faker->date(),
            'address'      => $this->faker->word,
            'neighborhood' => $this->faker->word,
            'user_id'      => User::factory(),
        ];
    }
}
