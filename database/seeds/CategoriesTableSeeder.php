<?php

use App\Models\Category;
use Faker\Factory;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        
        Category::create([
            'name' => 'un-categorize',
            'status' => 1,
            'slug' => $faker->sentence(mt_rand(5, 6), true)
        ]);

        Category::create([
            'name' => 'Natural',
            'status' => 1,
            'slug' => $faker->sentence(mt_rand(5, 6), true)
        ]);

        Category::create([
            'name' => 'Flowers',
            'status' => 1,
            'slug' => $faker->sentence(mt_rand(5, 6), true)
        ]);

        Category::create([
            'name' => 'Kitchen',
            'status' => 0,
            'slug' => $faker->sentence(mt_rand(5, 6), true)
        ]);
    }
}
