<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $superAdmin = Role::create(['name' => 'super_admin']);
        $admin = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Create basic permissions (example for future)
        Permission::create(['name' => 'manage tournaments']);
        Permission::create(['name' => 'approve tournaments']);
        Permission::create(['name' => 'manage sports']);

        // Assign all permissions to super_admin
        $superAdmin->givePermissionTo(Permission::all());

        // Assign specific permissions to regular admin
        $admin->givePermissionTo(['approve tournaments', 'manage sports']);

        // Assign roles to specific accounts
        $adminUser = User::where('email', 'admin@juara-league.id')->first();
        if ($adminUser) {
            $adminUser->assignRole('super_admin');
        }

        $organizerUser = User::where('email', 'organizer@juara-league.id')->first();
        if ($organizerUser) {
            $organizerUser->assignRole('user');
        }

        $participantUser = User::where('email', 'participant@juara-league.id')->first();
        if ($participantUser) {
            $participantUser->assignRole('user');
        }
    }
}
