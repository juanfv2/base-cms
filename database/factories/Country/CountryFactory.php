<?php

namespace Database\Factories\Country;

use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Country\Country::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->country,
            'code' => $this->faker->countryCode,
            // 'created_by' => $this->faker->word,
            // 'updated_by' => $this->faker->word,
            // 'created_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
