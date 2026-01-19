<?php

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    // Ensure all roles exist
    foreach (UserRole::cases() as $role) {
        if (! Role::where('name', $role->value)->exists()) {
            Role::create(['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(UserRole::Admin->value);
});

describe('User Resource Access', function () {
    test('super admin can access user management', function () {
        actingAs($this->superAdmin);

        Livewire::test(ListUsers::class)
            ->assertOk();
    });

    test('admin can access user management', function () {
        actingAs($this->admin);

        Livewire::test(ListUsers::class)
            ->assertOk();
    });

    test('customer cannot access user management', function () {
        $customer = User::factory()->create();
        $customer->assignRole(UserRole::Customer->value);

        actingAs($customer);

        $this->get('/admin/users')
            ->assertForbidden();
    });

    test('support cannot access user management', function () {
        $support = User::factory()->create();
        $support->assignRole(UserRole::Support->value);

        actingAs($support);

        $this->get('/admin/users')
            ->assertForbidden();
    });
});

describe('User CRUD Operations', function () {
    test('can list users', function () {
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            $user->assignRole(UserRole::Customer->value);
        }

        actingAs($this->superAdmin);

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($users);
    });

    test('can create a new user', function () {
        actingAs($this->superAdmin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'testuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => UserRole::Customer->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas(User::class, [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);

        $newUser = User::where('email', 'testuser@example.com')->first();
        expect($newUser->hasRole(UserRole::Customer->value))->toBeTrue();
    });

    test('can edit a user', function () {
        $userToEdit = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);
        $userToEdit->assignRole(UserRole::Customer->value);

        actingAs($this->superAdmin);

        Livewire::test(EditUser::class, ['record' => $userToEdit->id])
            ->assertSchemaStateSet([
                'name' => 'Original Name',
                'email' => 'original@example.com',
            ])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        assertDatabaseHas(User::class, [
            'id' => $userToEdit->id,
            'name' => 'Updated Name',
        ]);
    });

    test('validates required fields on create', function () {
        actingAs($this->superAdmin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => '',
                'email' => '',
                'password' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'email', 'password']);
    });
});

describe('Role Restrictions', function () {
    test('super admin can assign admin role', function () {
        actingAs($this->superAdmin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Admin',
                'email' => 'newadmin@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => UserRole::Admin->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $newAdmin = User::where('email', 'newadmin@example.com')->first();
        expect($newAdmin->hasRole(UserRole::Admin->value))->toBeTrue();
    });

    test('super admin can change user role', function () {
        $customer = User::factory()->create();
        $customer->assignRole(UserRole::Customer->value);

        actingAs($this->superAdmin);

        Livewire::test(EditUser::class, ['record' => $customer->id])
            ->fillForm([
                'role' => UserRole::Support->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $customer->refresh();
        expect($customer->hasRole(UserRole::Support->value))->toBeTrue();
    });
});
