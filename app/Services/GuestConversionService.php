<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Service for converting guest customers to registered users.
 *
 * When a guest makes a purchase, we create a user account for them
 * automatically so they can access their orders and tickets.
 */
class GuestConversionService
{
    /**
     * Convert a guest order's customer to a registered user.
     *
     * This should be called after a successful payment.
     * If the user already exists, the order is associated with them.
     * If not, a new user account is created.
     */
    public function convertGuestToUser(Order $order): ?User
    {
        // Skip if order already has a user
        if ($order->user_id !== null) {
            Log::channel('payments')->debug('Order already has user, skipping conversion', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);

            return $order->user;
        }

        // Skip if no customer email
        if (empty($order->customer_email)) {
            Log::channel('payments')->warning('Order has no customer email, cannot convert', [
                'order_id' => $order->id,
            ]);

            return null;
        }

        return DB::transaction(function () use ($order) {
            // Check if user already exists with this email
            $existingUser = User::where('email', $order->customer_email)->first();

            if ($existingUser) {
                return $this->associateOrderWithUser($order, $existingUser);
            }

            return $this->createUserAndAssociate($order);
        });
    }

    /**
     * Associate an existing user with a guest order.
     */
    protected function associateOrderWithUser(Order $order, User $user): User
    {
        $order->update(['user_id' => $user->id]);

        // Also update all tickets for this order to the user
        $order->tickets()->update(['user_id' => $user->id]);

        OrderEvent::log(
            order: $order,
            eventType: OrderEvent::USER_ASSOCIATED,
            description: "Orden asociada con usuario existente: {$user->email}",
            metadata: [
                'user_id' => $user->id,
                'action' => 'guest_associated_existing_user',
            ],
            actorType: OrderEvent::ACTOR_SYSTEM,
        );

        Log::channel('payments')->info('Guest order associated with existing user', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
    }

    /**
     * Create a new user and associate with the guest order.
     */
    protected function createUserAndAssociate(Order $order): User
    {
        // Create user with random password (they'll reset it)
        $user = User::create([
            'name' => $order->customer_name,
            'email' => $order->customer_email,
            'password' => bcrypt(Str::random(32)), // Random password, user will reset
            'email_verified_at' => now(), // Consider verified since they used the email for purchase
        ]);

        // Assign customer role
        $user->assignRole(UserRole::Customer->value);

        // Associate order with user
        $order->update(['user_id' => $user->id]);

        // Update tickets for this order to the user
        $order->tickets()->update(['user_id' => $user->id]);

        // Log the event
        OrderEvent::log(
            order: $order,
            eventType: OrderEvent::USER_ASSOCIATED,
            description: "Cuenta de usuario creada automáticamente: {$user->email}",
            metadata: [
                'user_id' => $user->id,
                'action' => 'guest_converted_to_user',
            ],
            actorType: OrderEvent::ACTOR_SYSTEM,
        );

        Log::channel('payments')->info('Guest converted to new user', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Trigger password reset email so user can set their password
        $this->sendPasswordResetEmail($user);

        // Fire registered event for any listeners
        event(new Registered($user));

        return $user;
    }

    /**
     * Send password reset email to newly created user.
     */
    protected function sendPasswordResetEmail(User $user): void
    {
        try {
            Password::broker()->sendResetLink(['email' => $user->email]);

            Log::channel('payments')->info('Password reset email sent to new user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the conversion
            Log::channel('payments')->error('Failed to send password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
