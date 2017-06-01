<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
	return [
		'name' => $faker->name,
		'email' => $faker->email,
		'password' => bcrypt(str_random(10)),
		'remember_token' => str_random(10),
	];
});

$factory->define(App\CourtCase::class, function (Faker\Generator $faker) {
	$case_no = $faker->unique()->word;

	return [
		'unique_id'         => 'somescraper-' . $case_no,
		'state'             => $faker->state,
		'court_type'        => $faker->randomElement(['District', 'Federal', 'Magistrates']),
		'case_no'           => $case_no,
		'case_name'         => $faker->name . ' V ' . $faker->name,
		'case_type'         => $faker->word,
		'suburb'            => $faker->city,
		'jurisdiction'      => $faker->randomElement(['Civil', 'Criminal']),
		'url'               => $faker->url,
		'notification_time' => $faker->datetime,
	];
});

$factory->define(App\Application::class, function (Faker\Generator $faker) {
	return [
		'title'          => $faker->word,
		'type'           => $faker->word,
		'status'         => $faker->randomElement(['Open','Closed']),
		'date_filed'     => $faker->datetime,
		'date_finalised' => $faker->datetime,
	];
});

$factory->define(App\Party::class, function (Faker\Generator $faker) {
	return [
		'role'     => $faker->randomElement(['Plaintiff','Defendant']),
		'rep_name' => $faker->name,
		'address'  => $faker->address,
		'phone'    => $faker->phoneNumber,
		'fax'      => $faker->phoneNumber,
	];
});

$factory->define(App\Watchlist::class, function (Faker\Generator $faker) {
	return [
		'name' => $faker->name,
	];
});

$factory->define(App\WatchlistEntity::class, function (Faker\Generator $faker) {
	return [];
});

