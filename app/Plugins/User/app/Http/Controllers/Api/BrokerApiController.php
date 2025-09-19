<?php

namespace App\Plugins\User\app\Http\Controllers\Api;

use App\Api\UserAutocompleteApi;
use App\Http\Controllers\Api\HomeApiController;
use App\Http\Controllers\Controller;
use App\Plugins\Client\app\Models\Client;
use App\Plugins\Codebook\app\Models\Address;
use App\Plugins\Product\app\Models\Sector;
use App\Plugins\System\app\Http\Controllers\Api\UserApiController;
use App\Plugins\System\app\Http\Requests\UserStoreRequest;
use App\Plugins\System\app\Models\User;
use App\Plugins\User\app\Http\Requests\BrokerStoreRequest;
use App\Plugins\User\app\Http\Requests\BrokerUpdateRequest;
use App\Plugins\User\app\Models\Broker;
use App\Plugins\User\app\Models\BrokerPartner;
use App\Plugins\User\app\Models\BrokerSector;
use App\Plugins\User\app\Models\Candidate;
use App\Plugins\User\app\Models\Group;
use App\Plugins\User\app\Models\Representative;
use App\Plugins\User\app\Notifications\Welcome;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;

class BrokerApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['can:user.broker.create'])->only(['store']);
        $this->middleware(['can:user.broker.read'])->only(['getBrokerShortNameByCareerId', 'getGuarantorByDate', 'getBrokerProductsByDate']);
        $this->middleware(['can:user.broker.update'])->only(['update', 'assignUser', 'unassignUser', 'changeCareerStatus', 'assignBrokerPartner', 'editBrokerPartner', 'destroyBrokerPartner']);
        $this->middleware(['can:user.broker.delete'])->only(['destroy']);

        $this->middleware('can_access_broker')->except(['store', 'setActive', 'getBrokerShortNameByCareerId', 'getGuarantorByDate']);
    }

    /**
     * Store a newly created user.
     *
     * @param BrokerStoreRequest $request
     * @return JsonResponse
     */
    public function store(BrokerStoreRequest $request, User $existing_user = null): JsonResponse
    {
        $request->merge([
            'birth_id' => $request->birth_id ? UserAutocompleteApi::formatBirthId($request->birth_id) : null,
            'phone'    => $request->phone ? UserAutocompleteApi::formatPhone($request->phone) : null,
            'representative' => collect($request->get('representative') ?? [])->map(function ($fields) {
                return array_merge($fields, [
                    'birth_id' => $fields['birth_id'] ? UserAutocompleteApi::formatBirthId($fields['birth_id']) : null,
                    'phone'    => $fields['phone'] ? UserAutocompleteApi::formatPhone($fields['phone']) : null,
                ]);
            })->toArray(),
        ]);

        $addresses = self::extractBrokerAddresses($request->all(), $address_fields);

        $userStoreRequest = new UserStoreRequest($request->only(app(HomeApiController::class)->createFormFields('system', 'user', true)->getOriginalContent()->keys()->toArray() ?? []));

        if (!$existing_user && $request->is_business != 'legal_person') {   //store main_user
            //TODO UserStoreRequest sa nezvaliduje!!!
            $oldUserInput = app(UserApiController::class)->store($userStoreRequest)->getOriginalContent()['_old_input'] ?? [];
            $system_user_id = $oldUserInput['id'];
        }

        $broker = new Broker();
        $broker->fill($request->except([
            ...$default_fields = ['_token', '_method', 'id', 'broker_name_other', 'broker_type', 'career_status'],
            ...$user_fields = $userStoreRequest->keys(),
            ...$address_fields,
            'representative'
        ]));

        if ($request->broker_type == 'assistant') {
            $group_id = Group::where('user_division_id', $request->division_id)
                ->whereNull('user_group_id')
                ->first()->id;
            $broker->user_group_id = $group_id;
        }

        $is_ok = [];

        array_push($is_ok, ...self::associateBrokerAddresses($broker, $addresses));

        $broker->assignRole($request->broker_type);

        $broker->career_status = 'not_active';

        $is_ok[] = $broker->save();

        $broker->regions()->sync($request->get('activity_region_ids'));

        if($request->is_business != 'legal_person') {
            if ($existing_user) {
                $broker->users()->attach($existing_user->id, ['is_main' => true]);
            } elseif ($system_user_id ?? null) {
                $broker->users()->attach($system_user_id, ['is_main' => true]);
            }
        } else {
            $representative_default_fields = ['id', 'representative_id'];
            $request->merge(['representative' => $representatives = collect($request->get('representative') ?? [])->mapWithKeys(function ($fields, $key) use (&$is_ok, $representative_default_fields, $broker, $request) {
                $addresses = self::extractBrokerAddresses($fields, $address_fields);

                $representative = Representative::findOrNew(($key > 0) ? $key : null);
                $representative->fill(array_diff_key($fields, array_flip([...$representative_default_fields, ...$address_fields])));
                $representative->broker_id = $broker->id;

                array_push($is_ok, ...self::associateBrokerAddresses($representative, $addresses));

                $is_ok[] = $representative->save();

                if ($fields['create_system_user'] ?? false) {
                    if ($user = User::where('email', $fields['email'])->first()) {
                        $user_id = $user->id;
                    } else {
                        $userStoreRequest = new UserStoreRequest(array_intersect_key($fields, array_flip(app(HomeApiController::class)->createFormFields('system', 'user', true)->getOriginalContent()->keys()->toArray() ?? [])));
                        $userStoreResponse = app(UserApiController::class)->store($userStoreRequest);
                        $user_id = $userStoreResponse->getOriginalContent()['_old_input']['id'];
                    }

                    $broker->users()->attach($user_id, ['is_main' => false]);
                }

                return [$new_key = $representative->id => array_merge($fields, ['representative_id' => $new_key])];
            })->toArray()]);
            $broker->representatives()->whereNotIn('id', array_keys($representatives))->update(['broker_id' => null]);
        }

        if ($candidate = Candidate::find($request->candidate_id)) {
            foreach ($candidate->identity_files as $file) {
                $file->subject_type = get_class($broker);
                $file->subject_id = $broker->id;

                $file->save();
            }

            $is_ok[] = $candidate->update([
                'broker_id' => $broker->id,
                'division_id' => $request->division_id,
                'status' => 'created',
            ]);
        }

        if(!in_array(FALSE, $is_ok)) {
            $this->copyBrokerSectors($broker);

            return response()->json(['message' => response_message('success_text', 1, ['model' => lcfirst(__('Broker'))]),
                '_old_input' => array_merge($request->all(), ['id' => $broker->id])
            ]);
        } else {
            abort(400, response_message('error_text', 1, ['model' => lcfirst(__('Broker'))]));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BrokerUpdateRequest $request
     * @param Broker $broker
     * @return JsonResponse
     */
    public function update(BrokerUpdateRequest $request, Broker $broker): JsonResponse
    {
        $request->merge([
            'birth_id' => $request->birth_id ? UserAutocompleteApi::formatBirthId($request->birth_id) : null,
            'representative' => $request->is_business == 'legal_person' ? collect($request->get('representative') ?? [])->map(function ($fields) {
                return array_merge($fields, [
                    'birth_id' => $fields['birth_id'] ? UserAutocompleteApi::formatBirthId($fields['birth_id']) : null,
                    'phone'    => $fields['phone'] ? UserAutocompleteApi::formatPhone($fields['phone']) : null,
                ]);
            })->toArray() : [],
        ]);

        $addresses = self::extractBrokerAddresses($request->all(), $address_fields);

        $is_ok = [];

        $broker->fill($request->except([
            ...$default_fields = ['_token', '_method', 'id', 'broker_name_other', 'broker_type', 'career_status'],
            ...$address_fields,
            'representative'
        ]));

        array_push($is_ok, ...self::associateBrokerAddresses($broker, $addresses));

        $is_ok[] = $broker->save();

        $broker->regions()->sync($request->get('activity_region_ids'));

        $representative_default_fields = ['id', 'representative_id'];
        $request->merge(['representative' => $representatives = collect($request->get('representative') ?? [])->mapWithKeys(function ($fields, $key) use (&$is_ok, $representative_default_fields, $broker) {
            $addresses = self::extractBrokerAddresses($fields, $address_fields);

            $representative = Representative::findOrNew(($key > 0) ? $key : null);
            $representative->fill(array_diff_key($fields, array_flip([...$representative_default_fields, ...$address_fields])));
            $representative->broker_id = $broker->id;

            array_push($is_ok, ...self::associateBrokerAddresses($representative, $addresses));

            $is_ok[] = $representative->save();

            return [$new_key = $representative->id => array_merge($fields, ['representative_id' => $new_key])];
        })->toArray()]);
        $broker->representatives()->whereNotIn('id', array_keys($representatives))->update(['broker_id' => null]);

        if(!in_array(FALSE, $is_ok)) {
            return response()->json(['message' => response_message('success_text', 1, ['model' => lcfirst(__('Broker'))]),
                '_old_input' => array_merge($request->all(), ['id' => $broker->id])
            ]);
        } else {
            abort(400, response_message('error_text', 1, ['model' => lcfirst(__('Broker'))]));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Broker $broker
     * @return JsonResponse
     */
    public function destroy(Broker $broker): JsonResponse
    {
        if($broker->delete()) {
            return response()->json(['message' => response_message('destroy.success_text', 1, ['model' => lcfirst(__('Broker'))])]);
        } else {
            abort(400, response_message('destroy.error_text', 1, ['model' => lcfirst(__('Broker'))]));
        }
    }

    /**
     * Change the active broker for the current user.
     *
     * @param Broker $broker
     * @return JsonResponse
     */
    public function setActive(int|string $broker) {
        if(!$broker_id = Broker::whereHas('users', function($q) {
            $q->where('system_users.id', auth()->id());
        })->where('id', $broker)->value('id')) {
            throw new AuthorizationException;
        }

        session()->put('active_broker_id', $broker_id);
        if(auth()->id()) {
            auth()->user()->active_broker_id = $broker_id;
            auth()->user()->save();
        }

        return response()->json(['success' => true]);
    }

    public function assignUser(Request $request, string $broker): JsonResponse
    {
        $user = User::where('email', $request->get('assign_user_email'))->first();

        if ($user) {
            $user->brokers()->syncWithoutDetaching([$broker => ['is_main' => false]]);

            return response()->json(['message' => response_message('success_text', 1, ['model' => lcfirst(__('User'))]),]);
        } else {
            abort(400, __('User with given email does not exist.'));
        }
    }

    public function unassignUser(Request $request, string $broker): JsonResponse
    {
        $user = User::find($request->get('user_id'));

        if ($user) {
            $relation = $user->brokers()->wherePivot('broker_id', $broker)->first();

            if ($relation && $relation->pivot->is_main == 0) {
                $user->brokers()->detach($broker);

                return response()->json(['message' => response_message('success_text', 1, ['model' => lcfirst(__('User'))]),]);
            } else {
                return response()->json([
                    'message' => __('The main user cannot be removed.')
                ], 400);
            }
        } else {
            abort(400, __('User with given ID does not exist.'));
        }
    }

    public function assignBrokerPartner(Request $request, Broker $broker): JsonResponse
    {
        $request->validate([
            'partner_id' => 'required',
            'external_id' => 'required',
        ]);

        $broker->brokerPartners()->create([
            'partner_id' => $request->partner_id,
            'external_id' => $request->external_id,
        ]);
        return response()->json(['message' => response_message('success_text', 1, ['model' => lcfirst(__('BrokerPartner'))]),]);
    }

    public function editBrokerPartner(Request $request, Broker $broker, BrokerPartner $brokerPartner): JsonResponse
    {
        $request->validate([
            'external_id' => 'required',
        ]);

        $brokerPartner->update([
            'external_id' => $request->external_id,
        ]);

        return response()->json(['message' => response_message('success_text', 1, ['model' => lcfirst(__('BrokerPartner'))]),]);
    }

    public function destroyBrokerPartner(Broker $broker, BrokerPartner $brokerPartner): JsonResponse
    {
        if($brokerPartner->delete()) {
            return response()->json(['message' => response_message('destroy.success_text', 1, ['model' => lcfirst(__('BrokerPartner'))])]);
        } else {
            abort(400, response_message('destroy.error_text', 1, ['model' => lcfirst(__('BrokerPartner'))]));
        }
    }

    private function extractBrokerAddresses(array $data, ?array &$address_fields): array {
        $address_fields = $address_fields ?? [];
        foreach (['permanent_address', 'temporary_address', 'business_address', 'shipping_address'] as $relation) {
            if(key_exists($address_other = $relation . '_other', $data)) {
                $address_fields[] = $address_other;
                foreach (['street', 'city', 'zip'] as $column) {
                    $attributes[$column] = $data[$address_fields[] = $relation . '_' . $column] ?? null;
                }
                if ($data[$address_other] ?? false) {
                    $addresses[$relation] = $attributes;
                } else {
                    $addresses[$relation] = false;
                }
            }
        }
        return $addresses ?? [];
    }

    private function associateBrokerAddresses(Broker|Representative $broker, array $addresses): array {
        if (!$broker->exists && !empty($addresses)) {$is_ok[] = $broker->save();}
        foreach ($addresses as $relation => $data) {
            if ($data) {
                $is_ok[] = ($address = Address::firstOrNew([
                    'owner_type' => $broker->getMorphClass(),
                    'owner_id' => $broker->getKey(),
                    ...$data
                ]))->save();
                $broker->$relation()->associate($address);
            } else {
                $broker->$relation()->dissociate();
            }
        }
        return $is_ok ?? [];
    }

    public function changeCareerStatus(Request $request, Broker $broker): JsonResponse
    {
        if ($broker->career_status == 'active') {
            $broker->career_status = 'not_active';
        } else {
            $broker->career_status = 'active';
            if ($request->boolean('with_mail')) {
                foreach ($broker->users as $user) {
                    Notification::route('mail', [
                        $user->email => $user->fullname ?? $user->email
                    ])->notify(new Welcome($broker));
                    break;
                }
            }
        }
        $broker->save();

        return response()->json(['success' => true]);
    }

    private function copyBrokerSectors($broker): void
    {
        if (in_array($broker->roles->first()->name, ['agent', 'employee_sfa', 'employee_pfa'])) {
            $broker_with_given_birth_id = Broker::where('birth_id', $broker->birth_id)
                ->whereNot('id', $broker->id)
                ->where('career_status', 'active')
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['agent', 'employee_sfa', 'employee_pfa']);
                })
                ->first();

            if ($broker_with_given_birth_id) {
                $existing_sectors = BrokerSector::where('broker_id', $broker_with_given_birth_id->id)
                    ->where('is_current', 1)
                    ->get();

                $sectorsToInsert = $existing_sectors->map(function ($sector) use ($broker) {
                    $newSector = $sector->replicate();
                    $newSector->broker_id = $broker->id;
                    $newSector = $newSector->getAttributes();
                    return $newSector;
                });

                BrokerSector::insert($sectorsToInsert->toArray());
            } else {
                foreach (Sector::all() as $sector) {
                    BrokerSector::create([
                        'sector_id' => $sector->id,
                        'broker_id' => $broker->id,
                        'status' => 'unset',
                        'is_current' => 1,
                    ]);
                }
            }
        }
    }

    public function getBrokerShortNameByCareerId(Request $request, string $career_id): JsonResponse
    {
        if(($career_id) && ($model = Broker::query()->where('career_id', $career_id)->first())) {
            return response()->json(Arr::only(Broker::getFormFieldsValues($model), ['broker_short_name']));
        } else {
            return response()->json();
        }
    }

    public function getGuarantorByDate(string $garant_id, $date): JsonResponse
    {
        if (($broker = Broker::find($garant_id)) && ($data = Broker::getFormFieldsValues($broker, [
                'filled_at' => $date,
            ]))) {
            Client::splitClientInputCollection(collect($data), $dividedFields);
            return response()->json(array_merge(
                isset($dividedFields['broker_fields']) ? $dividedFields['broker_fields']->toArray() : [],
                isset($dividedFields['broker_fields_a']) ? $dividedFields['broker_fields_a']->toArray() : [],
                isset($dividedFields['broker_fields_b']) ? $dividedFields['broker_fields_b']->toArray() : [],
                $broker->is_business ? ['broker_business_name' => $broker->business_name] : [],
                ['broker_is_business' => $broker->is_business]
            ));
        } else {
            return response()->json();
        }
    }

    public function getBrokerProductsByDate(Request $request, Broker $broker, $date)
    {
        $sectors = $broker->sectorsValidOnDate($date);

        if($sector_id = $request->get('filters')['sector_id'] ?? null) {
            $sectors = $sectors->only($sector_id);
        }

        $products = collect();
        $sectors->each(function ($sector) use (&$products) {
            $sectorProducts = $sector->products();
            $sectorProducts->each(function ($value, $key) use (&$products) {
                $products[$key] = $value;
            });
        });

        return $products->sortKeys();
    }
}
