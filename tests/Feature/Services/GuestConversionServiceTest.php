<?php

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Services\GuestConversionService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Ensure roles exist
    foreach (UserRole::cases() as $role) {
        if (! Role::where('name', $role->value)->exists()) {
            Role::create(['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    $this->service = app(GuestConversionService::class);
});

describe('Guest Conversion Service', function () {
    it('skips conversion for orders that already have a user', function () {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $result = $this->service->convertGuestToUser($order);

        expect($result->id)->toBe($user->id);
    });

    it('skips conversion for orders without customer email', function () {
        // Create order and then manually clear the email
        $order = Order::factory()->guest()->create();
        $order->update(['customer_email' => '']);

        $result = $this->service->convertGuestToUser($order);

        expect($result)->toBeNull();
    });

    it('creates a new user for guest orders', function () {
        Notification::fake();

        $order = Order::factory()->guest()->create([
            'customer_email' => 'newguest@example.com',
            'customer_name' => 'New Guest Customer',
        ]);

        $result = $this->service->convertGuestToUser($order);

        expect($result)->toBeInstanceOf(User::class)
            ->and($result->email)->toBe('newguest@example.com')
            ->and($result->name)->toBe('New Guest Customer')
            ->and($result->hasRole(UserRole::Customer->value))->toBeTrue();

        // Order should now have user_id
        $order->refresh();
        expect($order->user_id)->toBe($result->id);

        // Password reset notification should be sent
        Notification::assertSentTo($result, ResetPassword::class);
    });

    it('associates order with existing user if email matches', function () {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);
        $existingUser->assignRole(UserRole::Customer->value);

        $order = Order::factory()->guest()->create([
            'customer_email' => 'existing@example.com',
            'customer_name' => 'Different Name',
        ]);

        $result = $this->service->convertGuestToUser($order);

        expect($result->id)->toBe($existingUser->id);

        // Order should now have user_id of existing user
        $order->refresh();
        expect($order->user_id)->toBe($existingUser->id);
    });

    it('marks new users email as verified', function () {
        Notification::fake();

        $order = Order::factory()->guest()->create([
            'customer_email' => 'verified@example.com',
        ]);

        $result = $this->service->convertGuestToUser($order);

        expect($result->email_verified_at)->not->toBeNull();
    });

    it('logs order event when guest is converted', function () {
        Notification::fake();

        $order = Order::factory()->guest()->create([
            'customer_email' => 'eventtest@example.com',
        ]);

        $this->service->convertGuestToUser($order);

        $order->refresh();
        $events = $order->events()->get();

        expect($events)->not->toBeEmpty();

        $conversionEvent = $events->first(function ($event) {
            return str_contains($event->description, 'usuario');
        });

        expect($conversionEvent)->not->toBeNull();
    });

    it('handles multiple guest orders with same email', function () {
        Notification::fake();

        $order1 = Order::factory()->guest()->create([
            'customer_email' => 'multi@example.com',
            'customer_name' => 'Multi Order Customer',
        ]);

        $order2 = Order::factory()->guest()->create([
            'customer_email' => 'multi@example.com',
            'customer_name' => 'Multi Order Customer',
        ]);

        // First conversion creates user
        $user1 = $this->service->convertGuestToUser($order1);
        expect($user1)->toBeInstanceOf(User::class);

        // Second conversion should find existing user
        $user2 = $this->service->convertGuestToUser($order2);
        expect($user2->id)->toBe($user1->id);

        // Both orders should have the same user_id
        $order1->refresh();
        $order2->refresh();

        expect($order1->user_id)->toBe($user1->id)
            ->and($order2->user_id)->toBe($user1->id);
    });
});
