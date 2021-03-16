<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // For dev
        if (true || \App::environment('local')) {
            $userData = [
                'name'     => 'admin',
                'email'    => 'admin@admin.com',
                'password' => bcrypt('password'),
            ];

            $user = User::factory()->create($userData);

            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0]."'s Team",
                'personal_team' => true,
            ]));

            // User::factory(100)->create();
        }
        
    }
}
