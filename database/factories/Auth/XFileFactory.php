<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Auth\XFile;
use Faker\Generator as Faker;

$factory->define(XFile::class, function (Faker $faker) {

    return [
        'entity' => $faker->word,
        'entity_id' => $faker->numberBetween(1, 1000),
        'field' => $faker->word,
        'name' => $faker->word,
        'nameOriginal' => $faker->word,
        'publicPath' => $faker->word,
        'extension' => $faker->countryCode,
        'data' => $faker->text,
        // 'created_at' => $faker->date('Y-m-d H:i:s'),
        // 'updated_at' => $faker->date('Y-m-d H:i:s')
    ];
});
