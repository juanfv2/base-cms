<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Country\City;
use Faker\Generator as Faker;
use App\Models\Country\Region;
use App\Models\Country\Country;

$factory->define(City::class, function (Faker $faker) {

    return [
        'name' => $faker->word,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'country_id' => factory(Country::class),
        'region_id' => factory(Region::class),
    ];
});
