<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions with correct guard_name
        Permission::create(['name' => 'apply loan', 'guard_name' => 'web']);
        Permission::create(['name' => 'view loan', 'guard_name' => 'web']);
        Permission::create(['name' => 'access loan officer component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access cash flow component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access collateral component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access loan analysis component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access manager to BCC component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access BCC decision component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access HQ decision component', 'guard_name' => 'web']);
        Permission::create(['name' => 'access BoDCC component', 'guard_name' => 'web']);

        // Create roles and assign created permissions

        // Member Role
        $memberRole = Role::create(['name' => 'Member', 'guard_name' => 'web']);
        $memberRole->givePermissionTo(['apply loan', 'view loan']);  // Ensure guard matches

        // Loan Officer Role
        $loanOfficerRole = Role::create(['name' => 'LoanOfficer', 'guard_name' => 'web']);
        $loanOfficerRole->givePermissionTo(['apply loan', 'view loan', 'access loan officer component']);  // Ensure guard matches

        // Manager Role
        $managerRole = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->givePermissionTo([
            'access cash flow component',
            'access collateral component',
            'access loan analysis component',
            'access manager to BCC component'
        ]);

        // Branch Credit Committee Role
        $branchCreditCommitteeRole = Role::create(['name' => 'BranchCreditCommittee', 'guard_name' => 'web']);
        $branchCreditCommitteeRole->givePermissionTo(['access BCC decision component']);

        // Head Office Credit Committee Role
        $headOfficeCreditCommitteeRole = Role::create(['name' => 'HeadOfficeCREDITCOMMITTEE', 'guard_name' => 'web']);
        $headOfficeCreditCommitteeRole->givePermissionTo(['access HQ decision component']);

        // Board of Directors Credit Committee Role
        $boardOfDirectorsCreditCommitteeRole = Role::create(['name' => 'THEBOARDOFDIRECTORSCREDITCOMMITTEE', 'guard_name' => 'web']);
        $boardOfDirectorsCreditCommitteeRole->givePermissionTo(['access BoDCC component']);

        // Super Admin Role (Web guard)
        $superAdminRole = Role::create(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::where('guard_name', 'web')->get());  // Assign all web guard permissions
    }
}
