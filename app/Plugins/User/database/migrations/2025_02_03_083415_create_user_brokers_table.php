<?php

use App\Plugins\Codebook\app\Models\Address;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_brokers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_id')->nullable();//cez alter neskor ->constrained((new \App\Plugins\User\app\Models\Candidate())->getTable());
            $table->foreignId('user_group_id')->nullable()->constrained();
            $table->foreignId('parent_broker_id')->nullable()->constrained((new \App\Plugins\User\app\Models\Broker())->getTable());
            $table->string('is_business')->default(false);

            $table->string('career_id')->nullable();
            $table->string('career_status')->nullable();

            $table->string('birth_id')->nullable();
            $table->string('gender')->nullable();
            $table->dateTime('birth_date')->nullable();
            $table->string('broker_firstname')->nullable();
            $table->string('broker_lastname')->nullable();
            $table->string('broker_title_before')->nullable();
            $table->string('broker_title_after')->nullable();
            $table->foreignIdFor(Address::class, 'permanent_address_id')->nullable()->constrained((new Address())->getTable());
            $table->foreignIdFor(Address::class, 'temporary_address_id')->nullable()->constrained((new Address())->getTable());

            $table->string('business_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_tax')->nullable();
            $table->string('business_vat')->nullable();
            $table->foreignIdFor(Address::class, 'business_address_id')->nullable()->constrained((new Address())->getTable());
            $table->string('business_register_group')->nullable();
            $table->string('business_register_subgroup')->nullable();
            $table->string('business_register_id')->nullable();

            $table->foreignIdFor(Address::class, 'shipping_address_id')->nullable()->constrained((new Address())->getTable());

            $table->string('citizenship')->nullable();
            $table->string('identity_card_type')->nullable();
            $table->string('identity_card_id')->nullable();
            $table->datetime('identity_card_until')->nullable();

            $table->datetime('career_start_at')->nullable();
            $table->string('career_start_type')->nullable();
            $table->datetime('career_exit_at')->nullable();
            $table->string('career_exit_type')->nullable();
            $table->string('career_exit_note')->nullable();
            $table->datetime('contract_start_at')->nullable();
            $table->datetime('trust_signed_at')->nullable();
            $table->datetime('criminal_listed_at')->nullable();
            $table->datetime('contract_exit_at')->nullable();
            $table->datetime('termination_at')->nullable();

            $table->string('previous_sfa')->nullable();

            $table->string('educational_attainment')->nullable();
            $table->string('iban')->nullable();

            $table->string('nbs_number')->nullable();
            $table->datetime('nbs_start_register_at')->nullable();
            $table->datetime('nbs_start_at')->nullable();
            $table->datetime('nbs_exit_register_at')->nullable();
            $table->datetime('nbs_exit_at')->nullable();

            $table->boolean('disburse_provision')->nullable();
            $table->string('disburse_provision_note')->nullable();

            $table->boolean('has_storno')->default(0);
            $table->double('storno_value')->nullable();

            $table->boolean('is_ror')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Permission::findOrCreate('user.broker.*');
        Permission::findOrCreate('user.broker.create');
        Permission::findOrCreate('user.broker.read');
        Permission::findOrCreate('user.broker.update');
        Permission::findOrCreate('user.broker.delete');

        if(!DB::table('system_system_settings')
            ->where('plugin', 'User')
            ->where('subject_type', 'App\\Plugins\\User\\app\\Models\\Broker')
            ->exists()) {
            $sql = "INSERT INTO `system_system_settings` (`plugin`, `subject_type`, `form_except`, `table_except`, `column_types`, `created_at`, `updated_at`,  `is_visible`) VALUES
                ('User', 'App\\\Plugins\\\User\\\app\\\Models\\\Broker',	'[\"created_at\",\"updated_at\",\"deleted_at\"]',	'[\"id\",\"candidate_id\",\"is_business\",\"broker_firstname\",\"broker_lastname\",\"broker_title_before\",\"broker_title_after\",\"business_name\",\"business_register_group\",\"business_register_subgroup\",\"business_register_id\",\"citizenship\",\"identity_card_type\",\"identity_card_id\",\"identity_card_until\",\"career_start_at\",\"career_start_type\",\"career_exit_at\",\"career_exit_type\",\"career_exit_note\",\"contract_start_at\",\"trust_signed_at\",\"criminal_listed_at\",\"contract_exit_at\",\"termination_at\",\"previous_sfa\",\"educational_attainment\",\"iban\",\"nbs_start_register_at\",\"nbs_start_at\",\"nbs_exit_register_at\",\"nbs_exit_at\",\"disburse_provision_note\",\"storno_value\",\"created_at\",\"updated_at\",\"deleted_at\"]',	'{\"id\": {\"type\": \"hidden\"}, \"gender\": {\"tableType\": \"select\"}, \"is_ror\": {\"type\": \"switch\", \"label\": \"forms.ror\", \"inputType\": \"forms.checkbox\", \"tableType\": \"select\"}, \"birth_id\": {\"wrapClass\": \"col-md-6\"}, \"career_id\": {\"wrapClass\": \"col-md\"}, \"birth_date\": {\"type\": \"date\", \"tableType\": \"date\", \"wrapClass\": \"col-md-3\"}, \"has_storno\": {\"type\": \"switch\", \"inputType\": \"forms.checkbox\", \"tableType\": \"select\"}, \"business_id\": {\"wrapClass\": \"col-md-3\"}, \"citizenship\": {\"tableType\": \"select\"}, \"nbs_exit_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"business_tax\": {\"wrapClass\": \"col-md-6 col-xl-3\"}, \"business_vat\": {\"wrapClass\": \"col-md-6 col-xl-3\"}, \"candidate_id\": {\"type\": \"hidden\"}, \"nbs_start_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"previous_sfa\": {\"wrapClass\": \"col-md\"}, \"storno_value\": {\"max\": 100, \"min\": 0, \"type\": \"number\"}, \"business_name\": {\"wrapClass\": \"col-md-9\"}, \"user_group_id\": {\"tableType\": \"select\"}, \"career_exit_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"termination_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"activity_region\": {\"wrapClass\": \"col-md\"}, \"broker_lastname\": {\"label\": \"forms.lastname\", \"wrapClass\": \"col-md-4\"}, \"career_start_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"trust_signed_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"broker_firstname\": {\"label\": \"forms.firstname\", \"wrapClass\": \"col-md-4\"}, \"career_exit_note\": {\"wrapClass\": \"col-md\"}, \"contract_exit_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"identity_card_id\": {\"wrapClass\": \"col-md-4\"}, \"parent_broker_id\": {\"tableType\": \"select\"}, \"contract_start_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"broker_title_after\": {\"label\": \"forms.title_after\", \"wrapClass\": \"col-md-2\"}, \"criminal_listed_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"disburse_provision\": {\"type\": \"switch\", \"inputType\": \"forms.checkbox\", \"tableType\": \"select\"}, \"broker_title_before\": {\"label\": \"forms.title_before\", \"wrapClass\": \"col-md-2\"}, \"identity_card_until\": {\"type\": \"date\", \"tableType\": \"date\", \"wrapClass\": \"col-md-2\"}, \"business_register_id\": {\"wrapClass\": \"col-md\"}, \"nbs_exit_register_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"nbs_start_register_at\": {\"type\": \"date\", \"wrapClass\": \"col-md\"}, \"business_register_group\": {\"wrapClass\": \"col-md\"}, \"business_register_subgroup\": {\"wrapClass\": \"col-md-auto g-md-0\"}}', now(),	now(), 1);";

            DB::insert($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_brokers');
    }
};
