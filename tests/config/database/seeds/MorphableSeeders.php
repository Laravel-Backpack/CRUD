<?php

namespace Backpack\CRUD\Tests\Config\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MorphableSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $now = \Carbon\Carbon::now();

        DB::table('recommends')->insert([[
            'title' => $faker->title,
            'created_at' => $now,
            'updated_at' => $now,
        ], [
            'title' => $faker->title,
            'created_at' => $now,
            'updated_at' => $now,
        ]]);

        DB::table('bills')->insert([[
            'title' => $faker->title,
            'created_at' => $now,
            'updated_at' => $now,
        ], [
            'title' => $faker->title,
            'created_at' => $now,
            'updated_at' => $now,
        ]]);

        DB::table('planets')->insert([[
            'title' => $faker->title,
        ], [
            'title' => $faker->title,
        ]]);

        DB::table('planets_non_nullable')->insert([[
            'title' => $faker->title,
            'user_id' => '',
        ], [
            'title' => $faker->title,
            'user_id' => '',
        ]]);

        DB::table('comets')->insert([['user_id' => ''], ['user_id' => '']]);

        DB::table('bangs')->insert([['name' => 'bang1'], ['name' => 'bang2']]);
    }
}
