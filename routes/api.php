<?php

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are stateless and used for external integrations.
|
*/

/*
|--------------------------------------------------------------------------
| Payment Webhooks
|--------------------------------------------------------------------------
|
| These endpoints receive webhook notifications from payment providers.
| They must be publicly accessible without CSRF protection.
|
*/

Route::prefix('webhooks/payments')->name('webhooks.payments.')->group(function () {
    Route::post('/{provider}', [PaymentWebhookController::class, 'handleWebhook'])
        ->name('handle');
});
