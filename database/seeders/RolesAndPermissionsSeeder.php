<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder for roles and permissions.
 *
 * @see ALCANCE.md §2 - Actores del sistema
 * @see ARQUITECTURA.md §6 - Roles/Policies Filament
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions by module
        $this->createRafflePermissions();
        $this->createOrderPermissions();
        $this->createTicketPermissions();
        $this->createPaymentPermissions();
        $this->createCmsPermissions();
        $this->createUserPermissions();
        $this->createResultPermissions();

        // Create roles and assign permissions
        $this->createRoles();
    }

    private function createRafflePermissions(): void
    {
        $permissions = [
            'view_raffles',
            'view_any_raffle',
            'create_raffles',
            'update_raffles',
            'delete_raffles',
            'publish_raffles',
            'close_raffles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createOrderPermissions(): void
    {
        $permissions = [
            'view_orders',
            'view_any_order',
            'view_order_timeline',
            'view_order_transactions',
            'resend_order_email',
            'add_order_note',
            'export_orders',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createTicketPermissions(): void
    {
        $permissions = [
            'view_tickets',
            'view_any_ticket',
            'search_tickets',
            'export_tickets',
            'reassign_tickets', // For edge cases
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createPaymentPermissions(): void
    {
        $permissions = [
            'view_payments',
            'view_any_payment',
            'view_payment_details',
            'requery_payment', // Re-check with provider
            'initiate_refund',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createCmsPermissions(): void
    {
        $permissions = [
            'view_cms_pages',
            'update_cms_pages',
            'publish_cms_pages',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createUserPermissions(): void
    {
        $permissions = [
            'view_users',
            'view_any_user',
            'create_users',
            'update_users',
            'delete_users',
            'assign_roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createResultPermissions(): void
    {
        $permissions = [
            'view_results',
            'register_results',
            'confirm_results',
            'publish_results',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createRoles(): void
    {
        // Customer role - basic role for registered users
        // They don't need admin permissions, just the role for identification
        Role::firstOrCreate(['name' => UserRole::Customer->value]);

        // Support role - read-only access + limited actions
        // @see ALCANCE.md §2 - consulta ordenes/tickets/timeline, acciones limitadas
        $supportRole = Role::firstOrCreate(['name' => UserRole::Support->value]);
        $supportRole->syncPermissions([
            // View only permissions
            'view_orders',
            'view_any_order',
            'view_order_timeline',
            'view_order_transactions',
            'view_tickets',
            'view_any_ticket',
            'search_tickets',
            'view_payments',
            'view_any_payment',
            'view_payment_details',
            'view_raffles',
            'view_any_raffle',
            // Limited actions
            'add_order_note',
            'resend_order_email',
        ]);

        // Admin role - full operational access
        // @see ALCANCE.md §2 - gestiona sorteos/paquetes/stock, ordenes/pagos, tickets, CMS, resultados
        $adminRole = Role::firstOrCreate(['name' => UserRole::Admin->value]);
        $adminRole->syncPermissions([
            // Raffles - full access
            'view_raffles',
            'view_any_raffle',
            'create_raffles',
            'update_raffles',
            'delete_raffles',
            'publish_raffles',
            'close_raffles',
            // Orders - full access
            'view_orders',
            'view_any_order',
            'view_order_timeline',
            'view_order_transactions',
            'resend_order_email',
            'add_order_note',
            'export_orders',
            // Tickets - full access
            'view_tickets',
            'view_any_ticket',
            'search_tickets',
            'export_tickets',
            'reassign_tickets',
            // Payments - full access
            'view_payments',
            'view_any_payment',
            'view_payment_details',
            'requery_payment',
            'initiate_refund',
            // CMS - full access
            'view_cms_pages',
            'update_cms_pages',
            'publish_cms_pages',
            // Results - full access
            'view_results',
            'register_results',
            'confirm_results',
            'publish_results',
            // Users - limited (no role assignment)
            'view_users',
            'view_any_user',
        ]);

        // Super Admin role - all permissions including user/role management
        $superAdminRole = Role::firstOrCreate(['name' => UserRole::SuperAdmin->value]);
        $superAdminRole->syncPermissions(Permission::all());
    }
}
