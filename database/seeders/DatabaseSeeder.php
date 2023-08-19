<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        \App\Models\User::create([
            'username' => 'user',
            'email' => 'user@gmail.com',
            'password' => 'user'
        ]);
        \App\Models\Categories::insert(
            [
                'name_category' => 'Mobile Game',
            ],
            [
                'name_category' => 'PC Game',
            ],
        );
        \App\Models\Sub_Categories::insert(
            [
                'name_sub_category' => 'Growtopia',
                'category_id' => 1,
            ],
            [
                'name_sub_category' => 'Free Fire',
                'category_id' => 1,
            ],
        );
        \App\Models\Types_Sub_Categories::insert(
            [
                'name_type' => 'Akun',
                'sub_category_id' => 1,
            ],
            [
                'name_type' => 'Diamond',
                'sub_category_id' => 2,
            ],
        );
    }
}
