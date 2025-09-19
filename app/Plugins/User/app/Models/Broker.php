<?php

namespace App\Plugins\User\app\Models;

use App\Plugins\Client\app\Models\Client;
use App\Plugins\Codebook\app\Models\Address;
use App\Plugins\Codebook\app\Models\Region;
use App\Plugins\Commission\app\Models\StornoCommission;
use App\Plugins\Product\app\Models\Sector;
use App\Plugins\System\app\Models\User;
use App\Traits\HasBreadcrumb;
use App\Traits\HasDriveFile;
use App\Traits\HasFactory;
use App\Traits\HasForm;
use App\Traits\HasPermissions;
use App\Traits\HasSelfChildren;
use App\Traits\HasTable;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Yajra\DataTables\DataTables;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class Broker extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToThroughTrait;

    use HasForm, HasTable, HasBreadcrumb, HasDriveFile, HasSelfChildren;

    use HasForm, HasTable {
        getFormFields as traitGetFormFields;
        getFormField as traitGetFormField;
        getTableField as traitGetTableField;
    }

    use HasSelfChildren {
        getAllSelfChildrenIdsAttribute as traitGetAllSelfChildrenIdsAttribute;
    }

    use HasRoles, HasPermissions {
        HasPermissions::scopePermission insteadof HasRoles;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_brokers';

    protected $guard_name = 'web';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('withFullname', function (Builder $builder) {
            $builder->withFullname();
        });
        static::addGlobalScope('orderByCareerId', function (Builder $builder) {
            $builder->orderBy('career_id');
        });
    }

    public function scopeActiveCareer(Builder $query): void
    {
        $query->where('career_status', 'active');
    }

    public function scopeBrokerAccess(Builder $query, Broker|int|string $broker = null, bool $with_inactive_career = false): void
    {
        if(!$broker) {
            $broker = optional(optional(auth()->user())->activeBroker);
        } elseif(!$broker instanceof Broker) {$broker = Broker::query()->find($broker);}

        if(optional(auth()->user())->hasAnyRole(['master', 'admin'])) {
            //vsetci
        } else {
            $query->whereIn('id', $broker->getAllSelfChildrenIdsAttribute(null, $with_inactive_career) ?? []);
        }
    }


    public function scopeWithCompleteName(Builder $query): void
    {
        $query->with(['roles', 'users' => function ($q) {
            $q->select((new User())->getTable().'.*');
            $q->addSelect([DB::raw(User::getFullnameRawSelect()), (new User())->getTable() . '.id']);
        }]);
    }

    public function scopeWithFullname(Builder $query): void
    {
        $query->with(['users' => function ($q) {
            $q->select((new User())->getTable().'.*');
            $q->addSelect([DB::raw(User::getFullnameRawSelect()), (new User())->getTable() . '.id']);
        }]);
    }

    /**
     * Get the raw column select for complete name.
     *
     * @param bool $title
     * @param string|false|null $as => [null => 'Select ... as complete_name' || string => 'Select ... as something' || false => 'Select ...']
     * @return Expression|string
     */
    public static function getCompleteNameRawSelect(bool $title = true, string|false $as = 'complete_name', bool $as_string = false): Expression|string
    {
        $self = new self();
        $self_class = str_replace('\\', '\\\\', get_class($self));

        $as = $as == false ? '' : "as $as";

        $string = "CONCAT(
                " . self::getFullnameRawSelect($title, false, true) . ",
                IF(EXISTS(" . self::getBrokerTypeRawSelect(false, false, true) . "),
                    CONCAT(' | ', " . self::getBrokerTypeRawSelect(true, false, true) . "), ''),
                IF(TRIM(career_id) != '', CONCAT(' (', career_id , ')'), '')
            ) $as";
        return $as_string ? $string : DB::raw($string);
    }

    /**
     * Get the raw column select for short name.
     *
     * @param bool $firstname
     * @param string|false|null $as => [null => 'Select ... as short_name' || string => 'Select ... as something' || false => 'Select ...']
     * @return Expression|string
     */
    public static function getShortNameRawSelect(bool $fullname = false, string|false $as = 'short_name', bool $as_string = false): Expression|string
    {
        $self = new self();
        $self_class = str_replace('\\', '\\\\', get_class($self));

        $as = $as == false ? '' : "as $as";

        $string = "CONCAT(
                IF(TRIM(career_id) != '', CONCAT(career_id , ' | '), ''),
                " . /*$fullname ? self::getFullnameRawSelect(false, false, true) : */self::getNameRawSelect(false, true) . "
            ) $as";
        return $as_string ? $string : DB::raw($string);
    }

    /**
     * Get the raw column select for fullname.
     *
     * @param bool $title
     * @param string|false|null $as => [null => 'Select ... as fullname' || string => 'Select ... as something' || false => 'Select ...']
     * @return Expression|string
     */
    public static function getFullnameRawSelect(bool $title = true, string|false $as = 'fullname', bool $as_string = false): Expression|string
    {
        $self = new self();
        $as = $as == false ? '' : "as $as";
        $string = "IF(is_business = 'legal_person', business_name,
                      IF(TRIM(" . ($broker_name_concat = $title
                            ? "CONCAT(IF(ISNULL(broker_title_before), '', CONCAT(broker_title_before, ' ')), IF(ISNULL(broker_firstname), '', CONCAT(broker_firstname, ' ')), IF(ISNULL(broker_lastname), '', broker_lastname), IF(ISNULL(broker_title_after), '', CONCAT(' ', broker_title_after)))"
                            : "CONCAT(IF(ISNULL(broker_firstname), '', CONCAT(broker_firstname, ' ')), IF(ISNULL(broker_lastname), '', broker_lastname))") . ") != '', " . $broker_name_concat . ",
                        (" . ($model_relation = $self->main_user())->getQuery() //NOTE: Pouzita main_user() relation query ale nahradene where broker_id = ? za vazbu user_user_broker.broker_id = user_brokers.id a select fullname
                                ->tap(function (Builder $query) use ($model_relation) {
                                    $query->getQuery()->wheres = collect($query->getQuery()->wheres)->reject(function ($where) use ($model_relation) {return $where['type'] === 'Null' && $where['column'] === $model_relation->getQualifiedFirstKeyName();})->toArray();
                                })
                                ->whereColumn($model_relation->getQualifiedFirstKeyName(), '=', $model_relation->getQualifiedLocalKeyName())
                                ->selectRaw(User::getFullnameRawSelect($title, false, true))
                                ->limit(1)->toRawSql() . "))
                    ) $as";
        return $as_string ? $string : DB::raw($string);
    }

    public static function getNameRawSelect(string|false $as = 'name', bool $as_string = false): Expression|string
    {
        $self = new self();
        $as = $as == false ? '' : "as $as";
        $string = "IF(is_business = 'legal_person', business_name,
                      IF(TRIM(" . ($broker_name_concat = "CONCAT(IF(ISNULL(broker_firstname), '', CONCAT(LEFT(broker_firstname, 1), '. ')), IF(ISNULL(broker_lastname), '', broker_lastname))") . ") != '', " . $broker_name_concat . ",
                            (" . ($model_relation = $self->main_user())->getQuery() //NOTE: Pouzita main_user() relation query ale nahradene where broker_id = ? za vazbu user_user_broker.broker_id = user_brokers.id a select fullname
                                    ->tap(function (Builder $query) use ($model_relation) {
                                        $query->getQuery()->wheres = collect($query->getQuery()->wheres)->reject(function ($where) use ($model_relation) {return $where['type'] === 'Null' && $where['column'] === $model_relation->getQualifiedFirstKeyName();})->toArray();
                                    })
                                    ->whereColumn($model_relation->getQualifiedFirstKeyName(), '=', $model_relation->getQualifiedLocalKeyName())
                                    ->selectRaw(User::getNameRawSelect(false, true))
                                    ->limit(1)->toRawSql() . "))
                    ) $as";
        return $as_string ? $string : DB::raw($string);
    }

    public static function getBrokerTypeRawSelect(bool $short = false, string|false $as = 'broker_type', bool $as_string = false): Expression|string
    {
        $self = new self();
        $self_class = str_replace('\\', '\\\\', get_class($self));

        $as = $as == false ? '' : "as $as";
        $string = "(SELECT " . (/*TODO $short ? 'short' : */'name') . "
                            FROM `roles`
                            INNER JOIN `model_has_roles`
                            ON `roles`.`id` = `model_has_roles`.`role_id`
                            WHERE `model_has_roles`.`model_id` = `{$self->getTable()}`.`id`
                              AND `model_has_roles`.`model_type` = '$self_class'
                            LIMIT 1) $as";
        return $as_string ? $string : DB::raw($string);
    }

    /**
     * Get the raw column select for business register id.
     *
     * @param string|false|null $as => [null => 'Select ... as business_register_id' || string => 'Select ... as something' || false => 'Select ...']
     * @return Expression
     */
    public static function getBusinessRegisterRawSelect(string|false $as = 'business_register_id'): Expression
    {
        $as = $as == false ? '' : "as $as";
        return DB::raw("CONCAT(IF(ISNULL(business_register_group), '', CONCAT(business_register_group, ', ')), IF(ISNULL(business_register_subgroup), '', CONCAT(business_register_subgroup, ', ')), IF(ISNULL(business_register_id), '', business_register_id)) " . $as);
    }

    public static function splitBrokerInputCollection(Collection $fields, &$dividedFields, array $filters = []): Collection
    {
        $tempFields = collect();
        foreach ([
                'broker_fields' => [
                    'division_id', 'broker_type',
                    'career_id', 'career_status',
                    'user_group_id', 'parent_broker_id',
                    'create_system_user',
                ],
                'personal_fields' => ['is_business', 'birth_id', 'birth_date', 'gender', 'title_before', 'title_after', 'firstname', 'lastname', 'broker_name_other', 'broker_title_before', 'broker_title_after', 'broker_firstname', 'broker_lastname', 'business_id', 'business_name', 'business_tax', 'business_vat', 'business_register_group', 'business_register_subgroup', 'business_register_id'],
                'contact_fields' => ['email', 'phone', 'profile_photo_path'],
                'permanent_address_fields' => ['permanent_address_other', 'permanent_address_street', 'permanent_address_city', 'permanent_address_zip'],
                'temporary_address_fields' => ['temporary_address_other', 'temporary_address_street', 'temporary_address_city', 'temporary_address_zip'],
                'business_address_fields' => ['business_address_other', 'business_address_street', 'business_address_city', 'business_address_zip'],
                'shipping_address_fields' => ['shipping_address_other', 'shipping_address_street', 'shipping_address_city', 'shipping_address_zip'],
                'identity_fields' => ['citizenship', 'identity_card_type', 'identity_card_id', 'identity_card_until', 'identity_card_file_path'],
                'representative_fields' => ($filters['is_representative'] ?? false)
                    ? ['representative_id', 'representative_type', /*'representative_type_other'*/]
                    : [...$fields->filter(fn ($settings, $field) => substr($field, 0, 15) === 'representative[')->keys()->toArray()],
                'broker_info_fields' => [
                    'activity_region_ids', 'previous_sfa',
                    'career_start_at', 'career_start_type', 'contract_start_at', 'trust_signed_at', 'criminal_listed_at',
                    'career_exit_at', 'career_exit_type', 'career_exit_note', 'contract_exit_at', 'termination_at',
                ],
                'broker_nbs_fields' => ['nbs_number', 'nbs_start_register_at', 'nbs_start_at', 'nbs_exit_register_at', 'nbs_exit_at'],
                'broker_nbs_sector_fields' => [...$fields->filter(fn ($settings, $field) => substr( $field, 0, strpos($field, '[') + 1 ) === 'nbs_sectors[')->keys()->toArray()],
                'other_fields' => ['disburse_hr', 'disburse_hr_note', 'disburse_provision', 'disburse_provision_note', 'has_storno', 'storno_value', 'is_ror'],
                'candidate_fields' => ['candidate_email']
            ] as $group => $groupFields) {
            if(($filters['is_representative'] ?? false)) {
                $groupFields = array_map(fn($key) => "representative[_pending_][$key]", $groupFields);
            }
            $fields = splitInputCollection($fields, $groupFields);
            $tempFields[$group] = $groupFields;
        }
        $dividedFields = $tempFields;
        return splitInputCollection($fields);
    }



    public function division(): BelongsToThrough
    {
        return $this->belongsToThrough(Division::class, [Group::class]);
    }

    public function candidate(): belongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id')->withoutGlobalScopes();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'user_group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Broker::class, 'parent_broker_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_user_broker', 'broker_id', 'system_user_id');
    }

    public function main_user(): HasOneThrough
    {
        $pivot = new UserBroker;
        return $this->hasOneThrough(User::class, UserBroker::class,
            $pivot->broker()->getQualifiedForeignKeyName(),
            $pivot->user()->getQualifiedOwnerKeyName(),
            $this->getKeyName(),
            $pivot->user()->getQualifiedForeignKeyName(),
        )->where(function (Builder $query) use ($pivot) {
            $query->where($pivot->getTable() . '.is_main', true);
        });
    }

    public function representatives(): HasMany
    {
        return $this->hasMany(Representative::class);
    }

    public function brokerSectors(): HasMany
    {
        return $this->hasMany(BrokerSector::class, 'broker_id', 'id');
    }

    public function brokerCurrentSectors()
    {
        return $this->brokerSectors()->where('is_current', 1);
    }

    public function permanent_address(): BelongsTo {
        return $this->belongsTo(Address::class);
    }

    public function temporary_address(): BelongsTo {
        return $this->belongsTo(Address::class);
    }

    public function business_address(): BelongsTo {
        return $this->belongsTo(Address::class);
    }

    public function shipping_address(): BelongsTo {
        return $this->belongsTo(Address::class);
    }

    public function representatives_brokers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, BrokerRelation::class, 'receiving_broker_id', 'sending_broker_id')
            ->wherePivot('type', 'responsible_executive');
    }

    public function guarantors(): BelongsToMany
    {
        return $this->belongsToMany(self::class, BrokerRelation::class, 'receiving_broker_id', 'sending_broker_id')
            ->wherePivot('type', 'garant');
    }

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(self::class, BrokerRelation::class, 'receiving_broker_id', 'sending_broker_id')
            ->wherePivot('type', 'mentor');
    }

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'user_broker_region', 'broker_id', 'region_id');
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class, 'user_broker_sectors')
            ->withPivot(['is_current', 'status', 'nbs_start_at', 'nbs_exit_at'])
            ->orderBy('product_sectors.id');
    }

    public function sectorsValidOnDate($date)
    {
        $date = Carbon::parse($date);

        return $this->sectors->whereNotNull('pivot.nbs_start_at')->filter(function ($sector) use ($date) {
            $start_at = Carbon::parse($sector->pivot->nbs_start_at);

            $exit_at = $sector->pivot->nbs_exit_at
                ? Carbon::parse($sector->pivot->nbs_exit_at)
                : $start_at->copy()->addYear();

            return $date->between($start_at, $exit_at);
        });
    }

    public function clients(): belongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_client_broker', 'broker_id', 'client_id');
    }

    public function stornofonds(): HasMany
    {
        return $this->hasMany(StornoCommission::class, 'broker_id');
    }

    public function activeStornofonds(): HasMany
    {
        return $this->stornofonds()->where('status', 'blocked');
    }

    public function brokerPartners()
    {
        return $this->hasMany(BrokerPartner::class);
    }


    /**
     * Get the broker's complete name.
     *
     * @return string
     */
    public function getCompleteNameAttribute()
    {
        return $this->attributes['complete_name'] ?? ($this->career_id ? "$this->career_id | " : '') . (($broker_type = optional($this->roles->first())->name) ? ('' . __('forms.broker_types.' . $broker_type) . ' | ') : '') . $this->fullname;
    }
//
//    /**
//     * Get the broker's short ame.
//     *
//     * @return string
//     */
//    public function getShortFullnameAttribute()
//    {
//        return $this->attributes['short_fullname'] ?? ($this->career_id ? "$this->career_id | " : '') . $this->fullname_short;
//    }

    /**
     * Get the broker's short ame.
     *
     * @return string
     */
    public function getShortNameAttribute()
    {
        return $this->attributes['short_name'] ?? ($this->career_id ? "$this->career_id | " : '') . $this->name;
    }

    /**
     * Get the broker's fullname.
     *
     * @return string
     */
    public function getFullnameAttribute()
    {
        return $this->attributes['fullname'] ?? ($this->is_business == 'legal_person' ? $this->business_name : (!empty($broker_name = trim("{$this->broker_title_before} {$this->broker_firstname} {$this->broker_lastname} {$this->broker_title_after}")) ? $broker_name : optional($this->main_user)->fullname));
    }
//
//    /**
//     * Get the broker's name.
//     *
//     * @return string
//     */
//    public function getFullnameShortAttribute()
//    {
//        return $this->attributes['fullname_short'] ?? ($this->is_business == 'legal_person' ? $this->business_name : (!empty($broker_name = trim($this->broker_firstname . ' ' . $this->broker_lastname)) ? $broker_name : optional($this->main_user)->fullname_short));
//    }

    /**
     * Get the broker's name.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? ($this->is_business == 'legal_person' ? $this->business_name : (!empty($broker_name = trim((!empty($this->broker_firstname) ? (substr($this->broker_firstname, 0, 1) . '. ') : '') . $this->broker_lastname)) ? $broker_name : optional($this->main_user)->name));
    }

    /**
     * Get the broker's firstname.
     *
     * @return string
     */
    public function getFirstnameAttribute()
    {
        return $this->attributes['firstname'] ?? ($this->is_business == 'legal_person' ? $this->business_name : (!empty($broker_name = trim("$this->broker_firstname")) ? $broker_name : optional($this->main_user)->firstname));
    }
//
//    /**
//     * Get the broker's lastname.
//     *
//     * @return string
//     */
//    public function getLastnameAttribute()
//    {
//        return $this->attributes['lastname'] ?? ($this->is_business == 'legal_person' ? $this->business_name : (!empty($broker_name = trim("$this->broker_lastname")) ? $broker_name : optional($this->main_user)->lastname));
//    }

    public function getBrokerTypeAttribute() {
        return isset(optional($this->roles)->first()->name) ? __('forms.broker_types.' . optional($this->roles)->first()->name) : null;
    }

    public function getAddressAttribute()
    {
        return $this->attributes['address'] ?? ($this->is_business ?  optional($this->business_address)->address : ($this->permanent_address->address ?? null));
    }

    public function getRepresentativeFullnameAttribute()
    {
        return $this->attributes['representative_fullname'] ?? ($this->main_user ? $this->main_user->fullname : ($this->representatives->first() ? $this->representatives->first()->fullname : null));
    }

    public function getRepresentativeEmailAttribute()
    {
        return $this->attributes['representative_email'] ?? ($this->main_user ? $this->main_user->email : ($this->representatives->first() ? $this->representatives->first()->email : null));
    }

    public function getRepresentativePhoneAttribute()
    {
        return $this->attributes['representative_phone'] ?? ($this->main_user ? $this->main_user->phone : ($this->representatives->first() ? $this->representatives->first()->phone : null));
    }

    public static function getCandidateField(Candidate $candidate, $field) {
        return optional($candidate)->data[$field] ?? null;
    }

    private static function getSystemUserRolesArray(): array {
        return ['master', 'admin', 'user'];
    }

    public function getTotalBlockedCommissionAttribute(): float
    {
        return $this->attributes['total_blocked_commission'] ?? $this->activeStornofonds->sum('blocked_commission');
    }



    //HasForm
    public static function getFormFields(Broker $model = null, array $filters = [], array $exceptFields = null): Collection
    {
        if($model) {
            $filters['current_broker'] = $model;
        }

        $fields = self::traitGetFormFields($model, $filters, $exceptFields);

        $user_filters = array_merge($filters, []);

        $fields = User::getFormFields($filters['assign_user'] ?? null ?? optional($model)->main_user ?? null, $user_filters, $exceptFields)
            ->map(function ($item, $key) {
                if(in_array($key, ['title_before', 'title_after'])) {
                    $item['wrapClass'] = 'col-md-2';
                } elseif(in_array($key, ['firstname', 'lastname'])) {
                    $item['wrapClass'] = 'col-md-4';
                } elseif(in_array($key, ['phone', 'email'])) {
                    $item['wrapClass'] = 'col-md-6';
                }
                return $item;
            })->except(['id', 'group_ids'])->merge($fields);

        if(!($filters['only_show'] ?? null)) {
            $fields = $fields->except(['career_status']);
        }

        if ($filters['only_assign_user'] ?? null) {
            return $fields->only(['assign_user_email']);
        }

        if ($filters['only_commission_tree'] ?? null) {
            return $fields->only(['commission_tree_date']);
        }

        if(($filters['only_create'] ?? null) && !($filters['assign_user'] ?? null)) { //Iba pri create (nie assign_user @see App\Plugins\User\app\Http\Controllers\BrokerController::assignation)
            $fields = $fields->except([
                'broker_name_other', 'broker_title_before', 'broker_firstname', 'broker_lastname', 'broker_title_after',
                'career_exit_at', 'career_exit_type', 'career_exit_note', 'contract_exit_at', 'termination_at',
            ]);
        } elseif (($filters['only_create'] ?? null) && ($filters['assign_user'] ?? null)) {
            $fields = $fields->except([
                'career_exit_at', 'career_exit_type', 'career_exit_note', 'contract_exit_at', 'termination_at',
                'title_before', 'firstname', 'lastname', 'title_after',
                'email', 'phone', 'profile_photo_path'
            ]);
        } else {
            $fields = $fields->except([
                'title_before', 'firstname', 'lastname', 'title_after',
                'email', 'phone', 'profile_photo_path'
            ]);
        }

        if(($filters['division_create'] ?? null)) {
            $fields = self::splitBrokerInputCollection($fields, $dividedFields, $filters);
            $fields = $fields->except(['educational_attainment'])
                ->merge($dividedFields['personal_fields'] ?? [])
                ->merge($dividedFields['contact_fields'] ?? [])->except('profile_photo_path')
                ->merge($dividedFields['permanent_address_fields'] ?? [])
                ->merge($dividedFields['temporary_address_fields'] ?? [])
                ->merge($dividedFields['business_address_fields'] ?? [])
                ->merge($dividedFields['shipping_address_fields'] ?? [])
                ->merge($dividedFields['identity_fields'] ?? [])
                ->merge($dividedFields['representative_fields'] ?? [])
            ;
        } elseif($candidate_form = ($filters['candidate_form'] ?? null)) {

            if(in_array($candidate_form, ['create', 'edit'])) {
                $fields = self::splitBrokerInputCollection($fields, $dividedFields, $filters)->except(['id']);
                $fields = $fields
                    ->merge(collect($dividedFields['broker_fields'] ?? [])->except(['career_id', 'external_id', 'career_status',]))
                    ->merge(collect($dividedFields['personal_fields'] ?? []))
                    ->merge(collect($dividedFields['contact_fields'] ?? [])->except(['profile_photo_path']))
                    ->merge(collect($dividedFields['permanent_address_fields'] ?? []))
                    ->merge(collect($dividedFields['temporary_address_fields'] ?? []))
                    ->merge(collect($dividedFields['business_address_fields'] ?? []))
                    ->merge(collect($dividedFields['shipping_address_fields'] ?? []))
                    ->merge(collect($dividedFields['identity_fields'] ?? []))
                    ->merge(collect($dividedFields['representative_fields'] ?? []))
                    ->merge(collect($dividedFields['broker_info_fields'] ?? [])->only(['activity_region_ids', 'previous_sfa']))
                    ->merge(collect($dividedFields['broker_nbs_sector_fields'] ?? []))
                    ->merge(collect($dividedFields['other_fields'] ?? [])->only(['has_gold_contract']))
                    ->merge(collect($dividedFields['candidate_fields'] ?? []))
                ;
            } elseif(in_array($candidate_form, ['form'])) {
                $fields = self::splitBrokerInputCollection($fields, $dividedFields, $filters)->except(['id']);
                $fields = $fields
                    ->merge(collect($dividedFields['broker_fields'] ?? [])->only(['broker_type']))
                    ->merge(collect($dividedFields['personal_fields'] ?? []))
                    ->merge(collect($dividedFields['contact_fields'] ?? [])->except(['profile_photo_path']))
                    ->merge(collect($dividedFields['permanent_address_fields'] ?? []))
                    ->merge(collect($dividedFields['temporary_address_fields'] ?? []))
                    ->merge(collect($dividedFields['business_address_fields'] ?? []))
                    ->merge(collect($dividedFields['shipping_address_fields'] ?? []))
                    ->merge(collect($dividedFields['identity_fields'] ?? []))
                    ->merge(collect($dividedFields['representative_fields'] ?? []))
                    ->merge(collect($dividedFields['broker_info_fields'] ?? [])->only(['activity_region_ids', 'previous_sfa']))
                    ->merge(collect($dividedFields['broker_nbs_sector_fields'] ?? []))
                    ->merge(collect($dividedFields['other_fields'] ?? [])->only(['has_gold_contract']))
                ;
                //TODO pri PO field na FO + jeho OP
            } elseif(in_array($candidate_form, ['show'])) {
                $fields = self::splitBrokerInputCollection($fields, $dividedFields, $filters)->except(['id']);
                $fields = $fields->except(['educational_attainment', 'iban'])
                    ->merge(collect($dividedFields['broker_fields'] ?? [])->except(['career_id', 'external_id', 'career_status',]))
                    ->merge(collect($dividedFields['personal_fields'] ?? []))
                    ->merge(collect($dividedFields['contact_fields'] ?? [])->except(['profile_photo_path']))
                    ->merge(collect($dividedFields['permanent_address_fields'] ?? []))
                    ->merge(collect($dividedFields['temporary_address_fields'] ?? []))
                    ->merge(collect($dividedFields['business_address_fields'] ?? []))
                    ->merge(collect($dividedFields['shipping_address_fields'] ?? []))
                    ->merge(collect($dividedFields['identity_fields'] ?? []))
                    ->merge(collect($dividedFields['representative_fields'] ?? []))
                    ->merge(collect($dividedFields['broker_info_fields'] ?? [])->only(['activity_region_ids', 'previous_sfa']))
                    ->merge(collect($dividedFields['broker_nbs_sector_fields'] ?? []))
                    ->merge(collect($dividedFields['other_fields'] ?? [])->only(['has_gold_contract']))
                ;
            }
        }
        if($current_candidate = Candidate::query()->withoutGlobalScope('notCreatedCandidate')->with('identity_files')->where('id', $filters['candidate_id'] ?? null)->first()) {
            $fields = $fields->map(function ($item, $field) use ($current_candidate) {
                if($field == 'candidate_id') {
                    $item['data']['value'] = $current_candidate->id;
                } elseif($inputValue = old($field, self::getCandidateField($current_candidate, $field))) {
                    if ($item['inputType'] == 'forms.select') {
                        $old_values = collect(old(str_replace(']', '', str_replace('[', '.', $field)), $inputValue)); // AK ma definovany key
                        $item['slot'] = preg_replace_callback(
                            "/<option value='([^']*)'(.*?)>(.*?)<\/option>/",
                            function ($matches) use ($old_values) {
                                $key = $matches[1];
                                $attributes = $matches[2];
                                $text = $matches[3];
                                $has_old = $old_values->contains($key);

                                $attributes = preg_replace("/\s*selected(\s*=\s*(['\"])?(.*?)\\2)?/", '', $attributes);

                                return "<option value='$key'$attributes" . ($has_old ? ' selected' : '') . ">$text</option>";
                            },
                            $item['slot']
                        );
                    } else {
                        $item['data']['value'] = $inputValue;
                    }
                } elseif($field == 'identity_card_file_path') {
                    $item['data']['value'] = $current_candidate->identity_files->mapWithKeys(function ($file) use ($current_candidate) {
                        return [ route('drive.file.show',  [$file->id]) => $file->name ?? null];
                    })->toArray();
                }
                return $item;
            });
        }

        //Aby spravne zoradilo fieldy vo formulari
        $fields = collect([
            'id' => null, 'division_id' => null, 'broker_type' => null,
            'career_id' => null, 'career_status' => null,
            'user_group_id' => null, 'parent_broker_id' => null,
            'is_business' => null, 'birth_id' => null, 'birth_date' => null, 'gender' => null,
            'title_before' => null, 'firstname' => null, 'lastname' => null, 'title_after' => null,
            'broker_name_other' => null, 'broker_title_before' => null, 'broker_firstname' => null, 'broker_lastname' => null, 'broker_title_after' => null,
            'business_id' => null, 'business_name' => null, 'business_tax' => null, 'business_vat' => null,
            'business_register_group' => null, 'business_register_subgroup' => null, 'business_register_id' => null,
            'email' => null, 'phone' => null, 'profile_photo_path' => null,
            'permanent_address_other' => null, 'permanent_address_street' => null, 'permanent_address_city' => null, 'permanent_address_zip' => null,
            'temporary_address_other' => null, 'temporary_address_street' => null, 'temporary_address_city' => null, 'temporary_address_zip' => null,
            'business_address_other' => null, 'business_address_street' => null, 'business_address_city' => null, 'business_address_zip' => null,
            'shipping_address_other' => null, 'shipping_address_street' => null, 'shipping_address_city' => null, 'shipping_address_zip' => null,
            'citizenship' => null, 'identity_card_type' => null, 'identity_card_id' => null, 'identity_card_until' => null, 'identity_card_file_path' => null,
            'activity_region_ids' => null, 'previous_sfa' => null,
            'career_start_at' => null, 'career_start_type' => null,
            'contract_start_at' => null, 'trust_signed_at' => null, 'criminal_listed_at' => null,
            'career_exit_at' => null, 'career_exit_type' => null, 'career_exit_note' => null,
            'contract_exit_at' => null, 'termination_at' => null,
            'educational_attainment' => null, 'iban' => null,
            'nbs_number' => null, 'nbs_start_register_at' => null, 'nbs_start_at' => null, 'nbs_exit_register_at' => null, 'nbs_exit_at' => null,
        ])->intersectByKeys($fields)->merge($fields);
        return $fields;
    }

    protected static function getFormField(string $field, array &$customFields = null, array $filters = []): array|false
    {
        $division_id = $filters['user_division_id'] ?? null;

        if($field == 'id') {
            tap('broker_type', function ($c_name) use (&$customFields, $filters, $division_id) {
                $label = __('forms.type');
                $query = Role::query()->whereNotIn('name', self::getSystemUserRolesArray());
                if($current_broker = $filters['current_broker'] ?? null) {
                    $query->whereIn('name', $current_broker->getRoleNames());
                } elseif(($filters['only_create'] ?? null) && $division_id) {
                    $query->whereNot('name', 'coworker');
                } elseif(($filters['candidate_form'] ?? null) == 'form') {
                    $query->where('name', ($candidate = Candidate::withoutGlobalScopes()->find($filters['candidate_id'] ?? null)) ? self::getCandidateField($candidate, 'broker_type') : []);
                    $type = 'hidden';
                }
                $c_options = self::translateFieldOptions($query->pluck('name', 'name'), '.broker_types');
                self::renderSelect($c_name, $c_options, $inputSelector['inputType'], $inputSelector['slot'], $inputSelector['value'], 'agent');
                $customFields[$c_name] = self::getFormattedFormField([
                    'wrapClass' => 'col-md',
                    'inputType' => $inputSelector['inputType'],
                    'slot' => $inputSelector['slot'],
                    'value' => $inputSelector['value'],
                    'label' => $label ?? null,
                    'type' => $type ?? null,
                    'selectOptions' => $c_options ?? null,
                ], $c_name);
            });
            tap('division_id', function ($c_name) use (&$customFields, $division_id) {
                $c_collection = Division::all();
                $c_options = $c_collection->pluck('name', 'id')->prepend('-', null);

                self::renderSelect($c_name, $c_options, $inputSelector['inputType'], $inputSelector['slot'], $inputSelector['value'], $division_id);

                foreach ($c_collection as $item) {
                    $needle = " value='" . $item['id'] . "'";
                    $append = "data-division-code='" . $item['code'] . "'";
                    $inputSelector['slot'] = substr_replace($inputSelector['slot'], $needle . ' ' . $append, strpos($inputSelector['slot'], $needle), strlen($needle));
                }

                $customFields[$c_name] = self::getFormattedFormField([
                    'wrapClass' => $division_id ? 'd-none' : '' . 'col-md-6',
                    'inputType' => $inputSelector['inputType'],
                    'slot' => $inputSelector['slot'],
                    'value' => $division_id ?: $inputSelector['value'],
                    'selectOptions' => $c_options ?? null,
                ], $c_name);
            });
            tap('broker_name_other', function ($c_name) use (&$customFields) {
                $customFields[$c_name] = self::getFormattedFormField([
                    'inputType' => 'forms.checkbox',
                    'inputClass' => 'h6 lh-base',
                    'label' => 'Iné meno',
                    'type' => 'switch',
                ], $c_name);
            });
            tap('activity_region_ids', function ($c_name) use (&$customFields, $filters) {
                $c_options = Region::pluck('name', 'id');
                self::renderSelect($customName = $c_name, $c_options, $inputSelector['inputType'], $inputSelector['slot'], $inputSelector['value'], []);
                $customFields[$customName] = self::getFormattedFormField([
                    'wrapClass' => 'col-md-6',
                    'inputType' => $inputSelector['inputType'],
                    'label' => __('forms.activity_region'),
                    'slot' => $inputSelector['slot'],
                    'value' => $inputSelector['value'],
                    'multiple' => 'multiple',
                    'selectOptions' => $c_options ?? null,
                    'appendBtns' => $appendBtns ?? null
                ],$customName);
            });

            foreach (Representative::getFormFields(null, $filters) as $c_name => $data) {
                $customFields["representative[_pending_][$c_name]"] = $data;
            }

            //Candidate form
            if($candidate_form = ($filters['candidate_form'] ?? null)) {
                if(in_array($candidate_form, ['form', 'show', 'create', 'edit'])) {
                    tap('identity_card_file_path', function ($c_name) use (&$customFields) {
                        $customFields[$c_name] = self::getFormattedFormField([
                            'inputType' => 'forms.dropzone',
                            'multiple' => 'multiple',
                        ], $c_name);
                    });
                    tap('nbs_sectors', function ($c_name) use (&$customFields) {
                        $c_options = Sector::pluck('name', 'id');
                        foreach ($c_options as $option => $option_value) {
                            $customFields[$option_c_name = $c_name . "[$option]"] = self::getFormattedFormField([
                                'inputType' => 'forms.checkbox',
                                'type' => 'switch',
                                'label' => $option_value
                            ], $option_c_name);
                        }
                    });

                    if ($candidate_form == 'create') {
                        $customFields[$customName = 'candidate_email'] = self::getFormattedFormField([
                            'label' => __('forms.candidate_email')
                        ], $customName);
                    }
                }
            }
        }
        if($field == 'user_group_id') {
            if (in_array(($filters['candidate_form'] ?? null), ['create', 'show'])) {
                $wrapClass = 'col-md-6';
            } else {
                $wrapClass = 'col-md-3';
            }
            $label = __('forms.user_group_id');
            $query = Group::query();
            $collection = $query->get();
            $options = $collection->pluck('name', 'id');
            $options->prepend('-', null);
            self::renderSelect($field, $options, $inputType, $slot, $value);
            foreach ($collection as $item) {
                $needle = " value='" . $item['id'] . "'";
                $append = "data-division-id='". $item['user_division_id'] ."'";
                $append2 = "data-division-code='". $item->division->code ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append . ' ' . $append2, strpos($slot, $needle), strlen($needle));
            }
        } elseif($field == 'parent_broker_id') {                                  //TODO distinct
            if (in_array(($filters['candidate_form'] ?? null), ['create', 'show'])) {
                $wrapClass = 'col-md-6';
            } else {
                $wrapClass = 'col-md-3';
            }
            $query = Broker::query()
                ->with('division')
                ->whereNotNull('user_group_id');
            if(($filters['without_filters'] ?? null)) {
                $collection = $query->get();
                $options = $collection->pluck('short_name', 'id');
            } else {
                $collection = $query->withCompleteName()->get();
                $options = $collection->pluck('complete_name', 'id');
            }
            $options = $options->prepend('-', null);
            self::renderSelect($field, $options, $inputType, $slot, $value);

            foreach ($collection as $item) {
                $needle = " value='" . $item['id'] . "'";
                $append = "data-division-id='". optional($item->division)->id ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
            }
        } elseif ($field == 'career_status') {
            $label = __('forms.status');
            $wrapClass = 'col-md-3';
            $options = self::translateFieldOptions(collect([
                'active' => 'active',
                'not_active' => 'not_active'
            ]), '.career_statuses');
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } elseif ($field == 'is_business') {
            $collection = collect([
                false => [
                    'text' => 'person',
                    'personal_cols' => true,
                    'business_cols' => false,
                ],
                'natural_person' => [
                    'text' => 'natural_person',
                    'personal_cols' => true,
                    'business_cols' => true,
                    'business_register_group_label' => __('forms.business_register_group_natural'),
                    'business_register_id_label' => __('forms.business_register_id_natural'),
                ],
                'legal_person' => [
                    'text' => 'legal_person',
                    'personal_cols' => false,
                    'business_cols' => true,
                    'business_register_group_label' => __('forms.business_register_group_legal'),
                    'business_register_subgroup_label' => __('forms.business_register_subgroup_legal'),
                    'business_register_id_label' => __('forms.business_register_id_legal'),
                ]
            ]);
            if($filters['assign_user'] ?? null) {
                $collection = $collection->except(['legal_person']);
            }
            $options = self::translateFieldOptions($collection->mapWithKeys(function ($value, $key) {return [$key => $value['text']];}), '.business_types');
            self::renderSelect($field, $options, $inputType, $slot, $value);

            foreach ($collection as $option => $option_value) {
                $needle = " value='$option'";
                $append = "data-has-personal-cols='". $option_value['personal_cols'] ."' data-has-business-cols='". $option_value['business_cols'] ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
                $append = "data-business-register-group-label='". ($option_value['business_register_group_label'] ?? '') ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
                $append = "data-business-register-subgroup-label='". ($option_value['business_register_subgroup_label'] ?? '') ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
                $append = "data-business-register-id-label='". ($option_value['business_register_id_label'] ?? '') ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
            }
        } elseif ($field == 'gender') {
            $wrapClass = 'col-md-3';
            $options = self::translateFieldOptions(collect([null => '-', 'male' => 'male', 'female' => 'female']), '.genders');
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } elseif(in_array(str_replace('_id', '', $field), ['permanent_address', 'temporary_address', 'business_address', 'shipping_address'])) {    //TODO nahradit cez traitu
            $customFields[$customName = str_replace('id', 'other', $field)] = self::getFormattedFormField([
                'inputType' => 'forms.checkbox',
                'inputClass' => 'h6 lh-base',
                'type' => 'switch',
            ], $customName);
            $customFields[$customName = str_replace('id', 'street', $field)] = self::getFormattedFormField([
                'wrapClass' => 'col-md-6',
                'label' => __('forms.street')
            ], $customName);
            $customFields[$customName = str_replace('id', 'city', $field)] = self::getFormattedFormField([
                'wrapClass' => 'col-md-4',
                'label' => __('forms.city')
            ], $customName);
            $customFields[$customName = str_replace('id', 'zip', $field)] = self::getFormattedFormField([
                'wrapClass' => 'col-md-2',
                'label' => __('forms.zip')
            ], $customName);
            return false;
        } elseif ($field == 'citizenship') {
            $wrapClass = 'col-md-2';
            $default = 'sk';
            $options = self::translateFieldOptions(collect(countries())->map(function ($item, $key) use ($default) {
                return $item['native_name'];
            }));

            self::renderSelect($field, $options, $inputType, $slot, $value, $default);

            foreach ($options as $option => $option_value) {
                $needle = " value='$option'";
                $append = "data-is-native='". ($option == $default ? true : false) ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
            }
        } elseif($field == 'identity_card_type') {
            $wrapClass = 'col-md-4';
            $collection = collect([
                null => [   //Musi byt kvoli select2 bugu ze sa potom neda kliknut na defaultny option! //TODO fixnut a vymazat tento option!
                    'text' => '-',
                    'for_native' => false,
                    'for_foreign' => false,
                ],
                'identity_card' => [
                    'text' => 'identity_card',
                    'for_native' => true,
                    'for_foreign' => false,
                ],
                'residence_permit' => [
                    'text' => 'residence_permit',
                    'for_native' => false,
                    'for_foreign' => true,
                ],
                'passport' => [
                    'text' => 'passport',
                    'for_native' => true,
                    'for_foreign' => false,
                ],
            ]);
            $options = self::translateFieldOptions($collection->mapWithKeys(function ($value, $key) {return [$key => $value['text']];}), '.identity_card_types');
            self::renderSelect($field, $options, $inputType, $slot, $value);

            foreach ($collection as $option => $option_value) {
                $needle = " value='$option'";
                $append = "data-for-native='". $option_value['for_native'] ."' data-for-foreign='". $option_value['for_foreign'] ."'";
                $slot = substr_replace($slot, $needle. ' ' . $append, strpos($slot, $needle), strlen($needle));
            }
        } elseif ($field == 'educational_attainment') {
            $options = self::translateFieldOptions(collect([
                null => '-',
//                'primary' => 'primary',
                'secondary' => 'secondary',
                'higher_1_degree' => 'higher_1_degree',
                'higher_2_degree'  => 'higher_2_degree',
                'higher_3_degree'  => 'higher_3_degree'
            ]), '.educational_attainments');
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } elseif ($field == 'career_start_type') {
            $wrapClass = 'col-md';
            $options = self::translateFieldOptions(collect([
                'new' => 'new',
                'from_market' => 'from_market'
            ]), '.career_start_types')->prepend('-', null);;
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } elseif (in_array($field , ['nbs_exit_register_at', 'nbs_exit_at']) && ($filters['only_create'] ?? null)) {
            $type = 'hidden';
        } elseif ($field == 'career_exit_type') {
            $wrapClass = 'col-md';
            $options = self::translateFieldOptions(collect([
                'business' => 'business',
                'broker' => 'broker'
            ]), '.career_exit_types')->prepend('-', null);
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } elseif (($field == 'broker_type') && ($filters['without_filters'] ?? null)) {
            $options = self::translateFieldOptions(Role::query()->whereNotIn('name', self::getSystemUserRolesArray())->pluck('name', 'name'), '.broker_types');
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } elseif ((in_array($field, ['disburse_hr', 'disburse_provision', 'has_storno', 'is_ror'])) && ($filters['without_filters'] ?? null)) {
            $options = self::translateFieldOptions(collect([false => 'no', true => 'yes']));
            self::renderSelect($field, $options, $inputType, $slot, $value);
        } else {
            return self::traitGetFormField($field, $customFields, $filters);
        }

        if ($filters['only_assign_user'] ?? null) {
            $customFields[$customName = 'assign_user_email'] = self::getFormattedFormField([
                'label' => __('forms.email'),
            ], $customName);
        }

        if ($filters['only_commission_tree'] ?? null) {
            $customFields[$customName = 'commission_tree_date'] = self::getFormattedFormField([
                'label' => __('forms.date'),
                'type' => 'date',
                'value' => date('Y-m-d'),
            ], $customName);
        }

        return [
            'inputType' => $inputType ?? null,
            'wrapClass' => $wrapClass ?? null,
            'label' => $label ?? null,
            'type'  => $type ?? null,
            'slot'  => $slot ?? null,
            'value' => $value ?? null,
            'selectOptions' => $options ?? null
        ];
    }

    public static function getFormFieldsValues(self $model, array $filters = []): array {
        $model->load(['representatives', ...$address_relations = ['permanent_address', 'temporary_address', 'business_address', 'shipping_address']]);

        $model['broker_type'] = $model->roles->first()->name ?? null;
        $model['broker_name_other'] = !!($model->is_business == 'legal_person' ? false : (!empty($broker_name = trim("{$model->broker_firstname} {$model->broker_lastname}")) ? $broker_name : false));
        foreach ($address_relations as $relation) {
            $model->{$relation . '_other'} = !!$model->$relation;
            foreach (['street', 'city', 'zip'] as $column) {
                $model->{$relation . '_' . $column} = $model->$relation->$column ?? null;
            }
        }

        $model['broker_short_name'] = $model->short_name ?? '';
        $model['broker_fullname'] = $model->fullname ?? '';
        $model['broker_representative'] = $model->representative_fullname ?? '';
        $model['broker_email'] = $model->representative_email ?? '';
        $model['broker_phone'] = $model->representative_phone ?? '';
        $model['broker_address'] = $model->address ?? '';
        $model['broker_sector_ids'] = $model->brokerSectors()->validOnDate($filters['filled_at'] ?? null)->with('sector', 'garant')->get()->mapWithKeys(function ($value) {
            return [$value->sector_id => $value->sector->name . ($value->garant ? (' - ' . $value->garant->fullname) : '')];
        })->toArray();
        $model['broker_nbs_number'] = $model->nbs_number ?? '';
        $model['broker_career_id'] = $model->career_id ?? '';

        $model->representatives = (is_array($model->representatives) ? $model->representatives()->get() : $model->representatives)->mapWithKeys(function (Representative $representative) use ($filters) {
            return [$representative->id => Representative::getFormFields($representative, $filters)->map(function ($data, $key) use ($filters) {
                return $data['data']['value'] ?? null;
            })];
        })->toArray() ?? [];

        $model['activity_region_ids'] = $model->regions->pluck('id')->toArray();
        return $model->attributesToArray();
    }


    //HasTable
    protected static function getDatatable(bool $asResponse = true, array $filters = [], array $exceptFields = null) {
        $query = self::query()->select((new self)->getTable().'.*')
            ->with(['roles', 'main_user'])
            ->addSelect(self::getFullnameRawSelect())
//            ->addSelect(self::getBusinessRegisterRawSelect())
            ->addSelect(self::getBrokerTypeRawSelect())
            ->withCompleteName()
            ->with(array_map(function () {  //TODO nahradit cez traitu
                return function(BelongsTo $q) {
                    $q->select($q->getRelated()->getTable().'.*');
                    $q->addSelect(Address::getAddressRawSelect());
                };
            }, array_flip(['permanent_address', 'temporary_address', 'business_address', 'shipping_address'])))
        ;

        if ($filters['auth_system_user_id'] ?? null) {
            $query->whereHas('users', function ($q) use ($filters) {
                $q->where((new self())->users()->getQualifiedRelatedPivotKeyName(), $filters['auth_system_user_id']);
            })->activeCareer();
        } else {
            $query->brokerAccess(null, true);
        }

        if($filters['user_division_id'] ?? null) {
            $query->whereHas('division', function(Builder $q) use ($filters) {
                $q->where((new Division())->getQualifiedKeyName(), $filters['user_division_id']);
            });
        }

        self::getDatatableDefaultQueryFilter($query);
        $dtQuery = DataTables::of($query);

        tap('fullname', function ($column) use ($dtQuery) {
            $dtQuery->filterColumn($column, function ($q, $keyword) {
                $q->where(function ($q) use ($keyword) {
                    $q->where(self::getFullnameRawSelect(true, false), 'LIKE', "%{$keyword}%");
                });
            })->orderColumn($column, function ($query, $order) use ($column) {
                $query->orderBy(self::getFullnameRawSelect(false, false), $order);
            });
        });
        tap('email', function ($column) use ($dtQuery) {
            $relation = 'main_user';
            $dtQuery->editColumn($column, function ($item) use ($relation, $column) {
                return optional($item->$relation)->$column;
            })->filterColumn($column, function ($q, $keyword) use ($relation, $column) {
                $q->whereHas($relation, function ($q) use ($keyword, $column) {
                    $q->where($column, 'LIKE', "%{$keyword}%");
                });
            })->orderColumn($column, function ($query, $order) use ($relation, $column) {
                self::orderByHasOneThrough($query, $column, $order, $relation);
//                $query->orderBy(User::select($column)
//                    ->join($model_relation->getParent()->getTable(), $model_relation->getQualifiedParentKeyName(), '=', $model_relation->getQualifiedForeignKeyName())
//                    ->whereColumn($model_relation->getQualifiedFirstKeyName(), $model_relation->getQualifiedLocalKeyName())
//                    ->where($model_relation->getParent()->getTable() . '.is_main', true)
//                    ->limit(1), $order);
            });
        });
        tap('phone', function ($column) use ($dtQuery) {
            $relation = 'main_user';
            $dtQuery->editColumn($column, function ($item) use ($relation, $column) {
                return optional($item->$relation)->$column;
            })->filterColumn($column, function ($q, $keyword) use ($relation, $column) {
                $q->whereHas($relation, function ($q) use ($keyword, $column) {
                    $q->where($column, 'LIKE', "%{$keyword}%");
                });
            })->orderColumn($column, function ($query, $order) use ($relation, $column) {
                self::orderByHasOneThrough($query, $column, $order, $relation);
            });
        });

//        $dtQuery
//            ->filterColumn('business_register_id', function ($q, $keyword) {
//                $q->where(self::getBusinessRegisterRawSelect(false), 'LIKE', "%{$keyword}%");
//            })
//            ->orderColumn('business_register_id', function ($query, $order) {
//                $query->orderBy(self::getBusinessRegisterRawSelect(false), $order);
//            })
//        ;
        //TODO nahradit cez traitu
        foreach (['permanent_address', 'temporary_address', 'business_address', 'shipping_address'] as $relation) {
            self::applyBelongsToRelation($dtQuery, $relation, $relation . '_id', 'address', Address::getAddressRawSelect(false));
        }
        $dtQuery = self::getDatatableDefaultFilter($query, $dtQuery);
        tap('broker_type', function ($column) use ($dtQuery) {
            $dtQuery->filterColumn($column, function ($q, $keyword) use ($column) {
                if(($keyword = substr($keyword, 10, strlen($keyword) - 10)) == '') {
                    $keyword_options = [];
                } else {
                    $keyword_options = explode(',', $keyword);
                }
                $q->whereHas('roles', function($q) use ($keyword_options) {
                    $q->whereIn('name', array_map(fn($key) => str_replace('__key__', '', $key), $keyword_options));
                });
            });
        });

        if($asResponse) {
            if($options = self::getFormFieldOptions($column = 'career_status', array_merge($filters, ['without_filters' => true]))->toArray()[$column] ?? []) {
                $dtQuery->editColumn($column, function ($item) use ($column, $options) {
                    return collect([
                        'active' => '<span class="badge text-bg-primary w-100">' . ($options[$item->$column] ?? $item->$column) . '</span>',
                        'not_active' => '<span class="badge text-bg-danger w-100">' . ($options[$item->$column] ?? $item->$column) . '</span>',
                    ])[$item->$column] ?? $item->$column;
                })->escapeColumns($column);
            }
        }

        return ($asResponse ? $dtQuery->make(true) : $dtQuery);
    }

    protected static function getTableMergeColumns() {
        return [
            'broker_type' => ['orderable' => false, 'type' => 'select', 'label' => __('forms.type')], 'fullname' => ['label' => __('Name')], 'career_id' => null,
            'career_status' => ['label' => __('forms.status'), 'type' => 'select'], 'user_group_id' => null, 'parent_broker_id' => null,
            'email' => null, 'phone' => null,
            'birth_id' => null, 'birth_date' => null, 'gender' => null, 'citizenship' => null,
            'business_id' => null, 'business_tax' => null, 'business_vat' => null,// 'business_register_id' => null,
//            'activity_region' => null, 'nbs_number' => null,
        ];
    }

    protected static function getTableField(string $column): array
    {
        $settings = self::traitGetTableField($column);
        if ($column == 'is_ror') {
            return [...$settings,
                'label' => __('forms.ror_short'),
            ];
        } elseif ($column == 'nbs_number') {
            return [...$settings,
                'label' => __('forms.nbs_number_short'),
            ];
        } else {
            return $settings;
        }
    }


    //HasDriveFile
    public static function getDriveFileModelName(self $model, array $filters = []): string|null
    {
        return $model->short_name;
    }

    public function scopeDriveFileSubjectAccess(Builder $query/*TODO, User $user*/): void
    {
        $query->BrokerAccess(null, true);
    }

//    public static function getDriveFileOwner(array $filters = []): Model|null
//    {
//        return null;
////        return optional(auth()->user())->hasAnyRole(['master', 'admin']) ? User::find(auth()->id()) : null;
////        return auth()->user()->activeBroker ?? null;
//    }


    //HasSelfChildren
    public static function getSelfParentKey(): string|null {
        return 'parent_broker_id';
    }

    public function getAllSelfChildrenIdsAttribute($value = null, $with_inactive_career = false): array
    {
        return array_merge(
            $with_inactive_career? self::traitGetAllSelfChildrenIdsAttribute() : self::getAllSelfChildrenIdsWithActiveCareerAttributeRecursive(),
            //NOTE: Ked je broker divizia vrat vsetkych maklerov v divizii ako childrenIds
            optional(optional(auth()->user())->activeBroker)->hasAnyRole(['division']) ? $this->newQuery()->whereHas('division', function (Builder $query) {
                $query->whereKey($this->division->getKey());
            })->whereKeyNot($this->getKey())->when(!$with_inactive_career, fn($q) => $q->activeCareer())->pluck('id')->toArray() : []
        );
    }
    private function getAllSelfChildrenIdsWithActiveCareerAttributeRecursive(): array
    {
        $all_ids[] = $this->id;
        foreach ($this->self_children()->activeCareer()->get() as $child) {
            $all_ids = array_merge($all_ids, $child->getAllSelfChildrenIdsWithActiveCareerAttributeRecursive());
        }
        return $all_ids;
    }


    //HasNotes
    public static function getNoteAuthorNameField(): string
    {
        return 'name';
    }

    public function getDriveConfig(): array
    {
        return [
            'prefix' => '_system/',
            'uploader_id' => optional(auth()->user()->activeBroker)->id,
            'division_id' => optional(optional(auth()->user()->activeBroker)->division)->id,
            'name'        => $this->fullname,
            'parent'      => null
        ];
    }
}
