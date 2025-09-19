<?php

namespace Database\Seeders\demo;

use App\Plugins\User\app\Models\Division;
use App\Plugins\User\app\Models\Group;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    public function run()
    {
        Division::query()->whereDoesntHave('groups')->each(function ($division) {
            $default = $division->groups()->create([
                'name' => 'Skupina 1',
                'user_division_id' => $division->id,
                'provision' => 100,
            ]);

            Group::factory()->createHierarchy($default, rand(1, 10));
        });
    }
}
