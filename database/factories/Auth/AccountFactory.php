<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Auth\User;
use App\Models\Auth\Account;
use App\Models\AccountGroup;
use Faker\Generator as Faker;

$factory->define(Account::class, function (Faker $faker) {

    $user = factory(User::class)->create();

    return [
        'firstName' => $faker->firstName(),
        'lastName' => $faker->lastName,
        'phone' => $faker->phoneNumber,
        'cellPhone' => $faker->phoneNumber,
        'birthDate' => $faker->date('Y-m-d'),
        'email' => $user->email,
        // 'address' => $faker->word,
        // 'neighborhood' => $faker->word,
        // 'email' => $faker->word,
        'account_group_id' => factory(AccountGroup::class),
        // 'country_id' => $faker->word,
        // 'region_id' => $faker->word,
        // 'city_id' => $faker->word,
        // 'createdBy' => $faker->word,
        // 'updatedBy' => $faker->word,
        // 'created_at' => $faker->date('Y-m-d H:i:s'),
        // 'updated_at' => $faker->date('Y-m-d H:i:s'),
        // 'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
