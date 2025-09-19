<?php

namespace App\Plugins\User\app\Http\Requests;

use App\Http\Requests\Rules\CannotChange;
use App\Http\Requests\Rules\FormFieldOption;
use App\Plugins\System\app\Http\Requests\UserStoreRequest;
use App\Plugins\System\app\Models\User;
use App\Plugins\User\app\Models\Broker;
use App\Plugins\User\app\Models\Division;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Intervention\Validation\Rules\Iban;
use Propaganistas\LaravelPhone\Rules\Phone;

class BrokerStoreRequest extends UserStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return parent::authorize();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $career_id_prefix = ($division = Division::find(request()->get('division_id'))) ? substr(optional($division)->code, 0, 3) : null;

        return array_merge(array_diff_key(parent::rules(), array_flip([
            'group_ids'
        ])), [
            'group_ids' => 'prohibited',
            'broker_type' => ['required', new FormFieldOption(Broker::class, null, 'id')],
            'division_id' => Rule::requiredIf(request()->broker_type != 'coworker'),
            'user_group_id' => Rule::requiredIf(!in_array(request()->broker_type, ['assistant', 'coworker'])),
            'career_id' => [
                Rule::excludeIf(in_array(request()->broker_type, ['assistant', 'coworker'])),
                'required', 'regex:/^[A-Za-z0-9]{3}\d{3}$/', "starts_with:$career_id_prefix", Rule::unique(Broker::class)->ignore(request()->id),
            ],

            'is_business' => ['required', new FormFieldOption(Broker::class), new CannotChange(Broker::class)],
            'birth_id'  => [Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])), 'nullable', 'size:10'],  //TODO format
            'birth_date' => [Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])), 'nullable', 'date'],
            'gender' => [Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])), new FormFieldOption(Broker::class)],
            'firstname' => Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])),
            'lastname' => Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])),

            'business_id' => [Rule::requiredIf(!!request()->is_business), 'nullable', 'size:8'],
            'business_name' => Rule::requiredIf(!!request()->is_business),
            'business_tax' => Rule::requiredIf(!!request()->is_business),
            'business_register_group' => Rule::requiredIf(!!request()->is_business),
            'business_register_subgroup' => Rule::requiredIf(request()->is_business == 'legal_person'),
            'business_register_id' => Rule::requiredIf(!!request()->is_business),

            'email' => [Rule::requiredIf(request()->is_business != 'legal_person'), 'email', Rule::unique(User::class)->ignore(request()->system_user_id)],
            'phone' => [Rule::requiredIf(request()->is_business != 'legal_person'), (new Phone)->international()->country(app()->getLocale())],          // @link https://github.com/Propaganistas/Laravel-Phone#validation

            'permanent_address_street' => [Rule::requiredIf(request()->is_business != 'legal_person')],
            'permanent_address_city' => [Rule::requiredIf(request()->is_business != 'legal_person')],
            'permanent_address_zip' => [Rule::requiredIf(request()->is_business != 'legal_person')],
            'business_address_street' => [Rule::requiredIf(!!request()->is_business)],
            'business_address_city' => [Rule::requiredIf(!!request()->is_business)],
            'business_address_zip' => [Rule::requiredIf(!!request()->is_business)],

            'citizenship' => Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])),
            'identity_card_type' => Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])),
            'identity_card_id' => Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])),
            'identity_card_until' => [
                Rule::requiredIf(in_array(request()->is_business, [false, 'natural_person'])),
                'date',
                'after_or_equal:' . now()->toDateString(),
            ],

            'representative' => [Rule::requiredIf(request()->is_business == 'legal_person'), 'array', 'min:1'],
//            'representative.*.career_id' => Rule::forEach(function (string|null $value, string $attribute) use ($career_id_prefix) {
//                return [
//                    Rule::excludeIf(!$this->input('representative.' . explode('.', $attribute)[1] . '.create_system_user')),
//                    'required', 'regex:/^\d{6}$/', "starts_with:$career_id_prefix", Rule::unique(Broker::class)->ignore(request()->id),
//                    'distinct', 'different:career_id'
//                ];
//            }),
            'representative.*.birth_id' => ['required', 'size:10'],  //TODO format
            'representative.*.birth_date' => ['required', 'date'],
            'representative.*.gender' => ['required', new FormFieldOption(Broker::class)],
            'representative.*.firstname' => 'required',
            'representative.*.lastname' => 'required',
            'representative.*.email' => ['required', 'email'/*, Rule::unique(User::class)->ignore(request()->system_user_id)*/],
            'representative.*.phone' => ['required', (new Phone)->international()->country(app()->getLocale())],          // @link https://github.com/Propaganistas/Laravel-Phone#validation
            'representative.*.permanent_address_street' => 'required',
            'representative.*.permanent_address_city' => 'required',
            'representative.*.permanent_address_zip' => 'required',
            'representative.*.citizenship' => 'required',
            'representative.*.identity_card_type' => 'required',
            'representative.*.identity_card_id' => 'required',
            'representative.*.identity_card_until' => [
                'required',
                'date',
                'after_or_equal:' . now()->toDateString(),
            ],
//            'representative.*.educational_attainment' => Rule::forEach(function (string|null $value, string $attribute) use ($career_id_prefix) {
//                return [
//                    Rule::requiredIf(!!$this->input('representative.' . explode('.', $attribute)[1] . '.create_system_user'))
//                ];
//            }),

            'activity_region_ids' => Rule::requiredIf(in_array(request()->broker_type, ['agent', 'affiliate_partner', 'employee_pfa', 'gold'])),
            'career_start_at' => Rule::requiredIf(!in_array(request()->broker_type, ['agent', 'assistant'])),
            'contract_start_at' => Rule::requiredIf(in_array(request()->broker_type, ['agent', 'division'])),
            'trust_signed_at' => Rule::requiredIf(in_array(request()->broker_type, ['agent', 'employee_sfa', 'employee_pfa'])),
            'criminal_listed_at' => Rule::requiredIf(in_array(request()->broker_type, ['agent', 'employee_sfa', 'employee_pfa']) && (request()->is_business != 'legal_person')),
            'educational_attainment' => Rule::requiredIf(in_array(request()->broker_type, ['agent', 'employee_sfa', 'employee_pfa', 'division']) && (request()->is_business != 'legal_person')),
            'iban' => App::isProduction()
                ? [Rule::requiredIf(!in_array(request()->broker_type, ['assistant'])), 'nullable', new Iban()]
                : ['nullable', new Iban()],
        ]);
    }

    public function messages()
    {
        return [
            'iban' => __('validation.incorrect_iban_error_message'),
            'identity_card_until.after_or_equal' => __(':Attribute must be on or before today.'),
            'career_id.starts_with' => __(':Attribute has to start with the prefix: :value', ['value' => ':values']),
            'representative.*.career_id.starts_with' => __(':Attribute has to start with the prefix: :value', ['value' => ':values']),
            'representative.*.career_id.distinct' => __(':Attribute is already assigned to another broker.'),
            'representative.*.career_id.different' => __(':Attribute is already assigned to another broker.'),
            'representative.*.identity_card_until.after_or_equal' => __(':Attribute must be on or before today.'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), $attributes = Broker::getFormFields()->mapWithKeys(function ($item, $name) {
            return [preg_replace('/representative\[_pending_\]\[([a-zA-Z0-9_]+)\]/', 'representative.*.$1', $name) => $item['data']['label'] ?? null];
        })->toArray(), [
            'business_register_group' => request()->is_business == 'natural_person'                                     //TODO -> nefunguje cez JsValidator
                ? __('forms.business_register_group_natural')
                : __('forms.business_register_group_legal'),
            'business_register_subgroup' => __('forms.business_register_subgroup_legal'),
            'business_register_id' => request()->is_business == 'natural_person'                                        //TODO -> nefunguje cez JsValidator
                ? __('forms.business_register_id_natural')
                : __('forms.business_register_id_legal'),
            'representative' => __('Representative'),
            'representative.*.career_id' => $attributes['representative.*.career_id'] ?? $attributes['career_id'] ?? null
        ]);
    }
}
