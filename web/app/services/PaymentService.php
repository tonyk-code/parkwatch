<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Enums\SessionStatus;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\ParkingSession;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
    ) {
    }

    public function createPayment(
        ParkingSession $session,
        string $phone,
        string $idempotencyKey,
    ): Payment {
        $result = null;

        try {
            $result = DB::transaction(function () use (
                $session,
                $idempotencyKey,
            ) {
                $existingPayment = Payment::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existingPayment) {
                    $this->validateExistingPayment(
                        $existingPayment,
                        $session,
                    );

                    return [
                        'payment' => $existingPayment,
                        'created' => false,
                    ];
                }

                if ($session->status !== SessionStatus::AwaitingPayment) {
                    throw new RuntimeException(
                        'This parking session is not awaiting payment.',
                    );
                }

                $remainingAmount = max(
                    0,
                    (int) $session->amount_due
                        - (int) $session->amount_paid,
                );

                if ($remainingAmount <= 0) {
                    throw new RuntimeException(
                        'This parking session has no outstanding balance.',
                    );
                }

                $payment = Payment::create([
                    'session_id' => $session->id,
                    'site_id' => $session->site_id,
                    'amount_minor' => $remainingAmount,
                    'currency' => $session->currency,
                    'method' => 'mobile_money',
                    'status' => 'pending',
                    'idempotency_key' => $idempotencyKey,
                ]);

                $this->recordEvent(
                    payment: $payment,
                    eventId: 'local-' . Str::uuid()->toString(),
                    eventType: 'payment.created',
                    status: 'pending',
                    payload: [
                        'amount_minor' => $payment->amount_minor,
                        'currency' => $payment->currency,
                        'method' => $payment->method,
                    ],
                );

                return [
                    'payment' => $payment,
                    'created' => true,
                ];
            });
        } catch (QueryException $exception) {
            if (!$this->isIdempotencyConflict($exception)) {
                throw $exception;
            }

            $payment = Payment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->firstOrFail();

            $this->validateExistingPayment($payment, $session);

            return $payment->fresh();
        }

        /** @var Payment $payment */
        $payment = $result['payment'];

        if (!$result['created']) {
            return $payment->fresh();
        }

        try {
            $gatewayResult = $this->gateway->initiate(
                payment: $payment,
                phone: $phone,
                idempotencyKey: $idempotencyKey,
            );
        } catch (\Throwable $exception) {
            DB::transaction(function () use ($payment, $exception) {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedPayment->status === 'pending' &&
                    $lockedPayment->gateway_reference === null
                ) {
                    $lockedPayment->update([
                        'status' => 'failed',
                        'gateway_payload' => [
                            'error' => $exception->getMessage(),
                        ],
                    ]);

                    $this->recordEvent(
                        payment: $lockedPayment,
                        eventId : 'local-' . Str::uuid()->toString(),
                        eventType: 'payment.gateway_failed',
                        status: 'failed',
                        payload: [
                            'message' => $exception->getMessage(),
                        ],
                    );
                }
            });

            throw $exception;
        }

        return DB::transaction(function () use (
            $payment,
            $gatewayResult,
        ) {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->gateway_reference !== null) {
                return $lockedPayment->fresh();
            }

            $lockedPayment->update([
                'status' => $gatewayResult['status'],
                'gateway_reference' => $gatewayResult['reference'],
                'gateway_payload' => $gatewayResult['payload'],
            ]);

            $this->recordEvent(
                payment: $lockedPayment,
                eventId : 'local-' . Str::uuid()->toString(),
                eventType: 'payment.processing',
                status: $gatewayResult['status'],
                payload: $gatewayResult['payload'],
            );

            return $lockedPayment->fresh();
        });
    }

    private function validateExistingPayment(
        Payment $payment,
        ParkingSession $session,
    ): void {
        if ($payment->session_id !== $session->id) {
            throw new RuntimeException(
                'This idempotency key belongs to another parking session.',
            );
        }

        if ((int) $payment->amount_minor !== max(
            0,
            (int) $session->amount_due - (int) $session->amount_paid,
        )) {
            throw new RuntimeException(
                'The payment amount does not match the parking session balance.',
            );
        }

        if ($payment->currency !== $session->currency) {
            throw new RuntimeException(
                'The payment currency does not match the parking session.',
            );
        }
    }

    private function recordEvent(
        Payment $payment,
        string $eventId,
        string $eventType,
        string $status,
        array $payload,
    ): PaymentEvent {
        return PaymentEvent::create([
            'payment_id' => $payment->id,
            'event_id' => $eventId,
            'event_type' => $eventType,
            'status' => $status,
            'payload' => $payload,
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }

    private function isIdempotencyConflict(
        QueryException $exception,
    ): bool {
        return str_contains(
            strtolower($exception->getMessage()),
            'idempotency_key',
        );
    }
}