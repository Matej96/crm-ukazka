<?php

namespace Database\Seeders;

use App\Plugins\System\app\Models\Client;
use App\Plugins\System\app\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $master_permissions = collect();
        $admin_permissions = collect();

        //GENERAL
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'system.system_setting.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'system.client.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'example.example.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'blank_plugin.blank_model.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'system.client.%')->get());

        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'system.user.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'blog.category.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'blog.article.%')->get());

//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.course_category.%')->get());
//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.course.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.course_access.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.chapter.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.slide.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.test.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.certificate.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.review.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'elearning.course_file.%')->get());

        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'newsletter.contact.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'contact_form.message.%')->get());
//TODO        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'contact_form.idea.%')->get());


        //FINLIA_GENERAL
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'product.partner_category.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'product.sector.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'product.product_category.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'product.product_type.%')->get());
        $master_permissions = $master_permissions->merge(Permission::where('name', 'LIKE', 'commission.sfa_commission.%')->get());

//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.division.%')->get());
//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.group.%')->get());
//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.broker.%')->get());
//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.candidate.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'product.partner.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'product.product.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'product.product_version.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.division_product.%')->get());
        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.group_product.%')->get());
//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.broker_sector.%')->get());
//        $admin_permissions = $admin_permissions->merge(Permission::where('name', 'LIKE', 'user.broker_relation.%')->get());

        $permissions = (Permission::all())->whereNotIn('name', collect([
            ...$master_permissions->toArray(),
            ...$admin_permissions->toArray(),
        ])->pluck('name'));

        $permissions = $permissions->filter(fn ($permission) =>
            !str_starts_with($permission->name, 'elearning.course_category.')  &&
            !str_starts_with($permission->name, 'elearning.course.')  &&
            //TODO elearning.review., elearning.course_file.,
            !str_starts_with($permission->name, 'contact_form.idea.') &&
            //TODO drive.file.* -> divizia nech nevie nahravat ani editovat
            //TODO je to treba niekomu priradovat?? codebook.*, confirmation.confirmation, note.note,

            !str_starts_with($permission->name, 'user.division.') &&
            !str_starts_with($permission->name, 'user.group.') &&
            !str_starts_with($permission->name, 'user.broker.') &&
            !str_starts_with($permission->name, 'user.candidate.') &&
            !str_starts_with($permission->name, 'user.broker_sector.') &&
            !str_starts_with($permission->name, 'user.broker_relation.') &&
            !str_starts_with($permission->name, 'client.client.') &&
            !str_starts_with($permission->name, 'client.record.') &&
            !str_starts_with($permission->name, 'contract.contract.') &&
            !str_starts_with($permission->name, 'intervention.intervention.') &&
            !str_starts_with($permission->name, 'complaint.complaint.') &&
            !str_starts_with($permission->name, 'commission.broker_commission.') &&
            !str_starts_with($permission->name, 'commission.storno_commission.')  &&
            !str_starts_with($permission->name, 'closure.closure.')  &&
            !str_starts_with($permission->name, 'closure.partner_import.')//TODO && dalsie
            //TODO aby moze vidiet aj niekto iny ako admin - user.division_product.*, user.group_product.*
        );

//User roles
        // create roles and assign created permissions
        $role = Role::findOrCreate('master');
        $role->syncPermissions([]/*Permission::all()*/);

        $role = Role::findOrCreate('admin');
        $role->syncPermissions([...$permissions, ...$admin_permissions,
            'elearning.course_category.create',
            'elearning.course_category.read',
            'elearning.course_category.update',
            'elearning.course.create',
            'elearning.course.study',
            'elearning.course.read',
            'elearning.course.update',
            'contact_form.idea.create',
            'contact_form.idea.read',
            'contact_form.idea.update',
            'contact_form.idea.delete',

            'user.division.create',
            'user.division.read',
            'user.division.update',
            'user.group.create',
            'user.group.read',
            'user.group.update',
            'user.broker.create',
            'user.broker.read',
            'user.broker.update',
            'user.candidate.create',
            'user.candidate.read',
            'user.candidate.update',
            'user.broker_sector.create',
            'user.broker_sector.read',
            'user.broker_sector.update',
            'user.broker_relation.create',
            'user.broker_relation.read',
            'user.broker_relation.update',
            'client.client.read',
            'client.client.update',
            'client.record.sign',
            'contract.contract.read',
            'contract.contract.update',
            'intervention.intervention.create',
            'intervention.intervention.read',
            'intervention.intervention.update',
            'complaint.complaint.create',
            'complaint.complaint.read',
            'complaint.complaint.update',
            'commission.broker_commission.create',
            'commission.broker_commission.read',
            'commission.broker_commission.update',
            'commission.storno_commission.create',
            'commission.storno_commission.read',
            'commission.storno_commission.update',
            'closure.closure.create',
            'closure.closure.read',
            'closure.closure.update',
            'closure.partner_import.create',
            'closure.partner_import.read',
            'closure.partner_import.update',
        ]);

        $role = Role::findOrCreate('user');
        $role->syncPermissions([...$permissions,
            'elearning.course.study',
            'contact_form.idea.create',
        ]);
//User roles

//Broker roles
        $role = Role::findOrCreate('agent');
        $role->syncPermissions([
            'user.division.read',
            'user.broker.read',
            'user.broker_sector.read',
            'user.broker_relation.read',
            'client.client.create',
            'client.client.read',
            'client.record.create',
            'client.record.sign',
            'contract.contract.create',
            'contract.contract.read',
            'intervention.intervention.read',
            'intervention.intervention.update',
            'complaint.complaint.create',
            'complaint.complaint.read',
            'commission.broker_commission.read',
            'commission.storno_commission.read',
            'closure.closure.read',
        ]);

        $role = Role::findOrCreate('tipster_zos');
        $role->syncPermissions([
            'user.division.read',
            'user.broker.read',
            'user.broker_sector.read',
            'user.broker_relation.read',
            'client.client.create',
            'client.client.read',
            'client.record.create',
            'client.record.sign',
            'contract.contract.create',
            'contract.contract.read',
            'intervention.intervention.read',
            'intervention.intervention.update',
            'complaint.complaint.create',
            'complaint.complaint.read',
            'commission.broker_commission.read',
            'commission.storno_commission.read',
            'closure.closure.read',
        ]);

        $role = Role::findOrCreate('tipster_external');
        $role->syncPermissions([
            'user.division.read',
            'user.broker.read',
            'user.broker_sector.read',
            'user.broker_relation.read',
            'client.client.read',
            'contract.contract.read',
            'commission.broker_commission.read',    //TODO len rozpad - bez storno, provizneho vypisu a provizneho sadzobniku
            'commission.storno_commission.read',    //TODO len rozpad - bez storno, provizneho vypisu a provizneho sadzobniku
            'closure.closure.read',
        ]);

        $role = Role::findOrCreate('employee_sfa');
        $role->syncPermissions([
            'user.division.read',
            'user.broker.read',
            'user.broker_sector.read',
            'user.broker_relation.read',
            'client.client.create',
            'client.client.read',
            'client.record.create',
            'client.record.sign',
            'contract.contract.create',
            'contract.contract.read',
            'intervention.intervention.read',
            'intervention.intervention.update',
            'complaint.complaint.create',
            'complaint.complaint.read',
        ]);

        $role = Role::findOrCreate('employee_pfa');
        $role->syncPermissions([
            'user.division.read',
            'user.broker.read',
            'user.broker_sector.read',
            'user.broker_relation.read',
            'client.client.create',
            'client.client.read',
            'client.record.create',
            'client.record.sign',
            'contract.contract.create',
            'contract.contract.read',
            'intervention.intervention.read',
            'intervention.intervention.update',
            'complaint.complaint.create',
            'complaint.complaint.read',
        ]);

        $role = Role::findOrCreate('coworker');
        $role->syncPermissions([

        ]);

        $role = Role::findOrCreate('division');
        $role->syncPermissions([
            'user.division.read',
            'user.group.create',
            'user.group.read',
            'user.group.update',
            'user.broker.read',
            'user.broker_sector.read',
            'user.broker_relation.read',
            'user.candidate.create',
            'user.candidate.read',
            'client.client.read',
            'contract.contract.read',
            'intervention.intervention.read',
            'complaint.complaint.read',
            'commission.broker_commission.read',
            'commission.storno_commission.read',
            'closure.closure.read',
        ]);
//Broker roles

        $team_id = getPermissionsTeamId();
        $user = User::where('email', config('app.first_admin_email'))->first();
        $client = Client::where('name', config('app.first_client_name'))->first();
        setPermissionsTeamId($client->id);
        $user->assignRole('master');

        setPermissionsTeamId($team_id);

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
