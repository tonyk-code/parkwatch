<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGateway
{
    public function initiate(
        Payment $payment,
        string $phone,
        string $idempotencyKey,
    ): array {
        $reference = 'MOCK-' . Str::upper(Str::random(20));

        return [
            'reference' => $reference,
            'status' => 'processing',
            'payload' => [
                'provider' => 'mock',
                'reference' => $reference,
                'phone' => $phone,
                'amount_minor' => $payment->amount_minor,
                'currency' => $payment->currency,
                'idempotency_key' => $idempotencyKey,
                'message' => 'Mock payment initiated.',
            ],
        ];
    }

    public function verify(string $reference): array
    {
        $payment = Payment::query()
            ->where('gateway_reference', $reference)
            ->firstOrFail();

        return [
            'reference' => $reference,
            'status' => 'success',
            'amount_minor' => (int) $payment->amount_minor,
            'currency' => $payment->currency,
            'payload' => [
                'provider' => 'mock',
                'reference' => $reference,
                'status' => 'success',
                'amount_minor' => (int) $payment->amount_minor,
                'currency' => $payment->currency,
                'verified_at' => now()->toIso8601String(),
            ],
        ];
    }
}