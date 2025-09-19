<?php

namespace Database\Seeders\demo;

use App\Plugins\User\app\Models\Broker;
use App\Plugins\User\app\Models\Division;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrokerSeeder extends Seeder
{
    public function run()
    {
        Division::query()->whereDoesntHave('brokers')->with('groups')->each(function ($division) {
            $root = $division->groups->whereNull('user_group_id')->first();
            $children = $division->groups->reject(fn($i) => $i === $root);

            Broker::factory()->create([
                'user_group_id' => $root->id,
                'career_id' => $division->code . '000',
                'parent_broker_id' => null,
//                'is_business' => false,
                'broker_type' => 'division',
            ]);

            Broker::factory(9)->state(new Sequence(
                fn() => ['user_group_id' => $children->random()->id]
            ))->create();
        });
    }
}
