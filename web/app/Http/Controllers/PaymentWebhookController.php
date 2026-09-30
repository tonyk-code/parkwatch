<?php

namespace App\Http\Controllers;

use App\Services\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentWebhookService $service,
    ): JsonResponse {
        $rawPayload = $request->getContent();

        $receivedSignature =
            $request->header('x-chapa-signature')
            ?? $request->header('chapa-signature');

        $secret = (string) config(
            'services.payment.webhook_secret',
        );

        if ($secret === '' || $receivedSignature === null) {
            return response()->json(
                ['message' => 'Invalid webhook signature.'],
                Response::HTTP_UNAUTHORIZED,
            );
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $rawPayload,
            $secret,
        );

        if (
            !hash_equals(
                $expectedSignature,
                $receivedSignature,
            )
        ) {
            return response()->json(
                ['message' => 'Invalid webhook signature.'],
                Response::HTTP_UNAUTHORIZED,
            );
        }

        $payload = $request->json()->all();

        $result = $service->handle($payload);

        return response()->json([
            'ok' => true,
            'duplicate' => $result['duplicate'],
            'payment_id' => $result['payment_id'],
        ]);
    }
}