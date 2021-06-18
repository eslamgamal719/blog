<?php

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
         $this->call(RoleTableSeeder::class);
         $this->call(PermissionTableSeeder::class);
         $this->call(CategoriesTableSeeder::class);
         $this->call(PostsTableSeeder::class);
         $this->call(PagesTableSeeder::class);
         $this->call(CommentsTableSeeder::class);
         $this->call(SettingsController::class);
    }
}
