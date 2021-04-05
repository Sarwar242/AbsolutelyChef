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
        $this->call(UsersTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(OptionsTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(PackageTableSeeder::class);
        $this->call(ExperienceTableSeeder::class);

    }
}
