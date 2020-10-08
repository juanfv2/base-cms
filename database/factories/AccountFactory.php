<?php

namespace Database\Factories;

use App\Models\Account;
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
            'firstName' => $this->faker->word,
        'lastName' => $this->faker->word,
        'phone' => $this->faker->word,
        'cellPhone' => $this->faker->word,
        'birthDate' => $this->faker->word,
        'address' => $this->faker->word,
        'neighborhood' => $this->faker->word,
        'email' => $this->faker->word,
        'country_id' => $this->faker->word,
        'region_id' => $this->faker->word,
        'city_id' => $this->faker->word,
        'createdBy' => $this->faker->word,
        'updatedBy' => $this->faker->word,
        'created_at' => $this->faker->date('Y-m-d H:i:s'),
        'updated_at' => $this->faker->date('Y-m-d H:i:s'),
        'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
