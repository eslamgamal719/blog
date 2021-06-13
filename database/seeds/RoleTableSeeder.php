<?php

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'System Administrator',
            'allowed_route' => 'admin'
        ]);    

        $editorRole = Role::create([
            'name' => 'editor',
            'display_name' => 'Supervisor',
            'description' => 'System Supervisor',
            'allowed_route' => 'admin'
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Normal User',
            'allowed_route' => null
        ]);

////////////////////////////////////////////////////////////////////

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@admin.test',
            'email_verified_at' => Carbon::now(),
            'mobile' => '01023262138',
            'password' => bcrypt(123456789),
            'status' => '1',
        ]);
        $admin->attachRole($adminRole);


        $editor = User::create([
            'name' => 'Editor',
            'username' => 'editor',
            'email' => 'editor@editor.test',
            'email_verified_at' => Carbon::now(),
            'mobile' => '01023262139',
            'password' => bcrypt(123456789),
            'status' => '1',
        ]);
        $editor->attachRole($editorRole);


        $user1 = User::create([
            'name' => 'Eslam Gamal',
            'username' => 'Eslam',
            'email' => 'eslam@eslam.test',
            'email_verified_at' => Carbon::now(),
            'mobile' => '01023262137',
            'password' => bcrypt(123456789),
            'status' => '1',
        ]);
        $user1->attachRole($userRole);


        $user2 = User::create([
            'name' => 'Ahmed Ali',
            'username' => 'Ahmed',
            'email' => 'ahmed@ahmed.test',
            'email_verified_at' => Carbon::now(),
            'mobile' => '01023262136',
            'password' => bcrypt(123456789),
            'status' => '1',
        ]);
        $user2->attachRole($userRole);


        $user3 = User::create([
            'name' => 'Nader Ali',
            'username' => 'Nader',
            'email' => 'nader@nader.test',
            'email_verified_at' => Carbon::now(),
            'mobile' => '01023262135',
            'password' => bcrypt(123456789),
            'status' => '1',
        ]);
        $user3->attachRole($userRole);
        

        for($i = 0; $i <= 10; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'username' => $faker->userName,
                'email' => $faker->email,
                'mobile' => '0102326' . random_int(1000, 9999),
                'email_verified_at' => Carbon::now(),
                'password' => bcrypt(123456789),
                'status' => '1',
            ]);
            $user->attachRole($userRole);
        }
    }
}
