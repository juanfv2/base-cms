<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Country\Region;
use App\Models\Country\Country;

$factory->define(Region::class, function (Faker $faker) {

    return [
        'name' => $faker->word,
        'code' => $faker->countryCode,
        'country_id' => factory(Country::class),
    ];
});
