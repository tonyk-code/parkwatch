<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\ParkingSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentWebhookService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{
     *     duplicate: bool,
     *     payment_id: int
     * }
     */
    public function handle(array $payload): array
    {
        $eventId = (string) ($payload['event_id'] ?? '');
        $reference = (string) (
            $payload['reference']
            ?? $payload['tx_ref']
            ?? ''
        );

        if ($eventId === '') {
            throw new RuntimeException(
                'Webhook event_id is required.',
            );
        }

        if ($reference === '') {
            throw new RuntimeException(
                'Webhook payment reference is required.',
            );
        }

        $alreadyProcessed = PaymentEvent::query()
            ->where('event_id', $eventId)
            ->exists();

        if ($alreadyProcessed) {
            return [
                'duplicate' => true,
                'payment_id' => $this->paymentIdForEvent($eventId),
            ];
        }

        $payment = Payment::query()
            ->where('gateway_reference', $reference)
            ->first();

        if (!$payment) {
            throw new RuntimeException(
                'Payment for webhook reference was not found.',
            );
        }

        $incomingStatus = $this->normalizeStatus($payload);

        if ($incomingStatus === 'succeeded') {
            $verification = $this->gateway->verify($reference);

            $this->validateVerification(
                payment: $payment,
                verification: $verification,
            );
        } else {
            $verification = [
                'reference' => $reference,
                'status' => $incomingStatus,
                'amount_minor' => (int) $payment->amount_minor,
                'currency' => $payment->currency,
                'payload' => [],
            ];
        }

        return DB::transaction(function () use ($eventId, $payload, $payment, $incomingStatus, $verification, ) {
            $duplicate = PaymentEvent::query()
                ->where('event_id', $eventId)
                ->exists();

            if ($duplicate) {
                return [
                    'duplicate' => true,
                    'payment_id' => $payment->id,
                ];
            }

            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $lockedPayment->status;

            $shouldAdvance = $this->shouldAdvanceStatus(
                $previousStatus,
                $incomingStatus,
            );

            $session = $lockedPayment->session_id
                ? ParkingSession::query()
                    ->whereKey($lockedPayment->session_id)
                    ->lockForUpdate()
                    ->first()
                : null;

            if ($shouldAdvance) {
                $lockedPayment->update([
                    'status' => $incomingStatus,
                    'gateway_payload' => [
                        'webhook' => $payload,
                        'verification' => $verification['payload'],
                    ],
                    'paid_at' => $incomingStatus === 'succeeded'
                        ? now()
                        : $lockedPayment->paid_at,
                    'refunded_at' => $incomingStatus === 'refunded'
                        ? now()
                        : $lockedPayment->refunded_at,
                ]);

                if (
                    $incomingStatus === 'succeeded' &&
                    $previousStatus !== 'succeeded' &&
                    $session
                ) {
                    $newAmountPaid =
                        (int) $session->amount_paid
                        + (int) $lockedPayment->amount_minor;

                    $session->update([
                        'amount_paid' => $newAmountPaid,
                        'status' => $newAmountPaid >= (int) $session->amount_due
                            ? 'paid'
                            : 'awaiting_payment',
                    ]);

                    $session->events()->create([
                        'event_type' => 'payment.succeeded',
                        'from_status' => $previousStatus,
                        'to_status' => $session->status,
                        'payload' => [
                            'payment_id' => $lockedPayment->id,
                            'amount_minor' => $lockedPayment->amount_minor,
                            'currency' => $lockedPayment->currency,
                            'gateway_reference' =>
                                $lockedPayment->gateway_reference,
                        ],
                        'actor_type' => 'system',
                        'actor_id' => null,
                        'occurred_at' => now(),
                    ]);

                    AuditLog::create([
                        'organization_id' =>
                            $lockedPayment->site->organization_id,
                        'user_id' => null,
                        'action' => 'payment.succeeded',
                        'auditable_type' => Payment::class,
                        'auditable_id' => $lockedPayment->id,
                        'old_values' => [
                            'payment_status' => $previousStatus,
                            'session_status' => $session->getOriginal(
                                'status',
                            ),
                            'amount_paid' =>
                                $session->getOriginal('amount_paid'),
                        ],
                        'new_values' => [
                            'payment_status' => 'succeeded',
                            'session_status' => $session->status,
                            'amount_paid' => $newAmountPaid,
                        ],
                        'ip_address' => null,
                        'user_agent' => null,
                        'created_at' => now(),
                    ]);
                }
            }

            PaymentEvent::create([
                'payment_id' => $lockedPayment->id,
                'event_id' => $eventId,
                'event_type' => $this->eventType($payload),
                'status' => $incomingStatus,
                'payload' => [
                    'webhook' => $payload,
                    'verification' => $verification['payload'],
                    'status_applied' => $shouldAdvance,
                ],
                'occurred_at' => now(),
                'created_at' => now(),
            ]);

            return [
                'duplicate' => false,
                'payment_id' => $lockedPayment->id,
            ];
        });
    }

    /**
     * @param array{
     *     reference: string,
     *     status: string,
     *     amount_minor: int,
     *     currency: string,
     *     payload: array<string, mixed>
     * } $verification
     */
    private function validateVerification(
        Payment $payment,
        array $verification,
    ): void {
        if ($verification['status'] !== 'success') {
            throw new RuntimeException(
                'Payment verification did not return success.',
            );
        }

        if ($verification['reference'] !== $payment->gateway_reference) {
            throw new RuntimeException(
                'Verified payment reference does not match.',
            );
        }

        if (
            (int) $verification['amount_minor']
            !== (int) $payment->amount_minor
        ) {
            throw new RuntimeException(
                'Verified payment amount does not match.',
            );
        }

        if ($verification['currency'] !== $payment->currency) {
            throw new RuntimeException(
                'Verified payment currency does not match.',
            );
        }
    }

    private function normalizeStatus(array $payload): string
    {
        $event = strtolower((string) ($payload['event'] ?? ''));
        $status = strtolower((string) ($payload['status'] ?? ''));

        if (
            $event === 'charge.success' ||
            $status === 'success' ||
            $status === 'succeeded'
        ) {
            return 'succeeded';
        }

        if (
            $event === 'charge.refunded' ||
            $status === 'refunded'
        ) {
            return 'refunded';
        }

        if (
            $event === 'charge.failed' ||
            $event === 'charge.cancelled' ||
            $status === 'failed' ||
            $status === 'cancelled'
        ) {
            return 'failed';
        }

        return 'processing';
    }

    private function shouldAdvanceStatus(
        string $current,
        string $incoming,
    ): bool {
        $rank = [
            'pending' => 10,
            'processing' => 20,
            'failed' => 30,
            'succeeded' => 40,
            'refunded' => 50,
        ];

        return ($rank[$incoming] ?? 0) > ($rank[$current] ?? 0);
    }

    private function eventType(array $payload): string
    {
        return (string) (
            $payload['event']
            ?? 'payment.webhook'
        );
    }

    private function paymentIdForEvent(string $eventId): int
    {
        return (int) PaymentEvent::query()
            ->where('event_id', $eventId)
            ->value('payment_id');
    }
}