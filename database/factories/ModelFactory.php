<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Sponsorable::class, function (Faker $faker) {
  return [
    'name' => 'Example Poscast',
  ];
});

$factory->define(App\SponsorableSlot::class, function (Faker $faker) {
  return [
    'price' => 25000,
    'publish_date' => now()->addMonths(1),
  ];
});

$factory->define(App\Sponsorship::class, function (Faker $faker) {
  return [
    'email' => 'john@example.com',
    'company_name' => 'ExampleSoft Inc.',
    'amount' => 5000,
  ];
});
