<?php

namespace Database\Factories\Country;

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
    protected $model = \App\Models\Country\City::class;

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

        $region = Region::first();
        if (! $region) {
            $region = Region::factory()->create();
        }

        return [
            'name' => $this->faker->city,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'country_id' => $country,
            'region_id' => $region,
            // 'created_by' => $this->faker->word,
            // 'updated_by' => $this->faker->word,
            // 'created_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            // 'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
