<?php

namespace App\Plugins\User\database\factories;

use App\Plugins\Codebook\app\Models\Address;
use App\Plugins\Product\app\Models\Sector;
use App\Plugins\System\app\Models\User;
use App\Plugins\User\app\Models\Broker;
use App\Plugins\User\app\Models\BrokerSector;
use App\Plugins\User\app\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Plugins\User\app\Models\Broker>
 */
class BrokerFactory extends Factory
{
    protected $model = Broker::class;

    private static int $codeIndex = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_group_id' => Group::factory(),
//TODO out_of_memory            'parent_broker_id' => Broker::factory()->create(['is_ror' => true]),
            'career_id' => function (array $attributes) {
            return (optional(Group::find($attributes['user_group_id'])->division)->code ?? fake()->unique()->numerify('###'))
                . str_pad(self::$codeIndex++, 3, '0', STR_PAD_LEFT);
//                return fake()->unique()->numerify('######');
            },
            'career_status' => fake()->randomElement(Broker::getFormFieldOptions('career_status')['career_status']->keys()->flatMap(fn($v) => array_fill(0, $v === 'active' ? 5 : 1, $v))->toArray()),

            'is_business' => fake()->randomElement(Broker::getFormFieldOptions('is_business')['is_business']->keys()->toArray()),

            'gender' => function (array $attributes) {
                return in_array($attributes['is_business'], [false, 'natural_person']) ? fake()->randomElement(['male', 'female']) : null;
            },
            'birth_date' => function (array $attributes) {
                return in_array($attributes['is_business'], [false, 'natural_person']) ? fake()->dateTimeInInterval('-40 years', '+20 years')->format('Y-m-d') : null;
            },
            'birth_id' => function (array $attributes) {
                if(in_array($attributes['is_business'], [false, 'natural_person'])) {
                    $b_date = date('ymd', strtotime($attributes['birth_date']));
                    $b_date = ($attributes['gender'] == 'female')
                        ? substr_replace($b_date, ((int)substr($b_date, 2, 1) + 5) % 10, 2, 1)
                        : $b_date;
                    return $b_date . fake()->unique()->numerify('####');
                } else {
                    return null;
                }
            },

            'business_id' => function (array $attributes) {
                return !!$attributes['is_business'] ? fake()->numerify('########') : null;
            },
            'business_name' => function (array $attributes) {
                return !!$attributes['is_business'] ? fake()->company() : null;
            },
            'business_tax' => function (array $attributes) {
                return !!$attributes['is_business'] ? fake()->numerify('##########') : null;
            },
            'business_vat' => function (array $attributes) {
                return !!$attributes['is_business'] ? (fake()->numerify('SK') . $attributes['business_tax']) : null;
            },
            //TODO
//            'business_register_group' => function (array $attributes) {
//                return !!$attributes['is_business'] ? null : null;
//            },
//            'business_register_subgroup' => function (array $attributes) {
//                return !!$attributes['is_business'] ? null : null;
//            },
//            'business_register_id' => function (array $attributes) {
//                return !!$attributes['is_business'] ? null : null;
//            },

            'permanent_address_id' => function (array $attributes) {
                return in_array($attributes['is_business'], [false, 'natural_person']) ? Address::factory() : null;
            },
            'temporary_address_id' => function (array $attributes) {
                return in_array($attributes['is_business'], [false, 'natural_person']) && $this->faker->boolean(25) ? Address::factory() : null;
            },
            'business_address_id' => function (array $attributes) {
                return !!$attributes['is_business'] ? Address::factory() : null;
            },
            'shipping_address_id' => function (array $attributes) {
                return $this->faker->boolean(25) ? Address::factory() : null;
            },
        ];
    }

    public function create($attributes = [], ?Model $parent = null)
    {
        $role = Arr::pull($attributes, 'broker_type', fake()->randomElement(
            Broker::getFormFieldOptions('id')['broker_type']->keys()
                ->filter(fn($v) => $v === 'agent')
//TODO doplnit dalsie                ->reject(fn($v) => $v === 'division')
                ->toArray()));

        $model = parent::create($attributes, $parent);

        ($model instanceof \Illuminate\Support\Collection ? $model : collect([$model]))->each(function ($model) use ($role) {
            $model->assignRole($role);
            if ($model->is_business !== 'legal_person') {
                $user = User::factory()->create();
                $model->users()->attach($user->id, ['is_main' => true]);
            }

            foreach (['permanent_address', 'temporary_address', 'business_address', 'shipping_address'] as $relation) {
                if($related = $model->$relation) {
                    $related->owner()->associate($model);
                    $related->save();
                }
            }

            foreach (Sector::all() as $sector) {    //TODO nastavit aj ako platne
                BrokerSector::create([
                    'sector_id' => $sector->id,
                    'broker_id' => $model->id,
                    'status' => 'unset',
                    'is_current' => 1,
                ]);
            }
        });
        return $model;
    }
}
