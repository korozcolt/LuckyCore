<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PaymentProvider;
use App\Payments\Exceptions\InvalidWebhookSignatureException;
use App\Payments\Exceptions\PaymentProviderException;
use App\Payments\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Payment Webhook Controller.
 *
 * Handles incoming webhooks from payment providers.
 * All webhooks are logged and processed with idempotency.
 *
 * @see ARQUITECTURA.md §5 - Webhook endpoints por provider con firma
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
    ) {}

    /**
     * Handle incoming webhook from a payment provider.
     */
    public function handleWebhook(Request $request, string $provider): JsonResponse
    {
        Log::channel('payments')->info('Webhook received', [
            'provider' => $provider,
            'ip' => $request->ip(),
            'payload_size' => strlen($request->getContent()),
        ]);

        try {
            // Validate provider
            $paymentProvider = PaymentProvider::tryFrom($provider);
            if (! $paymentProvider) {
                Log::channel('payments')->warning('Unknown payment provider', [
                    'provider' => $provider,
                ]);

                return response()->json(['error' => 'Unknown provider'], 400);
            }

            // Get the provider instance
            $providerInstance = $this->paymentManager->provider($paymentProvider);

            // Verify webhook signature
            $providerInstance->verifyWebhookSignature($request);

            // Process the webhook
            $result = $providerInstance->processWebhook($request);

            if ($result->success) {
                Log::channel('payments')->info('Webhook processed successfully', [
                    'provider' => $provider,
                    'transaction_id' => $result->transaction?->id,
                    'status' => $result->status?->value,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processed successfully',
                ]);
            }

            Log::channel('payments')->warning('Webhook processing failed', [
                'provider' => $provider,
                'error_code' => $result->errorCode,
                'error_message' => $result->errorMessage,
            ]);

            return response()->json([
                'success' => false,
                'error' => $result->errorCode,
                'message' => $result->errorMessage,
            ], 200); // Still return 200 to prevent retries for business logic errors

        } catch (InvalidWebhookSignatureException $e) {
            Log::channel('payments')->error('Invalid webhook signature', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Invalid signature',
            ], 401);

        } catch (PaymentProviderException $e) {
            Log::channel('payments')->error('Payment provider error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'code' => $e->errorCode,
            ]);

            return response()->json([
                'error' => $e->errorCode ?? 'Provider error',
                'message' => $e->getMessage(),
            ], 500);

        } catch (\Throwable $e) {
            Log::channel('payments')->error('Unexpected webhook error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
