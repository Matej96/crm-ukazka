<?php

namespace Database\Seeders\demo;

use App\Plugins\User\app\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run()
    {
        Division::factory()->createMany(4);
    }
}
