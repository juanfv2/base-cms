<?php

namespace Database\Factories\Country;

use App\Models\Country\City;
use App\Models\Country\Country;
use App\Models\Country\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
        ];
    }
}
