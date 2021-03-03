<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$Hh8ASBG2oQFWTJb.uNTKeex8Z2WjYigFfPJf5uBh0IAAgBujZWg3i', // 123456
        // 'email_verified_at' => $faker->date('Y-m-d H:i:s'),
        // 'api_token' => $faker->word,
        'disabled' => 0,
        // 'uid' => $faker->word,
        'role_id' => factory(Role::class),
        'remember_token' => $faker->word,
        // 'createdBy' => $faker->word,
        // 'updatedBy' => $faker->word,
        // 'created_at' => $faker->date('Y-m-d H:i:s'),
        // 'updated_at' => $faker->date('Y-m-d H:i:s'),
        // 'deleted_at' => $faker->date('Y-m-d H:i:s')
    ];
});
