<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Auth\User;
use App\Models\Auth\Person;
use App\Models\Country\City;
use App\Models\Country\Region;
use App\Models\Country\Country;

use Faker\Generator as Faker;

$factory->define(Person::class, function (Faker $faker) {

    $user = factory(User::class)->create();

    return [
        'firstName' => $faker->firstName(),
        'lastName' => $faker->lastName,
        'phone' => $faker->phoneNumber,
        'cellPhone' => $faker->phoneNumber,
        'birthDate' => $faker->date(),
        'address' => $faker->address,
        'neighborhood' => $faker->word,
        'email' => $user->email,
        'country_id' => factory(Country::class),
        'region_id' => factory(Region::class),
        'city_id' => factory(City::class),
        // 'createdBy' => $faker->word,
        // 'updatedBy' => $faker->word,
        // 'created_at' => $faker->date('Y-m-d H:i:s'),
        // 'updated_at' => $faker->date('Y-m-d H:i:s'),
        // 'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
