<?php

namespace Database\Seeders;

use App\Enums\TalentRole;
use App\Models\Talent;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'John Doe',
            'email'    => 'john.doe@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);

        Talent::factory()->create([
            'first_name' => 'Sarah',
            'last_name'  => 'Connor',
            'email'      => 'sarah.connor@skynet.com',
            'role'       => TalentRole::CG_SUPERVISOR
        ]);

        Talent::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'McClane',
            'email'      => 'john.mclane@nypd.com',
            'role'       => TalentRole::DEVELOPER
        ]);

        Talent::factory()->create([
            'first_name' => 'John',
            'last_name'  => 'Hammond',
            'email'      => 'john.hammond@jurassicpark.com',
            'role'       => TalentRole::CG_ARTIST
        ]);
    }
}
