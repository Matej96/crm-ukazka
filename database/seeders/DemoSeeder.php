<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            \Database\Seeders\demo\DivisionSeeder::class,
            \Database\Seeders\demo\GroupSeeder::class,
            \Database\Seeders\demo\BrokerSeeder::class,
        ]);
    }
}
