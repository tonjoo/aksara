<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\User;

class UsersSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //admin sample
        App\User::truncate();

        App\User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '123456',
            'active' => true,
        ]);
    }

}